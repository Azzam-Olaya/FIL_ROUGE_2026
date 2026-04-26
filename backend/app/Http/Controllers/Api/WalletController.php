<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get user balance and stats.
     */
    public function getSummary(Request $request)
    {
        $user = $request->user();
        
        $totalEarned = 0;
        $totalSpent = 0;
        
        try {
            $totalEarned = Transaction::where('user_id', $user->id)
                ->where('type', 'escrow_out')
                ->where('status', 'completed')
                ->sum('amount');
            
            $totalSpent = Transaction::where('user_id', $user->id)
                ->where('type', 'escrow_in')
                ->where('status', 'completed')
                ->sum('amount');
        } catch (\Exception $e) {
            // Table transactions peut ne pas encore exister
        }

        return response()->json([
            'balance' => $user->balance,
            'total_earned' => $totalEarned,
            'total_spent' => $totalSpent,
        ]);
    }

    /**
     * Get transaction history.
     */
    public function getHistory(Request $request)
    {
        try {
            $transactions = Transaction::where('user_id', $request->user()->id)
                ->with('contract.mission')
                ->latest()
                ->paginate(20);
            return response()->json($transactions);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Simulation of adding funds (to be replaced by Stripe/PayPal).
     */
    public function addFunds(Request $request)
    {
        try {
            $request->validate(['amount' => 'required|numeric|min:10']);
            
            $user = $request->user();
            
            $user->increment('balance', $request->amount);
            
            try {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'description' => "Dépôt de fonds via simulation (Interface Admin)",
                    'status' => 'completed'
                ]);
            } catch (\Exception $e) {
                // Table transactions absente, mais le solde est quand même mis à jour
            }

            return response()->json([
                'message' => 'Fonds ajoutés avec succès !',
                'new_balance' => $user->balance
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Capture PayPal Order and credit user account.
     */
    public function capturePayPalOrder(Request $request)
    {
        $request->validate(['orderID' => 'required|string']);
        
        $orderID = $request->orderID;
        $clientId = env('PAYPAL_CLIENT_ID', 'sb'); 
        $secret = env('PAYPAL_SECRET');

        // Note: In highly restricted environment without SDK, we use direct CURL to PayPal API
        // 1. Get Access Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        $accessToken = null;

        if ($result) {
            $json = json_decode($result);
            $accessToken = $json->access_token ?? null;
        }
        curl_close($ch);

        if (!$accessToken) {
            return response()->json(['message' => 'Erreur lors de la validation PayPal (Token)'], 500);
        }

        // 2. Capture Order
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderID/capture");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $accessToken"
        ]);
        $result = curl_exec($ch);
        $orderData = json_decode($result);
        curl_close($ch);

        if (isset($orderData->status) && $orderData->status === 'COMPLETED') {
            $amount = $orderData->purchase_units[0]->payments->captures[0]->amount->value;
            $user = $request->user();

            $user->increment('balance', $amount);
            
            try {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'description' => "Dépôt PayPal (ID: $orderID)",
                    'status' => 'completed',
                    'metadata' => ['paypal_order_id' => $orderID]
                ]);
            } catch (\Exception $e) {}

            return response()->json([
                'message' => 'Paiement PayPal validé. Votre compte a été crédité.',
                'new_balance' => $user->balance
            ]);
        }

        return response()->json(['message' => 'Paiement non valide ou non complété.'], 400);
    }
}
