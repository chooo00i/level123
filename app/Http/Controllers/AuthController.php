<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 로그인 페이지
    public function create() {
        return inertia('Auth/Login');
    }

    // 로그인
    public function store(Request $request) {
        if (!Auth::attempt($request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]), true)) {
            throw ValidationException::withMessages([
                'email' => '이메일이나 비밀번호가 맞지 않습니다.',
            ]);
        }

        $request->session()->regenerate(); // 로그인 및 토큰 재생성
        return redirect()->intended(route('home'));
    }

    // 로그아웃
    public function destroy(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
