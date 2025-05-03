<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenController extends Controller
{
    /**
     * Show the user's token settings page.
     */
    public function index(): Response
    {
        return Inertia::render('settings/Tokens');
    }

    /**
     * Store a new token for the user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Create a new token
        $token = $user->createToken($request->name);

        return back()->with('status', 'Token created successfully.')->with('token', $token->plainTextToken);
    }

    /**
     * Delete a user's token.
     */
    public function destroy($tokenId)
    {
        $user = auth()->user();

        // Find and delete the token
        $token = $user->tokens()->findOrFail($tokenId);
        $token->delete();

        return back()->with('status', 'Token deleted successfully.');
    }
}

class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abilities' => $this->abilities,
            'last_used_at' => $this->last_used_at,
            'created_at' => $this->created_at,
        ];
    }
}
