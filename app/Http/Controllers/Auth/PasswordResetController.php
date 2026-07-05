<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(
            ['email' => 'required|email'],
            ['email.required' => 'Informe o e-mail.', 'email.email' => 'E-mail inválido.']
        );

        Password::sendResetLink($request->only('email'));

        // Resposta idêntica com e-mail existente ou não (não revela contas cadastradas)
        return back()->with('sucesso', 'Se este e-mail estiver cadastrado, você receberá um link em instantes.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required'     => 'Informe o e-mail.',
            'password.required'  => 'Informe a nova senha.',
            'password.min'       => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Link inválido ou expirado. Peça um novo link.']);
        }

        return redirect()->route('login')->with('sucesso', 'Senha redefinida! Entre com a nova senha.');
    }
}
