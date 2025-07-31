<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Traceability;
use App\Models\StateUser;
use App\Mail\NewUserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

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
                'label' => 'Correo electrónico',
                'placeholder' => 'Correo electrónico',
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

    public function register(Request $request)
    {
        try {
            // Validar tipo de registro
            $request->validate([
                'registration_type' => 'required|in:natural,company',
                'user' => 'required|string|email|max:255|unique:users,user',
                'password' => 'required|string|min:8|max:255',
                'confirm_password' => 'required|string|same:password',
            ]);

            // Validaciones específicas según el tipo de registro
            if ($request->registration_type === 'natural') {
                $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:100',
                    'document_id' => 'required|string|max:20|unique:users,document_id',
                    'city' => 'required|string|max:255',
                ]);
            } else {
                $request->validate([
                    'company_name' => 'required|string|max:255',
                    'nit' => 'required|string|max:20|unique:users,document_id',
                    'company_city' => 'required|string|max:255',
                    'economic_sector' => 'required|integer|between:1,21',
                ]);
            }

            // Validar autorización de datos
            $request->validate([
                'data_authorization' => 'required|accepted',
            ]);

        // Obtener el siguiente ID disponible
        $nextUserId = User::findNextAvailableId();
        
        // Mapeo de sectores económicos
        $economicSectors = [
            '1' => 'Agricultura, ganadería, caza, silvicultura y pesca',
            '2' => 'Explotación de minas y canteras',
            '3' => 'Industrias manufactureras',
            '4' => 'Suministro de electricidad, gas, vapor y aire acondicionado',
            '5' => 'Suministro de agua; gestión de residuos y saneamiento ambiental',
            '6' => 'Construcción',
            '7' => 'Comercio al por mayor y al por menor',
            '8' => 'Transporte y almacenamiento',
            '9' => 'Alojamiento y servicios de comida',
            '10' => 'Información y comunicaciones',
            '11' => 'Actividades financieras y de seguros',
            '12' => 'Actividades inmobiliarias',
            '13' => 'Actividades profesionales, científicas y técnicas',
            '14' => 'Actividades administrativas y de apoyo',
            '15' => 'Educación',
            '16' => 'Salud humana y asistencia social',
            '17' => 'Arte, entretenimiento y recreación',
            '18' => 'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)',
            '19' => 'Administración pública y defensa',
            '20' => 'Actividades de los hogares como empleadores',
            '21' => 'Organismos internacionales y otras instituciones extraterritoriales'
        ];
        
        // Crear usuario según el tipo de registro
        if ($request->registration_type === 'natural') {
            $user = User::create([
                'id' => $nextUserId,
                'document_id' => $request->document_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'city' => $request->city,
                'user' => $request->user,
                'password' => bcrypt($request->password),
                'registration_type' => 'natural',
                'data_authorization' => true,
                'role' => 0, // Usuario normal por defecto
                'status_users_id' => 2, // Estado inactivo por defecto
            ]);
        } else {
            $user = User::create([
                'id' => $nextUserId,
                'document_id' => $request->nit,
                'first_name' => $request->company_name,
                'last_name' => '', // Campo vacío para empresas
                'city' => $request->company_city,
                'user' => $request->user,
                'password' => bcrypt($request->password),
                'registration_type' => 'company',
                'economic_sector' => $economicSectors[$request->economic_sector] ?? $request->economic_sector,
                'data_authorization' => true,
                'role' => 0, // Usuario normal por defecto
                'status_users_id' => 2, // Estado inactivo por defecto
            ]);
        }
        
        // Crear ruta de trazabilidad para el usuario
        try {
            $nextId = Traceability::findNextAvailableId();
            Traceability::create([
                'id' => $nextId,
                'user_id' => $user->id,
                'tried' => '1',
                'variables' => '1', // Habilitada para empezar
                'matriz' => '0',
                'maps' => '0',
                'hypothesis' => '0',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '0',
                'conclusions' => '0',
                'results' => '0',
                'state' => '0'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating traceability route: ' . $e->getMessage());
            // No fallar el registro si la trazabilidad falla
        }

        // Enviar correo a todos los administradores
        try {
            $this->sendNotificationToAdmins($user);
        } catch (\Exception $e) {
            \Log::error('Error sending notification email: ' . $e->getMessage());
        }
        
        session()->flash('success', __('register.success'));
        
        return response()->json([
            'status' => 'success',
            'message' => __('register.success'),
            'redirect' => route('login')
        ]);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            
            return response()->json([
                'status' => 'error',
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envía notificación por correo a todos los administradores
     */
    private function sendNotificationToAdmins($newUser)
    {
        // Obtener todos los administradores
        $admins = User::where('role', 1)->get();
        
        if ($admins->isEmpty()) {
            \Log::warning('No hay administradores registrados para enviar notificación');
            return;
        }

        // Generar URL de activación
        $token = Hash::make($newUser->user);
        $activationUrl = route('user.activation', [
            'userId' => $newUser->id,
            'token' => $token
        ]);

        // Log de la notificación (para pruebas)
        \Log::info('Nuevo usuario registrado: ' . $newUser->first_name . ' ' . $newUser->last_name . ' (' . $newUser->user . ')');

        // Enviar correo a cada administrador
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->user)->send(new NewUserNotification($newUser, $activationUrl));
                \Log::info('Notificación enviada a administrador: ' . $admin->user);
            } catch (\Exception $e) {
                \Log::error('Error enviando correo a administrador ' . $admin->user . ': ' . $e->getMessage());
            }
        }
    }
}