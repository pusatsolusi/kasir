<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products= Products::latest()->paginate(10);
        return view('products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
         return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable|string',
        ]);
        $image = $request->file('image');
        $image->storeAs('/products',$image->hashName());
        Products::create([
            'image' => $image->hashName(),
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
        ]);
        return redirect()->route('products.index')->with('success','Data Berhasil Disimpan');
    }

    /**
     * Display the specified resource.
     */
    
    // model binding
    public function show(Products $products)
    {
        //
       
         return view('products.show',compact('products'));
    }
// expilict ID
    // public function show(string $id)
    // {
    //     //
    //    $products= Products::findOrFail($id);
    //      return view('products.show',compact('products'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
         return view('products.edit',compact('products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Products $products)
    {
        //
        $request->validate([
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable|string',
        ]);
        if($request->hasFile('image')){
            Storage::delete('products/'.$products->image);
            $image = $request->file('image');
            $image->storeAs('products',$image->hashName());
            Products::create([
                'image' => $image->hashName(),
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description,
            ]);
        }else{
            Products::create([
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description,
            ]);
        }
        return redirect()->route('products.index')->with('success','data berhasil diupdare');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products)
    {
        // Ambil nama gambar sebelum produk dihapus dari database
        $imageName = $products->image;

        try {
            // 1. Hapus record produk dari database
            $products->delete();

            // 2. Hapus file gambar dari storage
            if ($imageName && Storage::exists('public/products/' . $imageName)) {
                Storage::delete('public/products/' . $imageName);
            }

            // 3. Redirect ke halaman index dengan pesan sukses
            return redirect()->route('products.index')
                             ->with('success', 'Produk berhasil dihapus!');

        } catch (\Exception $e) {
            // 4. Penanganan error
            // Log error untuk debugging (opsional, tapi disarankan)
            // \Log::error('Error deleting product: ' . $e->getMessage());

            // Redirect dengan pesan error
            return redirect()->back()
                             ->with('error', 'Gagal menghapus produk. Silakan coba lagi.');
        }
    }
}
