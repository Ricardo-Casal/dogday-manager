<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Staff/Users/Index', [
            'users' => User::where('role', 'staff')->orderBy('name')->get(['id', 'name', 'email', 'created_at']),
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
            'role'     => $request->input('role', 'staff'),
        ]);

        // Auto-link to owner if email matches
        if ($user->role === 'owner') {
            $owner = Owner::where('email', $user->email)->whereNull('user_id')->first();
            if ($owner) {
                $owner->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('staff.users.index');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Não podes apagar a tua própria conta.');

        $user->delete();

        return back();
    }
}
