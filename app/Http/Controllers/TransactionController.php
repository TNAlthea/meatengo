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
                'user_id' => $request->user_id, 
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

    public function get_all_transaction_history(Request $request)
    {
        try {
            $perPage = 10; // Number of items per page
            $currentPage = $request->input('page', 1); // Get the current page from the request query parameters

            // Fetch the transaction history with pagination
            $transactionHistory = Transaction::select('transactions.id AS transaction_id', 'transactions.sold_at', 'transactions.total_before_discount', 'transactions.discount', 'transactions.total', 'transactions.plastic_bag', 'transactions.payment_method', 'users.name AS customer_name', 'cashiers.name AS cashier_name', 'inventories.name AS product_name', 'transaction_products.quantity')
                ->join('users', 'users.id', '=', 'transactions.user_id')
                ->join('cashiers', 'cashiers.id', '=', 'transactions.cashier_id')
                ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
                ->join('inventories', 'transaction_products.inventory_id', '=', 'inventories.id')
                ->paginate(15, ['*'], 'transactions');

            return response()->json([
                'message' => 'Successfully retrieved transaction history',
                'data' => $transactionHistory
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve transaction history',
                'reason' => $e->getMessage()
            ], 500);
        }
    }
}
