<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Traceability;
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
        // Obtener todos los IDs existentes ordenados
        $existingIds = User::orderBy('id')->pluck('id')->toArray();
        
        // Si no hay usuarios, empezar con ID 1
        if (empty($existingIds)) {
            return 1;
        }
        
        // Buscar el primer hueco disponible
        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }
        
        // Si no hay huecos, usar el siguiente ID después del último
        return $expectedId;
    }

    private function getEconomicSectorText($sectorId)
    {
        $sectors = [
            1 => 'Agricultura, ganadería, caza, silvicultura y pesca',
            2 => 'Explotación de minas y canteras',
            3 => 'Industrias manufactureras',
            4 => 'Suministro de electricidad, gas, vapor y aire acondicionado',
            5 => 'Distribución de agua; evacuación y tratamiento de aguas residuales, gestión de desechos y descontaminación',
            6 => 'Construcción',
            7 => 'Comercio al por mayor y al por menor; reparación de vehículos automotores y motocicletas',
            8 => 'Transporte y almacenamiento',
            9 => 'Alojamiento y servicios de comida',
            10 => 'Información y comunicaciones',
            11 => 'Actividades financieras y de seguros',
            12 => 'Actividades inmobiliarias',
            13 => 'Actividades profesionales, científicas y técnicas',
            14 => 'Actividades de servicios administrativos y de apoyo',
            15 => 'Administración pública y defensa; planes de seguridad social de afiliación obligatoria',
            16 => 'Educación',
            17 => 'Actividades de atención de la salud humana y de asistencia social',
            18 => 'Actividades artísticas, de entretenimiento y recreativas',
            19 => 'Otras actividades de servicios',
            20 => 'Actividades de los hogares individuales en calidad de empleadores; actividades no diferenciadas de los hogares individuales como productores de bienes y servicios para uso propio',
            21 => 'Actividades de organizaciones y entidades extraterritoriales'
        ];
        
        return $sectors[$sectorId] ?? 'Sector no especificado';
    }

    public function register(Request $request)
    {
        // Validar tipo de registro
        $request->validate([
            'registration_type' => 'required|in:natural,company',
            'user' => 'required|string|email|max:255|unique:users,user',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'data_authorization' => 'required|accepted',
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

        // Obtener el próximo ID disponible
        $nextId = $this->findNextAvailableId();
        
        // Crear usuario según el tipo de registro
        if ($request->registration_type === 'natural') {
            $user = User::create([
                'id' => $nextId,
                'document_id' => $request->document_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'city' => $request->city,
                'user' => $request->user,
                'password' => bcrypt($request->password),
                'registration_type' => 'natural',
                'data_authorization' => $request->data_authorization ? true : false,
                'status_users_id' => 2, // Estado inactivo por defecto
            ]);
        } else {
            $user = User::create([
                'id' => $nextId,
                'document_id' => $request->nit,
                'first_name' => $request->company_name,
                'last_name' => '', // Campo vacío para empresas
                'city' => $request->company_city,
                'user' => $request->user,
                'password' => bcrypt($request->password),
                'registration_type' => 'company',
                'economic_sector' => $this->getEconomicSectorText($request->economic_sector),
                'data_authorization' => $request->data_authorization ? true : false,
                'status_users_id' => 2, // Estado inactivo por defecto
            ]);
        }
        
        // Crear la ruta de trazabilidad para el usuario
        Traceability::getOrCreateForUser($user->id);
        
        session()->flash('success', __('register.success'));
        
        return response()->json([
            'status' => 'success',
            'message' => __('register.success'),
            'redirect' => route('login')
        ]);
    }
}