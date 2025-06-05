<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PasswordChangeRequest;
use App\Models\StatePasswordChangeRequest;
use App\Models\UpdatedPassword;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $rules_update =   [
        'id'                =>  'required|exists:users,id',
        'name'              =>  'nullable|min:3|max:200',
        'user'              =>  'nullable|max:100|unique:users,user',
        'password'          =>  'nullable',
        'roles_id'          =>  'nullable|exists:roles,id',
        'status_users_id'   =>  'nullable|exists:status_users,id'
    ];

    /**
     * Show the form for restore password.
     * @return \Illuminate\Http\Response
     */
    public function showPasswordReset(): View
    {
        return view('login.restore-password');
    }

    /**
     * Password reset from link
     * @param  \Illuminate\Http\Request  $request
     */
    public function passwordReset( Request $request )
    {
        $data = null;
        $code = 200;

        if( isset($request->data ) )
        {
            try
            {
                //Decrypt params
                $params = Crypt::decrypt( $request->data ); // id => value
                //Get password change requests
                $pcr = PasswordChangeRequest::find( $params['id'] );
                if( isset( $pcr ) )
                {
                    $data = [ 'exists' => true ];
                    $data['password_change_request'] = $pcr;
                }else{
                    $data = [ 'exists' => false ];
                }
            }catch( DecryptException $e){
                $data = [
                    'exists'    => false,
                    'msg'     => $e->getMessage()
                ];
            }
        }else{
            $data = [
                        'fails' => [
                    'error' => __('messages.bad_request'),
                    'data'  => __('validation.required', ['attribute' => 'data']),
                ]
            ];
            $code = 400;
        }
        // dd($data);
        return response()->view( 'users.password-reset', $data, $code );
    }
}
