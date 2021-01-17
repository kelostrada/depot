<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Rate;
use App\Models\Stock;
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
        return $this->getStocks();
    }

    /**
     * Download CSV with stocks
     *
     * @return \Illuminate\Http\Response
     */
    public function csv()
    {
        $result = $this->getStocks();
        $csv = "";

        foreach ($result as $item) {
            $csv .= $item['id'] . ";" . $item['name'] . ";" . $item['quantity'] . ";";
            $csv .= $item['rated_price'] . ";" . $item['rated_total'] . ";";
            $csv .= $item['vat_total'] . "\n";
        }

        return $csv;
    }

    private function getStocks() {
        $products = Product::with(['stocks.invoice'])->where('quantity', '>', 0)->get();
        $result = [];

        foreach ($products as $product)
        {
            $sorted_stock = $product->stocks->sortByDesc(function($stock, $key) {
                return $stock->invoice->date;
            })->all();
            $sorted_stock = array_values($sorted_stock);

            $i = 0;
            $product_quantity = $product->quantity;

            while ($product_quantity > 0) {
                $quantity = $product_quantity < $sorted_stock[$i]->quantity ? $product_quantity : $sorted_stock[$i]->quantity;

                $rate = Rate::where('date', '<', $sorted_stock[$i]->invoice->date)
                    ->where('currency', $sorted_stock[$i]->currency)
                    ->orderByDesc('date')
                    ->limit(1)
                    ->first();

                if ($rate) {
                    $rate = $rate->value;
                } else {
                    $rate = 1.0;
                }

                $price = (float)$sorted_stock[$i]->price;
                $rated_price = round($price * $rate, 2);

                $result[] = [
                    'id' => $sorted_stock[$i]->id,
                    'name' => $product->name,
                    'ref' => $product->ref,
                    'invoice' => $sorted_stock[$i]->invoice->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => round($quantity * $price, 2),
                    'rated_price' => $rated_price,
                    'rated_total' => $rated_price * $quantity,
                    'vat_total' => ($sorted_stock[$i]->currency == 'PLN' ? round($rated_price * $quantity * 0.23, 2) : 0),
                    'currency' => $sorted_stock[$i]->currency
                ];

                $product_quantity -= $sorted_stock[$i]->quantity;
                $i++;
            }
        }

        return $result;
    }
}
