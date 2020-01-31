<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;

class StocksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with(['stocks.invoice'])->where('quantity', '>', 0)->get();
        $result = [];

        foreach ($products as $product)
        {
            $sorted_stock = $product->stocks->sortBy('real_price');
            $i = 0;
            $product_quantity = $product->quantity;

            while($product_quantity > 0) {
                $quantity = $product_quantity < $sorted_stock[$i]->quantity ? $product_quantity : $sorted_stock[$i]->quantity;

                $result[] = [
                    'id' => $sorted_stock[$i]->id,
                    'name' => $product->name,
                    'code' => $product->ref,
                    'quantity' => $quantity,
                    'price' => $sorted_stock[$i]->real_price,
                    'total' => round($quantity * $sorted_stock[$i]->real_price, 2),
                ];

                $product_quantity -= $sorted_stock[$i]->quantity;
                $i++;
            }
        }

        return $result;

        $csv = "";

        foreach ($result as $item) {
            $csv .= $item['id'] . ";" . $item['name'] . ";" . $item['quantity'] . ";" . $item['price'] . ";" . $item['total'] . "\n";
        }

        return $csv;
    }
}
