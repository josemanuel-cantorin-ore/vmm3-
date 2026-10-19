<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * 1. Muestra la vista del formulario de login.
     */
    public function index()
    {
        return view('login'); 
    }

    /**
     * 2. Procesa los datos enviados por el profesor.
     */
    public function login(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
            'clave'         => 'required|string',
        ]);

        // Control de intentos fallidos 
        $llaveBloqueo = strtolower($request->input('identificador')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($llaveBloqueo, 3)) {
            $segundosRestantes = RateLimiter::availableIn($llaveBloqueo);
            $minutos = ceil($segundosRestantes / 60);

            return back()->withErrors([
                'identificador' => "Cuenta bloqueada por seguridad. Intenta de nuevo en {$minutos} minutos.",
            ])->onlyInput('identificador');
        }

        $identificador = $request->input('identificador');
        $campoBaseDatos = filter_var($identificador, FILTER_VALIDATE_EMAIL) ? 'correo' : 'dni';

        $credenciales = [
            $campoBaseDatos => $identificador,
            'password'      => $request->input('clave')
        ];

        // Captura el estado de la casilla Recordarme (true o false)
        $recordarme = $request->has('remember');

        if (Auth::attempt($credenciales, $recordarme)) {
            RateLimiter::clear($llaveBloqueo);
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        RateLimiter::hit($llaveBloqueo, 300);
        $intentosRestantes = RateLimiter::retriesLeft($llaveBloqueo, 3);

        return back()->withErrors([
            'identificador' => "Credenciales incorrectas. Te quedan {$intentosRestantes} intentos antes del bloqueo.",
        ])->onlyInput('identificador');
    }

    /**
     * 3. Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}