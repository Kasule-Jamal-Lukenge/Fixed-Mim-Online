<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    //Listing all Categories
    public function index(){
        return response()->json(Category::all());
    }

    //Adding A New Category (Only Admin)
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        // $category = Category::create($request->all());

        $imagePath = null;
        if($request->hasFile('image')){
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $imagePath?'/storage/'.$imagePath:null,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category
        ], 201);
    }

    //Showing A Single Category
    public function show($id){
        $category = Category::find($id);

        if(! $category){
            return response()->json(['message' => 'Category Not Found'], 404);
        }

        return response()->json($category);
    }

    //Updating A Category
    public function update(Request $request, $id){
        $category = Category::find($id);

        if(! $category){
            return response()->json(['message' => 'Category Not Found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' .$id,
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $imagePath = null;
        if($request->hasFile('image')){
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->image_url = '/storage'.$imagePath;
        }

        // $category->update($request->all());

        $category->update($request->only(['name', 'description']));

        return response()->json([
            'message' => 'Category Updated Successfully.',
            'category' => $category
        ], 200);
    }

    //Deleting A Category
    public function destroy($id){
        $category = Category::find($id);

        if(! $category){
            return response()->json(['message' => 'Category Not Found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category Deleted Successfully.'], 200);
    }
}
