<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario
     */
    public function register(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer', // Por defecto es customer
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            // Crear token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validar datos
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Buscar usuario
            $user = User::where('email', $request->email)->first();

            // Verificar credenciales
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            // Crear token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cerrar sesión (eliminar token actual)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Eliminar el token actual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
