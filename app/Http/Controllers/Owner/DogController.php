<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DogController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Owner/Dogs/Create');
    }

    public function store(Request $request)
    {
        $owner = auth()->user()->owner;

        abort_unless($owner, 403);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'breed'     => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'notes'     => 'nullable|string',
        ]);

        $owner->dogs()->create($validated);

        return redirect()->route('owner.dashboard')->with('success', 'Cão adicionado com sucesso!');
    }
}
