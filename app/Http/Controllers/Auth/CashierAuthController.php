<?php

namespace App\Http\Controllers\Auth;

use App\Models\Cashier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;


class CashierAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:cashiers', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = auth('cashiers')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $cashier = auth('cashiers')->user();
        return response()->json([
            'data' => $cashier,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => auth('cashiers')->factory()->getTTL() * 60 . ' seconds'
            ]
        ]);
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:cashiers',
                'cabang' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);

            DB::beginTransaction();

            $cashier = Cashier::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'Cashier',
                'cabang' => $request->cabang,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cashier created successfully',
                'data' => $cashier
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new cashier failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Create new cashier failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth('cashiers')->logout();
            return response()->json([
                'message' => 'Successfully logged out',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function self()
    {
        try{
            return response()->json(auth()->user());
        } catch (Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh()
    {
        $newToken = auth('cashiers')->refresh();

        return response()->json([
            'data' => auth('cashiers')->user(),
            'authorisation' => [
                'token' => $newToken,
                'type' => 'bearer',
            ]
        ]);
    }
}
