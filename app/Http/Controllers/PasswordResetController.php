<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Mostrar formulario para solicitar restablecimiento de contraseña
     */
    public function showResetForm()
    {
        return view('login.restore-password-simple');
    }

    /**
     * Enviar enlace de restablecimiento por correo
     */
    public function sendResetLink(Request $request)
    {
        try {
            // Validar el email
            $request->validate([
                'email' => 'required|email|max:255|exists:users,user',
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.max' => 'El correo electrónico no puede exceder los 255 caracteres.',
                'email.exists' => 'No existe una cuenta registrada con este correo electrónico.',
            ]);

            // Buscar el usuario
            $user = User::where('user', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No existe una cuenta registrada con este correo electrónico.'
                ], 404);
            }

            // Generar token único
            $token = Str::random(64);
            
            // Guardar token en la base de datos
            $user->update([
                'password_reset_token' => $token,
                'password_reset_expires_at' => now()->addHours(1), // Token válido por 1 hora
            ]);

            // Generar URL de restablecimiento
            $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $request->email]);
            
            // Obtener nombre del usuario para el correo
            $userName = $user->first_name ? $user->first_name . ' ' . $user->last_name : null;
            
            // Enviar correo electrónico
            Mail::to($request->email)->send(new PasswordResetMail($resetUrl, $userName));

            // Log del envío exitoso
            Log::info('Password reset link sent successfully', [
                'email' => $request->email,
                'user_id' => $user->id,
                'reset_url' => $resetUrl
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Se ha enviado un enlace de restablecimiento al correo ' . $request->email . '. Revisa tu bandeja de entrada.'
            ]);

        } catch (ValidationException $e) {
            // Manejar errores de validación
            $errors = $e->errors();
            $firstError = reset($errors);
            $message = reset($firstError);
            
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error sending password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al enviar el enlace de restablecimiento. Por favor, intenta nuevamente.'
            ], 500);
        }
    }

    /**
     * Mostrar formulario para restablecer contraseña
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('login')->with('error', 'Enlace de restablecimiento inválido.');
        }

        // Verificar que el token sea válido
        $user = User::where('user', $email)
                   ->where('password_reset_token', $token)
                   ->where('password_reset_expires_at', '>', now())
                   ->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'El enlace de restablecimiento no es válido o ha expirado.');
        }

        return view('login.reset-password-simple', compact('token', 'email'));
    }

    /**
     * Procesar el restablecimiento de contraseña
     */
    public function resetPassword(Request $request)
    {
        try {
            // Validar los datos
            $request->validate([
                'token' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'password_confirmation' => 'required|same:password',
            ], [
                'token.required' => 'Token de restablecimiento requerido.',
                'token.max' => 'El token no puede exceder los 255 caracteres.',
                'email.required' => 'Correo electrónico requerido.',
                'email.email' => 'Formato de correo electrónico inválido.',
                'email.max' => 'El correo electrónico no puede exceder los 255 caracteres.',
                'password.required' => 'La nueva contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.max' => 'La contraseña no puede exceder los 255 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula, un número y un carácter especial (@$!%*?&).',
                'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
                'password_confirmation.same' => 'La confirmación de contraseña no coincide.',
            ]);

            // Verificar que el usuario y token sean válidos
            $user = User::where('user', $request->email)
                       ->where('password_reset_token', $request->token)
                       ->where('password_reset_expires_at', '>', now())
                       ->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El enlace de restablecimiento no es válido o ha expirado.'
                ], 400);
            }

            // Actualizar contraseña y limpiar token
            $user->update([
                'password' => Hash::make($request->password),
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ]);

            // Log del restablecimiento exitoso
            Log::info('Password reset completed successfully', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña actualizada correctamente. Puedes iniciar sesión con tu nueva contraseña.',
                'redirect' => route('login')
            ]);

        } catch (ValidationException $e) {
            // Manejar errores de validación
            $errors = $e->errors();
            $firstError = reset($errors);
            $message = reset($firstError);
            
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error resetting password', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restablecer la contraseña. Por favor, intenta nuevamente.'
            ], 500);
        }
    }
}
