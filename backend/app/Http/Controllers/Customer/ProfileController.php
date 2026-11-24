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
        $user->address = $validated['address'] ?? $user->address;

        // A new number has to prove it receives texts. One replacing a
        // verified number waits until it does, so the account never trades a
        // proven number for an unproven one; one replacing an unverified
        // number (or none) is saved now and verified when the code comes in.
        $newNumber = trim((string) ($validated['contact_number'] ?? ''));
        $numberChanged = $newNumber !== '' && $newNumber !== trim((string) $user->contact_number);
        $numberWaits = $numberChanged && $user->phone_verified;

        if ($numberChanged && ! $numberWaits) {
            $user->contact_number = $newNumber;
            $user->phone_verified = false;
        }

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

        if ($numberChanged) {
            \App\Support\PhoneVerification::send($user, 'sms', $numberWaits ? $newNumber : null);

            return redirect()->route('verify.code')
                ->with('verification_mode', 'sms')
                ->with('success', $numberWaits
                    ? 'Profile updated. Enter the code we texted to your new number to switch to it.'
                    : 'Profile updated. Enter the code we texted to verify your number.');
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
