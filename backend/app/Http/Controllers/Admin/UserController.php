<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $applyFilters = function ($query) use ($search) {
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('fullname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            }
            return $query;
        };

        $users = $applyFilters(User::whereIn('role', ['admin', 'staff', 'customer']))->latest()->get();

        return view('admin.users.users', compact('users', 'search'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent disabling yourself
        if (auth()->id() == $user->id) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $request->validate([
            'status' => 'required|in:active,disabled',
        ]);

        $user->update(['status' => $request->status]);

        return back()->with('success', 'User status updated successfully.');
    }
}
