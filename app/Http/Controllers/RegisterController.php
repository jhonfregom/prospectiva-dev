<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // URLs que usará el componente Vue
        $list_urls = [
            'register' => route('start_register'),
            'login' => route('login'),
            'home' => route('home'),
        ];

        // Textos para el componente Vue
        $texts = [
            'title' => __('register.title'),
            'subtitle' => __('register.subtitle'),
            'login_link' => __('register.login_link'),
        ];

        // Campos del formulario
        $fields = [
            'first_name' => [
                'label' => __('register.first_name'),
                'placeholder' => __('register.first_name'),
                'error' => false,
                'msg' => '',
            ],
            'last_name' => [
                'label' => __('register.last_name'),
                'placeholder' => __('register.last_name'),
                'error' => false,
                'msg' => '',
            ],
            'user' => [
                'label' => __('register.user'),
                'placeholder' => __('register.user'),
                'error' => false,
                'msg' => '',
            ],
            'password' => [
                'label' => __('register.password'),
                'placeholder' => __('register.password'),
                'error' => false,
                'msg' => '',
            ],
            'document_id' => [
                'label' => __('register.document_id'),
                'placeholder' => __('register.document_id'),
                'error' => false,
                'msg' => '',
            ],
            'submit' => [
                'label' => __('register.submit'),
            ],
        ];

        return view('register.register', compact('list_urls', 'texts', 'fields'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'document_id' => 'required|string|max:20|unique:users,user',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:100',
            'user' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create([
            'document_id' => $request->document_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user' => $request->user,
            'password' => bcrypt($request->password),
            'status_users_id' => 2, // Estado inactivo por defecto
        ]);
        return redirect()->route('login')->with('success', 'Registro exitoso. Por favor, inicie sesión.');
    }
}