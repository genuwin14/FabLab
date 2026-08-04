<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $role = $request->input('role', '');
        $status = $request->input('status', '');

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = User::whereIn('role', ['admin', 'staff', 'customer']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        if (in_array($role, ['admin', 'staff', 'customer'], true)) {
            $query->where('role', $role);
        }

        if (in_array($status, ['active', 'disabled'], true)) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate($perPage)->withQueryString();

        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('admin.users.users', compact('users', 'search', 'role', 'status', 'perPage', 'roleCounts'));
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
