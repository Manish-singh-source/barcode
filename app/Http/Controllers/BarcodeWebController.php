<?php

namespace App\Http\Controllers;

use App\Models\BarcodeGeneration;
use Illuminate\View\View;

class BarcodeWebController extends Controller
{
    public function index(): View
    {
        return view('admin.barcodes.index');
    }

    public function show(int $id): View
    {
        $barcode = BarcodeGeneration::query()
            ->with(['product', 'user'])
            ->findOrFail($id);

        return view('admin.barcodes.show', compact('barcode'));
    }
}