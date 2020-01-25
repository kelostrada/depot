<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return view('admin.import_stock');
    }

    public function importStock(Request $request)
    {
        return $request->input('test');
    }
}
