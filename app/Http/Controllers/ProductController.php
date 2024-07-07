<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::get();
        return response()->json(['success' => true,'products' => $products , 'count' => count($products)]);
    }
    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'product_specification' => 'nullable|string',
            'product_price' => 'required|numeric|min:0',
            'quantity' => 'nullable|',
            'product_type' => 'required|string|in:Books,Coloring Books,Medications,Prosthetic Tools',
            'product_image' => 'nullable|image|max:2048',

        ]);
        if ($validator->fails()) {
            return response()->json(['success'=> false ,'errors' => $validator->errors()], 422);
        }
        $product = new Product();
        $product->product_name = $request->input('product_name');
        $product->product_description = $request->input('product_description');
        $product->product_specification = $request->input('product_specification');
        $product->product_price = $request->input('product_price');
        $product->product_type = $request->input('product_type');
        $product->quantity = $request->input('quantity');

        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
            $product->product_image = $imagePath;
        }

        $product->save();

        $product->admin()->attach($user_id);

        return response()->json(['success' => true,'message' => 'Product created successfully', 'product' => $product], 201);
    }
    /* -------------------------------------------------------------------------- */
    public function show(request $request)
    {
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);
        if ($product == null) {
            return response()->json(['success'=> false ,"message" => "product not found"], 404);
        }
        return response()->json(['success' => true,"product" => $product]);
    }
    /* -------------------------------------------------------------------------- */
    public function edit(string $product_id)
    {
        $product = Product::findOrFail($product_id);
        return response()->json(['success' => true,"product" => $product]);
    }
    /* -------------------------------------------------------------------------- */
    public function update(Request $request)
    {

        $product_id = $request->input('product_id');
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['success'=> false ,'error' => 'Product not found'], 404);
        }

        if ($request->filled('product_name')) {
            $product->product_name = $request->input('product_name');
        }
        if ($request->filled('product_description')) {
            $product->product_description = $request->input('product_description');
        }
        if ($request->filled('product_specification')) {
            $product->product_specification = $request->input('product_specification');
        }
        if ($request->filled('product_price')) {
            $product->product_price = $request->input('product_price');
        }
        if ($request->filled('product_type')) {
            $product->product_type = $request->input('product_type');
        }
        if ($request->filled('quantity')) {
            $product->quantity = $request->input('quantity');
        }
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
            $product->product_image = $imagePath;
        }

        $product->save();

        return response()->json(['success' => true,'message' => 'Product updated successfully', 'product' => $product]);
    }
    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);

        if (!$product) {
            return response()->json(['success'=> false ,'error' => 'Product not found'], 404);
        }

        $product->delete();
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');

        return response()->json(['success' => true,'message' => 'Product deleted successfully']);
    }
    /* -------------------------------------------------------------------------- */


}
