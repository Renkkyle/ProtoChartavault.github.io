<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;  
use App\Models\User;  

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('Custom_Login'); // Adjust the view name if needed
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Check if email exists in the database
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'The email you entered does not exist.']);
        }
    
        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }
    
        // Check if the user is approved
        if (!$user->is_approved) {
            return redirect()->back()->with(['email_nonverified' => 'Your account needs to be approved by the admin before you can log in.']);
        }
    
        // Attempt login
        Auth::login($user);
    
        // Redirect based on role
        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login'); // Use route name for redirection
    }
}