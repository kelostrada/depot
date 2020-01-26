<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return view('admin.import_stock');
    }

    public function importStock(Request $request)
    {
        $invoice_name = $request->get('invoice');
        $invoice_date = $request->get('date');

        $data = $request->file('invoice_file')->get();
        $data = explode("\r\n", $data);
        $data = array_map(function ($line) {
           return explode(';', $line);
        }, $data);
        $data = array_slice($data, 1, sizeof($data));

        $invoice = new Invoice();
        $invoice->name = $invoice_name;
        $invoice->date = $invoice_date;
        $invoice->save();

        $data = array_map(function ($stock) use ($invoice) {
            $product_name = $stock[0];
            $quantity = $stock[1];
            $price = $stock[2];
//            $currency = $stock[3];

            $product = Product::firstOrNew(['name' => $product_name]);

            if (!$product->id) {
                $product->quantity = 0;
                $product->price = 0;
                $product->save();
            }

            $stock = new Stock();
            $stock->product_id = $product->id;
            $stock->invoice_id = $invoice->id;
            $stock->quantity = $quantity;
            $stock->price = $price;
//            $stock->currency = $currency;

            $stock->save();

            return $product;
        }, $data);





        return $data;
    }
}