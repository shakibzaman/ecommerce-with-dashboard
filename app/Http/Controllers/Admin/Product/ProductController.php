<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function App\Helpers\createProductTransferLog;
use function App\Helpers\createStockHistory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $store_type = ['1' => 'Cold Storage', '2' => 'Office'];
        $store_type = ['' => '-- Please Select One --'] + $store_type;
        $categories = Category::all();
        $units = Unit::all();
        $statuses = ['1' => 'Active', '0' => 'Inactive'];
        $products = Product::with('category', 'unit')->orderBy('id', 'desc')->paginate(20);
        return view('products.index', compact('products', 'categories', 'units', 'statuses', 'store_type'))->with('i', ($request->input('page', 1) - 1) * 20);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'wholesell_price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:100',
            'status' => 'required|boolean',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                logger('Image is avai;able');
                $imageName = 'product-' . time() . '.' . $request->image->extension();
                $request->image->move(public_path('images/products/thumb'), $imageName);
                $validatedData['image'] = $imageName;
            }
            Product::create($validatedData);
        } catch (Exception $e) {
            info('Product store error', [$e]);
            return $e;
        }


        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'wholesell_price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:100',
            'status' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image && file_exists(public_path('images/products/thumb/' . $product->image))) {
                unlink(public_path('images/products/thumb/' . $product->image));
            }

            // Store the new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/products/thumb'), $imageName);
            $validatedData['image'] = $imageName;
        }

        $product->update($validatedData);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function getStock($storeId)
    {
        // $product = 
    }
    public function transferView($product_id)
    {
        $store_type = ['cold_storage_quantity' => 'Cold Storage', 'office_quantity' => 'Office'];
        $store_type = ['' => '-- Please Select One --'] + $store_type;
        $product = Product::where('id', $product_id)->first();
        return view('products.stock.transfer', compact('product', 'store_type'));
    }

    public function productTransfer(Request $request)
    {
        DB::beginTransaction();
        try {
            $fromStock = $request->from_store_id;
            $toStock = $request->to_store_id;
            $product = Product::findOrFail($request->id);

            $transferQuantity = $request->quantity;

            // Stock records
            $fromStockQuantity = $product->$fromStock;
            $toStockQuantity = $product->$toStock;

            // Check available product 
            if ($fromStockQuantity < $transferQuantity) {
                return response()->json(['status' => 400, 'message' => 'Insufficient stock for transfer.']);
            }

            // Update stocks
            $product->decrement($fromStock, $transferQuantity);  // Decrement from stock
            $product->increment($toStock, $transferQuantity);    // Increment to stock

            // Prepare log data
            $logData = [
                'product_id' => $product->id,
                'quantity' => $transferQuantity,
                'transfer_from' => $fromStock,
                'transfer_to' => $toStock,
                'transfer_pre_quantity' => $fromStockQuantity,
                'transfer_post_quantity' => $product->$fromStock,
                'received_pre_quantity' => $toStockQuantity,
                'received_post_quantity' => $product->$toStock,
                'transfer_by' => Auth::id(),
                'reason' => $request->note,
            ];

            // Create Product Transfer Log
            $transferLog = createProductTransferLog($logData);
            if ($transferLog['status'] != 200) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Failed to create product transfer log.']);
            }

            // Stock history for from stock (decrement)
            $fromStockHistory = [
                'store_id' => $fromStock,
                'quantity' => $transferQuantity,
                'product_id' => $product->id,
                'previous_qty' => $fromStockQuantity,
                'update_qty' => $product->$fromStock,
                'note' => 'Manual Stock Transfer (Deduct) -> ' . $request->note,
            ];
            $fromHistory = createStockHistory($fromStockHistory);
            if ($fromHistory['status'] != 200) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => $fromHistory['message']]);
            }

            // Stock history for to stock (increment)
            $toStockHistory = [
                'store_id' => $toStock,
                'quantity' => $transferQuantity,
                'product_id' => $product->id,
                'previous_qty' => $toStockQuantity,
                'update_qty' => $product->$toStock,
                'note' => 'Manual Stock Transfer (Increment) -> ' . $request->note,
            ];
            $toHistory = createStockHistory($toStockHistory);
            if ($toHistory['status'] != 200) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => $toHistory['message']]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Product transfer successful');
            return response()->json(['status' => 200, 'message' => 'Product transfer successful.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 400, 'message' => $e->getMessage()]);
        }
    }
}
