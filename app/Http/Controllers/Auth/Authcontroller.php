<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Valida os dados enviados
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tenta autenticar o usuário
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Garante segurança da sessão
            return redirect()->intended('/dashboard'); // Redireciona pro painel
        }

        // Se falhar, volta pro login com erro
        return back()->with('error', 'E-mail ou senha inválidos.');
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Destroi a autenticação

        $request->session()->invalidate();      // Invalida a sessão atual
        $request->session()->regenerateToken(); // Garante nova proteção CSRF

        return redirect('/login'); // Redireciona pro login
    }

}

