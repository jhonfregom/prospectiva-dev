<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Traceability;
use Illuminate\Support\Facades\Auth;
use App\Events\UserRegistered;

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
            'registration_type' => [
                'label' => 'Tipo de Registro',
                'placeholder' => 'Seleccione el tipo de registro',
                'error' => false,
                'msg' => '',
            ],
            // Campos para Persona Natural
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
            'document_id' => [
                'label' => __('register.document_id'),
                'placeholder' => __('register.document_id'),
                'error' => false,
                'msg' => '',
            ],
            'email' => [
                'label' => 'Correo electrónico',
                'placeholder' => 'Correo electrónico',
                'error' => false,
                'msg' => '',
            ],
            'city' => [
                'label' => 'Ciudad / Región',
                'placeholder' => 'Ciudad / Región',
                'error' => false,
                'msg' => '',
            ],
            // Campos para Empresa/Organización
            'company_name' => [
                'label' => 'Nombre de la empresa',
                'placeholder' => 'Nombre de la empresa',
                'error' => false,
                'msg' => '',
            ],
            'nit' => [
                'label' => 'NIT o número de identificación',
                'placeholder' => 'NIT o número de identificación',
                'error' => false,
                'msg' => '',
            ],
            'corporate_email' => [
                'label' => 'Correo electrónico corporativo',
                'placeholder' => 'Correo electrónico corporativo',
                'error' => false,
                'msg' => '',
            ],
            'company_city' => [
                'label' => 'Ciudad / Región',
                'placeholder' => 'Ciudad / Región',
                'error' => false,
                'msg' => '',
            ],
            'economic_sector' => [
                'label' => 'Sector económico',
                'placeholder' => 'Seleccione el sector económico',
                'error' => false,
                'msg' => '',
            ],
            // Campos comunes
            'user' => [
                'label' => 'Correo electrónico para el login',
                'placeholder' => 'Correo electrónico para el login',
                'error' => false,
                'msg' => '',
            ],
            'password' => [
                'label' => __('register.password'),
                'placeholder' => __('register.password'),
                'error' => false,
                'msg' => '',
            ],
            'confirm_password' => [
                'label' => __('register.confirm_password'),
                'placeholder' => __('register.confirm_password'),
                'error' => false,
                'msg' => '',
            ],
            'submit' => [
                'label' => __('register.submit'),
            ],
        ];

        return view('register.register', compact('list_urls', 'texts', 'fields'));
    }

    private function findNextAvailableId()
    {
        // Usar MAX() para obtener el ID más alto de manera eficiente
        $maxId = User::max('id');
        return $maxId ? $maxId + 1 : 1;
    }

    private function getEconomicSectorText($sectorId)
    {
        $sector = \App\Models\EconomicSector::find($sectorId);
        return $sector ? $sector->name : 'Sector no especificado';
    }

    public function register(Request $request)
    {
        try {
            // Validar tipo de registro y campos comunes
            $request->validate([
                'registration_type' => 'required|in:natural,company',
                'user' => 'required|string|email|max:255|unique:users,user',
                'password' => 'required|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirm_password' => 'required|string|min:8|same:password',
            ], [
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.max' => 'La contraseña no puede exceder los 255 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula, un número y un carácter especial (@$!%*?&).',
                'confirm_password.required' => 'La confirmación de contraseña es obligatoria.',
                'confirm_password.same' => 'La confirmación de contraseña no coincide.',
            ]);

            // Validaciones específicas según el tipo de registro
            if ($request->registration_type === 'natural') {
                $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:100',
                    'document_id' => 'required|string|size:10|unique:users,document_id|regex:/^\d+$/',
                    'city' => 'nullable|string|max:255',
                ], [
                    'document_id.required' => 'El documento de identidad es obligatorio.',
                    'document_id.size' => 'La cédula debe tener exactamente 10 dígitos.',
                    'document_id.regex' => 'La cédula solo debe contener números.',
                    'document_id.unique' => 'Esta cédula ya está registrada en el sistema.',
                ]);
            } else {
                $request->validate([
                    'company_name' => 'required|string|max:255',
                    'nit' => 'required|string|size:9|unique:users,document_id|regex:/^\d+$/',
                    'economic_sector' => 'required|integer|between:1,30',
                    'company_city' => 'nullable|string|max:255',
                ], [
                    'nit.required' => 'El NIT es obligatorio.',
                    'nit.size' => 'El NIT debe tener exactamente 9 dígitos.',
                    'nit.regex' => 'El NIT solo debe contener números.',
                    'nit.unique' => 'Este NIT ya está registrado en el sistema.',
                    'economic_sector.between' => 'El sector económico debe estar entre 1 y 30.',
                ]);
            }

            // Obtener el próximo ID disponible
            $nextId = $this->findNextAvailableId();
            
            // Crear usuario según el tipo de registro
            if ($request->registration_type === 'natural') {
                $userData = [
                    'id' => $nextId,
                    'document_id' => $request->document_id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'user' => $request->user,
                    'password' => bcrypt($request->password),
                    'registration_type' => 'natural',
                    'economic_sector' => null,
                    'city' => $request->city,
                    'status_users_id' => 2,
                    'role' => 0,
                ];
                $user = User::create($userData);
            } else {
                $userData = [
                    'id' => $nextId,
                    'document_id' => $request->nit,
                    'first_name' => $request->company_name,
                    'last_name' => '',
                    'user' => $request->user,
                    'password' => bcrypt($request->password),
                    'registration_type' => 'company',
                    'economic_sector' => $request->economic_sector,
                    'city' => $request->company_city,
                    'status_users_id' => 2,
                    'role' => 0,
                ];
                $user = User::create($userData);
            }
            
            // Crear la ruta de trazabilidad para el usuario
            $traceability = Traceability::getOrCreateForUser($user->id);
            
            session()->flash('success', __('register.success'));
            
            return response()->json([
                'status' => 'success',
                'message' => __('register.success'),
                'redirect' => route('login')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en registro: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Línea del error: ' . $e->getLine());
            \Log::error('Archivo del error: ' . $e->getFile());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}