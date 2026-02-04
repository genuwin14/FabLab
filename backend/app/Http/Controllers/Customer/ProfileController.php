<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'degree' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:10',
            'section' => 'nullable|string|max:10',
            'gender' => 'nullable|in:Male,Female,Other',
            'photo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:255',
        ]);

        $user->fullname = $validated['fullname'];
        $user->email = $validated['email'];
        $user->contact_number = $validated['contact_number'] ?? $user->contact_number;
        $user->address = $validated['address'] ?? $user->address;

        // Customer specific fields
        $user->degree = $validated['degree'] ?? $user->degree;
        $user->year = $validated['year'] ?? $user->year;
        $user->section = $validated['section'] ?? $user->section;
        $user->gender = $validated['gender'] ?? $user->gender;

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('profile-photos', 'public');
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
