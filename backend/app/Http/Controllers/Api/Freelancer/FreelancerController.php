<?php

namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FreelancerController extends Controller
{
    /**
     * Voir les missions disponibles
     */
    public function getAvailableMissions()
    {
        return response()->json(
            \App\Models\Mission::where('status', 'open')->with(['client', 'category'])->get()
        );
    }

    /**
     * Publier un portfolio (brief)
     */
    public function storePortfolio(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'nullable|array',
        ]);

        $portfolio = \App\Models\Portfolio::create([
            'freelancer_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'images' => $request->images,
        ]);

        return response()->json($portfolio, 201);
    }

    /**
     * Mes portfolios
     */
    public function getMyPortfolios(Request $request)
    {
        return response()->json(
            $request->user()->portfolios()->with('category')->get()
        );
    }
}
