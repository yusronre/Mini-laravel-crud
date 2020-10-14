<?php

namespace miniCrud\Http\Controllers;

use Illuminate\Http\Request;
use miniCrud\Category;
use miniCrud\Product;
use File;
use Image;

class ProductController extends Controller
{
    //
    public function index()
    {
    	$products = Product::with('category')->orderBy('created_at', 'DESC')->paginate(10);
    	return view('products.index', compact('products'));
    }


    public function create()
    {
    	$categories = Category::orderBy('name', 'ASC')->get();
    	return view('products.create', compact('categories'));
    }


    public function store(Request $request)
    {
    	$this->validate($request, [
        'code' => 'required|string|max:10|unique:products',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:100',
        'stock' => 'required|integer',
        'price' => 'required|integer',
        'category_id' => 'required|exists:categories,id',
        'photo' => 'nullable|image|mimes:jpg,png,jpeg'
    ]);

    try {
    	$photo = null;
    	if ($request->hasFile('photo')) {
    		$photo = $this->saveFile($request->name, $request->file('photo'));
    	}

    $product = Product::create([
    		'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'stock' => $request->stock,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'photo' => $photo
        ]);

    return redirect(route('produk.index'))
    	->with(['success' => '<strong>' . $product->name . '</strong> Ditambahkan']);
    } catch (\Exception $e) {
    	return redirect()->back()->with(['error' => $e-getMessage()]);
    }
    }

    private function saveFile($name, $photo)
    {
    	//set nama file adalah gabungan antara nama produk dan time(). Ekstensi gambar tetap dipertahankan
    $images = str_slug($name) . time() . '.' . $photo->getClientOriginalExtension();
    //set path untuk menyimpan gambar
    $path = public_path('uploads/product');

    if (!File::isDirectory($path)){
    	File::makeDirectory($path, 0777, true, true);
    }

    Image::make($photo)->save($path . '/' . $images);
    return $images;
    }


    public function destroy($id)
    {
    	$products = Product::FindOrFail($id);

    	if (!empty($products->photo)) {
    		File::delete(public_path('uploads/product/' . $products->photo));
    	}
    	$products->delete();
    	return redirect(route('produk.index'));
    }


    public function edit($id)
    {
    	$product = Product::findOrFail($id);
   		$categories = Category::orderBy('name', 'ASC')->get();
    	return view('products.edit', compact('products', 'categories'));
    }


    public function update(Request $request, $id)
    {
    	 $this->validate($request, [
        'code' => 'required|string|max:10|exists:products,code',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:100',
        'stock' => 'required|integer',
        'price' => 'required|integer',
        'category_id' => 'required|exists:categories,id',
        'photo' => 'nullable|image|mimes:jpg,png,jpeg'
    ]);

    	 try {
    	 	$product = Product::findOrFail($id);
    	 	$photo = $product->photo;

    	 	if ($request->hasFile('photo')) {
    	 		!empty($photo) ? File::delete(public_path('uploads/product/' . $photo)):null;
    	 		$photo = $this ->saveFile($request->name, $request->file('photo'));
    	 	}

    	 	$product->update([
            'name' => $request->name,
            'description' => $request->description,
            'stock' => $request->stock,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'photo' => $photo
        ]);

    	 	return redirect(route('produk.index'))
    	 		->with(['success' => '<strong>' . $product->name . '</strong> Diperbarui']);
    	 } catch (\Exception $e) {
    	 	return redirect()->back()
    	 		->with(['error' => $e->getMessage()]);
    	 }
}
}