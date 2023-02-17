<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;

class PhotoUploaderController extends Controller
{
    public function uploadPhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = 'Product-' . time() . '.' . $file->getClientOriginalExtension();
            $path = '/images/products/';
            $file->move(public_path($path), $file_name);

            return response()->json(['file_path' => $path . $file_name]);
        }
        return response()->json(['error' => 'No Files Found']);
    }
}
