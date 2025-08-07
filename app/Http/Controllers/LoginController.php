<?php

namespace App\Http\Controllers;

use App\Models\StateUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): View | RedirectResponse
    {
        if( Auth::check() )
        {
            
            return redirect('/app');
        }

        return view('login.login');
    }

    public function authenticate(Request $request): JsonResponse
    {
        $credentials = $request->only('user', 'password');
        $credentials['status_users_id'] = StateUser::STATUS_ACTIVE;

        if (Auth::attempt($credentials)) {
            
            $user = Auth::user();
            return response()->json(
                array(
                    'status'    => 200,
                    'user'      => [
                        'id'         => $user->id,
                        'role'       => $user->role,
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'user'       => $user->user,
                    ]
                ),
                200
            );
        }

         return response()->json(
            array(
                'status'    =>  204,
                'error'     =>  "No content",
                'data'      =>  array( trans('auth.failed') )
            ),
            200
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(
            array(
                'status'    => 200,
            ),
            200
        );
    }
}