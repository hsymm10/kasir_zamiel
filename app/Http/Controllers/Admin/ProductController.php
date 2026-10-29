<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->orderBy('name', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();

        // Pre-normalize product names for JS duplicate validation
        // Done here to avoid preg_replace inside @json() which causes Blade ParseError
        $productsForValidation = $products->map(function ($p) {
            return [
                'id'      => $p->id,
                'name'    => strtolower(preg_replace('/\s+/', ' ', trim($p->name))),
                'barcode' => $p->barcode ? strtolower(trim($p->barcode)) : null,
            ];
        })->values()->all();

        return view('admin.products.index', compact('products', 'categories', 'productsForValidation'));
    }

    public function store(Request $request)
    {
        // Clean price input from dots (e.g. 13.500 -> 13500)
        if ($request->has('price_buy')) {
            $request->merge(['price_buy' => str_replace('.', '', $request->price_buy)]);
        }
        if ($request->has('price_sell')) {
            $request->merge(['price_sell' => str_replace('.', '', $request->price_sell)]);
        }

        // Normalize spaces in the name input (e.g. "Kopi   Susu " -> "Kopi Susu")
        if ($request->has('name')) {
            $request->merge(['name' => preg_replace('/\s+/', ' ', trim($request->name))]);
        }

        $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'name'            => 'required|string|max:255',
            'barcode'         => 'nullable|string|max:255|unique:products,barcode',
            'image'           => 'nullable|sometimes|file|max:5120',
            'price_buy'       => 'required|numeric|min:0',
            'price_sell'      => 'required|numeric|min:0',
            'discount_persen' => 'nullable|numeric|min:0|max:100',
            'stock'           => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        // Backend Name Duplicate Check (case insensitive & spacing ignored)
        $normalizedName = strtolower(str_replace(' ', '', $request->name));
        $nameExists = Product::whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$normalizedName])->exists();
        if ($nameExists) {
            return redirect()->back()->withErrors(['name' => '⚠️ Produk dengan nama tersebut sudah tersedia.'])->withInput();
        }

        // Image validation and duplicate file check
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'jfif'])) {
                return redirect()->back()->withErrors(['image' => 'Format file gambar tidak valid. Gunakan JPG, JPEG, PNG, WEBP, GIF, atau BMP.'])->withInput();
            }

            // MD5 Hash check to prevent duplicate upload
            $uploadedHash = md5_file($file->getRealPath());
            $existingProducts = Product::whereNotNull('image')->get();
            foreach ($existingProducts as $p) {
                $existingImagePath = public_path($p->image);
                if (file_exists($existingImagePath)) {
                    if (md5_file($existingImagePath) === $uploadedHash) {
                        return redirect()->back()->withErrors(['image' => '⚠️ Gambar ini sudah digunakan oleh produk lain (' . $p->name . ').'])->withInput();
                    }
                }
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $imagePath = 'uploads/products/' . $imageName;
        }

        Product::create([
            'category_id'     => $request->category_id,
            'name'            => $request->name,
            'barcode'         => $request->barcode,
            'image'           => $imagePath,
            'price_buy'       => $request->price_buy,
            'price_sell'      => $request->price_sell,
            'discount_persen' => $request->input('discount_persen', 0),
            'stock'           => $request->stock,
            'expiration_date' => $request->expiration_date,
        ]);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        // Clean price input from dots (e.g. 13.500 -> 13500)
        if ($request->has('price_buy')) {
            $request->merge(['price_buy' => str_replace('.', '', $request->price_buy)]);
        }
        if ($request->has('price_sell')) {
            $request->merge(['price_sell' => str_replace('.', '', $request->price_sell)]);
        }

        // Normalize spaces in the name input (e.g. "Kopi   Susu " -> "Kopi Susu")
        if ($request->has('name')) {
            $request->merge(['name' => preg_replace('/\s+/', ' ', trim($request->name))]);
        }

        $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'name'            => 'required|string|max:255',
            'barcode'         => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'image'           => 'nullable|sometimes|file|max:5120',
            'price_buy'       => 'required|numeric|min:0',
            'price_sell'      => 'required|numeric|min:0',
            'discount_persen' => 'nullable|numeric|min:0|max:100',
            'stock'           => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        // Backend Name Duplicate Check (excluding current product)
        $normalizedName = strtolower(str_replace(' ', '', $request->name));
        $nameExists = Product::where('id', '!=', $product->id)
            ->whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$normalizedName])
            ->exists();
        if ($nameExists) {
            return redirect()->back()->withErrors(['name' => '⚠️ Produk dengan nama tersebut sudah tersedia.'])->withInput();
        }

        // Image validation and duplicate file check
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'jfif'])) {
                return redirect()->back()->withErrors(['image' => 'Format file gambar tidak valid. Gunakan JPG, JPEG, PNG, WEBP, GIF, atau BMP.'])->withInput();
            }

            // MD5 Hash check to prevent duplicate upload (excluding current product)
            $uploadedHash = md5_file($file->getRealPath());
            $existingProducts = Product::whereNotNull('image')->where('id', '!=', $product->id)->get();
            foreach ($existingProducts as $p) {
                $existingImagePath = public_path($p->image);
                if (file_exists($existingImagePath)) {
                    if (md5_file($existingImagePath) === $uploadedHash) {
                        return redirect()->back()->withErrors(['image' => '⚠️ Gambar ini sudah digunakan oleh produk lain (' . $p->name . ').'])->withInput();
                    }
                }
            }
        }

        $data = [
            'category_id'     => $request->category_id,
            'name'            => $request->name,
            'barcode'         => $request->barcode,
            'price_buy'       => $request->price_buy,
            'price_sell'      => $request->price_sell,
            'discount_persen' => $request->input('discount_persen', 0),
            'stock'           => $request->stock,
            'expiration_date' => $request->expiration_date,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && File::exists(public_path($product->image))) {
                File::delete(public_path($product->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $data['image'] = 'uploads/products/' . $imageName;
        }

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image && File::exists(public_path($product->image))) {
            File::delete(public_path($product->image));
        }

        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Produk berhasil dihapus. Data transaksi lama tetap tersimpan.');
    }
}
