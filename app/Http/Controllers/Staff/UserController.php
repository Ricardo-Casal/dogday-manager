<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 20]) ? $perPage : 15;

        $query = User::withTrashed()
            ->with('owner.dogs')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('owner', function ($q) use ($search) {
                          $q->where('phone', 'like', "%{$search}%")
                            ->orWhereHas('dogs', fn($q) => $q->where('name', 'like', "%{$search}%"));
                      });
                });
            }, function ($q) {
                $q->whereNull('deleted_at');
            })
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderBy('name');

        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Staff/Users/Index', [
            'users'   => $users,
            'filters' => ['search' => $search, 'per_page' => $perPage],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Staff/Users/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'staff',
        ]);

        // Auto-link to owner if email matches
        $owner = Owner::where('email', $user->email)->whereNull('user_id')->first();
        if ($owner) {
            $owner->update(['user_id' => $user->id]);
        }

        return redirect()->route('staff.users.index');
    }

    public function show(User $user): Response
    {
        $user->load('owner.dogs');

        return Inertia::render('Staff/Users/Show', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if ($user->owner) {
            $user->owner->update($validated);
        } else {
            $user->owner()->create(array_merge($validated, [
                'name'    => $user->name,
                'email'   => $user->email,
                'user_id' => $user->id,
            ]));
        }

        return back();
    }

    public function promote(User $user)
    {
        abort_if($user->id === auth()->id(), 403);
        $user->update(['role' => $user->role === 'staff' ? 'owner' : 'staff']);
        return back();
    }

    public function addDog(Request $request, User $user)
    {
        $owner = $user->owner;
        abort_unless($owner, 422, 'Este utilizador não tem perfil de dono.');

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'breed'     => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'notes'     => 'nullable|string',
        ]);

        $owner->dogs()->create($validated);

        return back();
    }

    public function removeDog(User $user, Dog $dog)
    {
        abort_unless($dog->owner_id === $user->owner?->id, 403);
        $dog->delete();
        return back();
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Não podes apagar a tua própria conta.');

        $request->validate([
            'notes' => 'required|string|min:5|max:500',
        ]);

        $user->deleted_notes = $request->notes;
        $user->save();
        $user->delete();

        return back();
    }
}
