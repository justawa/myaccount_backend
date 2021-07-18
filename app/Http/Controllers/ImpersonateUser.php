<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;

class ImpersonateUser extends Controller
{
    public function show_all_users()
    {
        $users = User::all();

        return view('impersonate.show_all_users', compact('users'));
    }

    public function impersonate_user($id)
    {
        $user = User::find($id);
        Auth::user()->impersonate($user);

        return redirect()->route('home');
    }

    public function leave_impersonation()
    {
        Auth::user()->leaveImpersonation();

        return redirect()->route('home');
    }
}
