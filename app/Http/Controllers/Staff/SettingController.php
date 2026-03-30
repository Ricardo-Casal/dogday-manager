<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Staff/Settings/Index', [
            'settings' => Setting::orderBy('key')->get(),
        ]);
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:0',
        ]);

        $setting->update($validated);

        return back();
    }
}
