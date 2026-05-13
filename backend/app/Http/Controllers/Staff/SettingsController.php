<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('staff.settings.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'notifications_enabled' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $user->notifications_enabled = (bool) ($validated['notifications_enabled'] ?? false);
        $user->save();

        return redirect()
            ->route('staff.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
