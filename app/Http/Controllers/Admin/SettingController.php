<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings configuration page.
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Update the application and store configurations.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'store_name' => 'required|string|max:100',
            'store_address' => 'required|string|max:255',
            'store_phone' => 'required|string|max:30',
            'business_type' => 'required|in:sembako,fnb,fashion,retail',
            'color_primary' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'color_secondary' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'color_accent' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'color_danger' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'store_qris' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Capture toggle configurations (checkboxes)
        $toggles = [
            'enable_barcode' => $request->has('enable_barcode') ? '1' : '0',
            'enable_expiration' => $request->has('enable_expiration') ? '1' : '0',
            'enable_stock' => $request->has('enable_stock') ? '1' : '0',
        ];

        // Exclude store_qris file object from main loops
        $fields = collect($validated)->except('store_qris')->all();
        $data = array_merge($fields, $toggles);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Handle QRIS File Upload
        if ($request->hasFile('store_qris')) {
            $file = $request->file('store_qris');
            $filename = 'qris_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/qris'), $filename);

            // Delete old QRIS image if exists
            $oldQris = Setting::getByKey('store_qris');
            if ($oldQris && file_exists(public_path($oldQris))) {
                @unlink(public_path($oldQris));
            }

            Setting::updateOrCreate(
                ['key' => 'store_qris'],
                ['value' => 'uploads/qris/' . $filename]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan kasir berhasil disimpan!');
    }
}
