<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->get();
        $staffs = User::where('role', 'staff')->get();
        $customers = User::where('role', 'customer')->get();

        return view('admin.users.users', compact('admins', 'staffs', 'customers'));
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
