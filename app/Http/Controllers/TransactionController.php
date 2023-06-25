<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:cashiers');
    }

    public function add_transaction(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|string',
                'cashier_id' => 'required|string',
                'payment_method' => 'required|string',
                'total_before_discount' => 'required|numeric',
                'discount' => 'required|numeric',
                'plastic_bag' => 'required|numeric',
                'total' => 'required|numeric',
            ]);

            DB::beginTransaction();

            $transaction = Transaction::create([
                'user_id' => '4916ded3-aab8-4eef-b113-13291c55a781',
                'cashier_id' => $request->cashier_id,
                'payment_method' => $request->payment_method,
                'total_before_discount' => $request->total_before_discount,
                'discount' => $request->discount,
                'plastic_bag' => $request->plastic_bag,
                'total' => $request->total
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new transaction failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Create new transaction failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }
}
