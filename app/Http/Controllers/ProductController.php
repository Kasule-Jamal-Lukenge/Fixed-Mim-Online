<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    //Listing all products
    public function index(){
        $products = Product::with('category:id,name')->get()->map(function($p) {
            $p->category_name = $p->category->name ?? null;
            if ($p->image_url) {
                $p->image_url = asset($p->image_url);
            }
            return $p;
        });
        return response()->json($products);
    }

    //Adding A New Product
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            // 'image' => 'nullable|string'
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // $product = Product::create($request->all());

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'image_url' => $imagePath ? '/storage/' . $imagePath : null,
        ]);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product
        ], 201);
    }

    //Show One Product
    public function show($id){
        $product = Product::with('category')->find($id);

        if(! $product){
            return response()->json(['message' => 'Product Not Found.'], 404);
        }

        return response()->json($product);
    }

    //Update Product
    public function update(Request $request, $id){
        $product = Product::find($id);

        if(! $product){
            return response()->json(['message' => 'Product Not Found.'], 400);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:products,name' .$id,
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'nullable|string'
        ]);

        $product->update($request->all());

        return response()->json([
            'message' => 'Product Updated Successfully.',
            'product' => $product
        ], 200);
    }

    //Deleting A Product
    public function destroy($id){
        $product = Product::find($id);

        if(! $product){
            return response()->json(['message' => 'Product Not Found'], 404);
        }

        $product->delete();

        return response()->json(['message' => "Product Deleted Successfully."]);
    }

    public function popular(){
        //determining the most popular items by use of a sold product count
        $popularProducts = Product::orderBy('sold_count', 'desc')
            ->take(8) // limit to 8 top-selling products
            ->get(['id', 'name', 'price', 'image_url', 'sold_count']);

        return response()->json($popularProducts);
    }

}
