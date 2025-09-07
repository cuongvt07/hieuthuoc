<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'ten_dang_nhap' => 'required|string',
            'mat_khau' => 'required|string',
        ]);

        $user = NguoiDung::where('ten_dang_nhap', $credentials['ten_dang_nhap'])->first();
        
        // Check if the user exists and the password matches
        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau_hash)) {
            Auth::login($user);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'ten_dang_nhap' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput($request->except('mat_khau'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
