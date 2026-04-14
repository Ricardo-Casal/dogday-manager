<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function index(): Response
    {
        $owners = Owner::with('user:id,name,email')->withCount('dogs')->orderBy('name')->get();

        return Inertia::render('Owners/Index', [
            'owners' => $owners,
        ]);
    }

    public function create(): Response
    {
        $availableUsers = User::where('role', 'owner')
            ->whereDoesntHave('owner')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Owners/Create', [
            'availableUsers' => $availableUsers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'notes'           => 'nullable|string',
            'user_id'         => 'nullable|exists:users,id',
            'dogs'            => 'nullable|array',
            'dogs.*.name'     => 'required|string|max:255',
            'dogs.*.breed'    => 'nullable|string|max:255',
            'dogs.*.birthdate'=> 'nullable|date',
            'dogs.*.notes'    => 'nullable|string',
        ]);

        $owner = Owner::create($validated);

        if (!empty($validated['dogs'])) {
            $owner->dogs()->createMany($validated['dogs']);
        }

        // Auto-link by email if no user was manually selected
        if (!$owner->user_id) {
            $this->autoLinkByEmail($owner);
        }

        return redirect()->route('owners.index');
    }

    public function show(Owner $owner): Response
    {
        $owner->load('dogs.attendances', 'user:id,name,email');

        return Inertia::render('Owners/Show', [
            'owner' => $owner,
        ]);
    }

    public function edit(Owner $owner): Response
    {
        $owner->load('dogs', 'user:id,name,email');

        $availableUsers = User::where('role', 'owner')
            ->where(function ($q) use ($owner) {
                $q->whereDoesntHave('owner')
                  ->orWhere('id', $owner->user_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Owners/Edit', [
            'owner'          => $owner,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function update(Request $request, Owner $owner)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'notes'   => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $owner->update($validated);

        // Auto-link by email if user_id was cleared and email is set
        if (!$owner->user_id && $owner->email) {
            $this->autoLinkByEmail($owner);
        }

        return redirect()->route('owners.show', $owner);
    }

    public function destroy(Owner $owner)
    {
        $owner->delete();

        return redirect()->route('owners.index');
    }

    private function autoLinkByEmail(Owner $owner): void
    {
        if (!$owner->email) {
            return;
        }

        $user = User::where('email', $owner->email)
            ->where('role', 'owner')
            ->whereDoesntHave('owner')
            ->first();

        if ($user) {
            $owner->update(['user_id' => $user->id]);
        }
    }
}
