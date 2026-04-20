<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Publier une mission
     */
    public function storeMission(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'deadline' => 'required|date|after:today',
        ]);

        $mission = \App\Models\Mission::create([
            'client_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'deadline' => $request->deadline,
            'status' => 'open',
        ]);

        return response()->json($mission, 201);
    }

    /**
     * Voir mes missions
     */
    public function getMyMissions(Request $request)
    {
        return response()->json(
            $request->user()->missions()->with('category')->get()
        );
    }
}
