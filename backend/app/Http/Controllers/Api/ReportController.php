<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Client/Freelancer : soumettre un signalement
    public function store(Request $request)
    {
        $request->validate([
            'reported_id' => 'required|exists:users,id',
            'reason'      => 'required|string|max:100',
            'details'     => 'nullable|string|max:500',
        ]);

        $reporter = $request->user();

        if ($reporter->id === (int) $request->reported_id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous signaler vous-même.'], 422);
        }

        // Éviter les doublons
        $exists = Report::where('reporter_id', $reporter->id)
            ->where('reported_id', $request->reported_id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Vous avez déjà signalé cet utilisateur.'], 422);
        }

        Report::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $request->reported_id,
            'reason'      => $request->reason,
            'details'     => $request->details,
        ]);

        // Auto-ban si +10 signalements pending/banned
        $count = Report::where('reported_id', $request->reported_id)
            ->whereIn('status', ['pending', 'banned'])
            ->count();

        if ($count >= 10) {
            User::where('id', $request->reported_id)->update(['verification_status' => 'banned']);
            Report::where('reported_id', $request->reported_id)
                ->where('status', 'pending')
                ->update(['status' => 'banned']);
        }

        return response()->json(['message' => 'Signalement envoyé avec succès.'], 201);
    }

    // Admin : liste des signalements
    public function index()
    {
        $reports = Report::with(['reporter:id,name,email', 'reported:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'            => $r->id,
                    'reporter'      => $r->reporter,
                    'reported'      => $r->reported,
                    'reason'        => $r->reason,
                    'details'       => $r->details,
                    'status'        => $r->status,
                    'created_at'    => $r->created_at->diffForHumans(),
                    'report_count'  => Report::where('reported_id', $r->reported_id)->count(),
                ];
            });

        return response()->json($reports);
    }

    // Admin : ignorer un signalement
    public function dismiss($id)
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => 'dismissed']);
        return response()->json(['message' => 'Signalement ignoré.']);
    }

    // Admin : bannir l'utilisateur signalé
    public function ban($id)
    {
        $report = Report::findOrFail($id);
        User::where('id', $report->reported_id)->update(['verification_status' => 'banned']);
        Report::where('reported_id', $report->reported_id)
            ->where('status', 'pending')
            ->update(['status' => 'banned']);
        return response()->json(['message' => 'Utilisateur banni.']);
    }
}
