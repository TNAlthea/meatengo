<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = auth()->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = auth()->user();
        return response()->json([
            'data' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60 . ' seconds'
            ]
        ]);
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'telephone' => 'required|string|min:9|max:15',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => Str::of($request->name)->lower(),
                'email' => Str::of($request->email)->lower(),
                'role' => 'User',
                'telephone' => $request->telephone,
                'points' => 0,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new user failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Create new user failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
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
        $newToken = auth()->refresh();

        return response()->json([
            'data' => auth()->user(),
            'authorisation' => [
                'token' => $newToken,
                'type' => 'bearer',
            ]
        ]);
    }

    //misc
    public function add_points($id, $points)
    {
        try {
            $user = User::findOrFail($id);

            DB::beginTransaction();
            $user->points += $points;

            $user->save();
            DB::commit();

            return response()->json([
                'message' => "points added to $user->name successfully",
                "$user->name's points" => $user->points,
            ], 200);
        } catch (Exception $e){
            return response()->json([
                'message' => "Error when adding $user->name points",
                'reason' => $e->getMessage(),
            ], 500); 
        }
    }
}
