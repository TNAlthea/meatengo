<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins')->only('get_as_admin, find_by_name');
        $this->middleware('auth:cashiers')->only('get_as_cashier, find_by_name');
    }

    public function get_as_admin()
    {
        try {
            $users = User::all();
            
            if (!$users){
                return response()->json([
                    'message' => 'user database is empty'
                ], 204);
            }
            return response()->json([
                'message' => 'successfully fetch all user data',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'error when trying to fetch user data',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function get_as_cashier()
    {
        try {
            $users = User::select('id', 'name', 'email', 'created_at')->get();;
             
            if (!$users){
                return response()->json([
                    'message' => 'user database is empty'
                ], 204);
            }
            return response()->json([
                'message' => 'successfully fetch all user data',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'error when trying to fetch user data',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }
}
