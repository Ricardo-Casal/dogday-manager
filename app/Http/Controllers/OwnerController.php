<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function index(): Response
    {
        $owners = Owner::withCount('dogs')->orderBy('name')->get();

        return Inertia::render('Owners/Index', [
            'owners' => $owners,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owners/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'dogs' => 'nullable|array',
            'dogs.*.name' => 'required|string|max:255',
            'dogs.*.breed' => 'nullable|string|max:255',
            'dogs.*.birthdate' => 'nullable|date',
            'dogs.*.notes' => 'nullable|string',
        ]);

        $owner = Owner::create($validated);

        if (!empty($validated['dogs'])) {
            $owner->dogs()->createMany($validated['dogs']);
        }

        return redirect()->route('owners.index');
    }

    public function show(Owner $owner): Response
    {
        $owner->load('dogs.attendances');

        return Inertia::render('Owners/Show', [
            'owner' => $owner,
        ]);
    }

    public function edit(Owner $owner): Response
    {
        $owner->load('dogs');

        return Inertia::render('Owners/Edit', [
            'owner' => $owner,
        ]);
    }

    public function update(Request $request, Owner $owner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $owner->update($validated);

        return redirect()->route('owners.show', $owner);
    }

    public function destroy(Owner $owner)
    {
        $owner->delete();

        return redirect()->route('owners.index');
    }
}
