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

        $invoice = Invoice::firstOrNew(['name' => $invoice_name]);
        $invoice->name = $invoice_name;
        $invoice->date = $invoice_date;
        $invoice->save();

        $data = array_map(function ($stock) use ($invoice) {
            $product_ref = trim($stock[0]);
            $product_name = trim($stock[1]);
            $quantity = $stock[2];
            $price = $stock[3];
            $currency = trim($stock[4]);

            $product = Product::firstOrNew(['ref' => $product_ref]);

            if (!$product->id) {
                $product->quantity = 0;
                $product->price = 0;
                $product->name = $product_name;
                $product->ref = $product_ref;
                $product->save();
            }

            if($invoice->stocks()->where('product_id', '=', $product->id)->get()->isEmpty()) {
                $stock = new Stock();
                $stock->product_id = $product->id;
                $stock->invoice_id = $invoice->id;
                $stock->quantity = $quantity;
                $stock->price = $price;
                $stock->currency = $currency;

                $stock->save();
            }

            return $product;
        }, $data);

        return redirect('admin/stock');
    }
}
