<?php

namespace App\Http\Controllers;

use App\Models\TransactionProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class TransactionProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:cashiers');
    }
    public function add_transaction_product(Request $request)
    {
        try {
            $request->validate([
                'quantity' => 'required|numeric',
                'transaction_id' => 'required|numeric',
                'inventory_id' => 'required|numeric',
            ]);

            DB::beginTransaction();

            $transactionProduct = TransactionProduct::create(
                [
                    'quantity' => $request->quantity,
                    'transaction_id' => $request->transaction_id,
                    'inventory_id' => $request->inventory_id
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Transaction created successfully',
                'data' => $transactionProduct
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new transaction product failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Create new transaction product failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }
}
