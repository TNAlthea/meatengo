<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins,cashiers');
    }
    public function add_inventory(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'stock' => 'required|numeric',
                'category' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);

            DB::beginTransaction();

            $imageName = $request->file('image')->getClientOriginalName();

            $inventory = Inventory::create([
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
                'sold' => 0,
                'category' => $request->category,
                'image' => $imageName,
                'status' => 'active'
            ]);

            DB::commit();

            $request->file('image')->storeAs('public/images/inventory', $imageName);

            return response()->json([
                'message' => 'Inventory created successfully',
                'data' => $inventory
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Create new inventory failed',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Create new inventory failed',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }
    public function update($product_id, Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'stock' => 'required|numeric',
                'category' => 'required|string|max:255',
                'image' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);

            $inventory = Inventory::findOrFail($request->id);

            if ($request->hasFile('image')) {
                $imageName = $request->file('image')->getClientOriginalName();
                $request->file('image')->storeAs('public/images/inventory', $imageName);
                $inventory->image = $imageName;
            }

            $inventory->name = $request->name;
            $inventory->price = $request->price;
            $inventory->stock = $request->stock;
            $inventory->category = $request->category;

            $inventory->save();

            return response()->json([
                'message' => 'Product ID ' . $request->id . ' Updated Successfully',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'An error occurred while updating a product',
                'reason' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating a product',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function deduct_stock($product_id, $quantity)
    {
        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($product_id);

            // Check if the requested quantity is available in stock
            if ($inventory->stock >= $quantity) {
                // Reduce the stock by the requested quantity
                $inventory->stock -= $quantity;
                $inventory->save();

                DB::commit();

                return true; // Stock deduction successful
            } else {
                DB::rollback();

                return false; // Insufficient stock
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // this just soft deletes product from the table, changing its status to become inactive
    public function delete($product_id)
    {
        try {
            DB::beginTransaction();

            $deleteTarget = Inventory::findOrFail($product_id);
            $deleteTarget->status = 'inactive';
            $deleteTarget->save();
            
            DB::commit();
            return response()->json([
                'message' => $deleteTarget->name . ' status successfully changed to inactive.'
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to change product status to inactive',
                'reason' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAll()
    {
        try {
            // $inventory = Inventory::where('status', '=', 'active')->get()->toArray();
            $inventory = Inventory::all();

            return response()->json([
                'message' => 'successfully retrieve inventory data',
                'data' => $inventory
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve inventory data',
                'reason' => $e->getMessage()
            ], 500);
        }
    }

    public function get($product_id){
        try{
            $target = Inventory::findOrFail($product_id);

            return response()->json([
                'data' => $target,
                'message' => 'successfully returns an item by id: ' . $product_id
            ], 200);
        } catch (Exception $e){
            return response()->json([
                'message' => 'failed to retrieve an item by id: ' . $product_id,
                'reason' => $e->getMessage()
            ], 500);
        }
    }
}
