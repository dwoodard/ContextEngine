<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Agent::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class' => 'required|string',
            'url' => 'nullable|url',
            'skills' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $agent = Agent::create($validated);

        return response()->json($agent, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        return response()->json($agent);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'class' => 'sometimes|required|string',
            'url' => 'nullable|url',
            'skills' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $agent->update($validated);

        return response()->json($agent);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        $agent->delete();

        return response()->noContent();
    }
}
