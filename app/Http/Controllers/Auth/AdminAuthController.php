<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;


class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = auth('admins')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $admin = auth('admins')->user();

        return response()->json([
            'data' => $admin,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => auth('admins')->factory()->getTTL() * 60 . ' seconds'
            ]
        ]);
    }
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'password' => 'required|string|min:6',
            ]);

            DB::beginTransaction();

            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'Admin',
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Admin created successfully',
                'data' => $admin
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new admin failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Create new admin failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth('admins')->logout();

            return response()->json([
                'message' => 'Successfully logged out',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh()
    {
        $newToken = auth('admins')->refresh();

        return response()->json([
            'data' => auth('admins')->user(),
            'authorisation' => [
                'token' => $newToken,
                'type' => 'bearer',
            ]
        ]);
    }
}
