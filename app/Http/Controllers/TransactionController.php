<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class TransactionController extends Controller
{
    public function add_transaction(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'numeric',
                'cashier_id' => 'required|numeric',
                'total_amount' => 'required|numeric',
            ]);

            DB::beginTransaction();

            $transaction = Transaction::create([
                'user_id' => $request->user_id,
                'cashier_id' => $request->cashier_id || 1,
                'total_amount' => $request->total_amount,
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
