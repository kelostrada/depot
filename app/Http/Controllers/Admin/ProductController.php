<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function quantity(Request $request, int $id)
    {
        $product = Product::find($id);
        if ($product) {
            $value = (int)$request->get('value');
            $product->quantity += $value;
            $product->save();
            return $product;
        } else {
            return 404;
        }
    }
}
