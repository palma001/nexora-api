<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Integration;

class IntegrationController extends Controller
{
    public function index()
    {
        return response()->json(Integration::all());
    }

    public function update(Request $request, $id)
    {
        $integration = Integration::findOrFail($id);

        $request->validate([
            'is_active' => 'boolean',
            'credentials' => 'nullable|array',
        ]);

        $integration->update($request->only(['is_active', 'credentials']));

        return response()->json($integration);
    }
}
