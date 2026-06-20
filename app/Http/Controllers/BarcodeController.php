<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BarcodeController extends Controller
{
    public function generateForm(): View
    {
        return view('admin.barcodes.generate');
    }
}