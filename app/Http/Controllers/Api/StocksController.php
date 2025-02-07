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

    public function all()
    {
        return $this->getAllStocks();
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
                $vat = $sorted_stock[$i]->currency == 'PLN' ? round($rated_price * 0.23, 2) : 0;

                $result[] = [
                    'id' => $sorted_stock[$i]->id,
                    'name' => str_replace("\n", "", $product->name),
                    'ref' => $product->ref,
                    'invoice' => $sorted_stock[$i]->invoice->name,
                    'date' => $sorted_stock[$i]->invoice->date,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => round($quantity * $price, 2),
                    'rated_price' => $rated_price,
                    'rated_total' => $rated_price * $quantity,
                    'vat' => $vat,
                    'vat_total' => $vat * $quantity,
                    'currency' => $sorted_stock[$i]->currency
                ];

                $product_quantity -= $sorted_stock[$i]->quantity;
                $i++;
            }
        }

        return $result;
    }
    private function getAllStocks() {
        $products = Product::with(['stocks.invoice'])->get();
        $result = [];

        foreach ($products as $product)
        {
            $sorted_stock = $product->stocks->sortByDesc(function($stock, $key) {
                return $stock->invoice->date;
            })->all();
            $sorted_stock = array_values($sorted_stock);

            foreach ($sorted_stock as $stock) {
                $rate = Rate::where('date', '<', $stock->invoice->date)
                    ->where('currency', $stock->currency)
                    ->orderByDesc('date')
                    ->limit(1)
                    ->first();

                if ($rate) {
                    $rate = $rate->value;
                } else {
                    $rate = 1.0;
                }

                $price = (float)$stock->price;
                $rated_price = round($price * $rate, 2);
                $vat = $stock->currency == 'PLN' ? round($rated_price * 0.23, 2) : 0;

                $result[] = [
                    'id' => $stock->id,
                    'name' => $product->name,
                    'ref' => $product->ref,
                    'invoice' => $stock->invoice->name,
                    'date' => $stock->invoice->date,
                    'quantity' => $stock->quantity,
                    'price' => $price,
                    'total' => round($stock->quantity * $price, 2),
                    'rated_price' => $rated_price,
                    'rated_total' => $rated_price * $stock->quantity,
                    'vat' => $vat,
                    'vat_total' => $vat * $stock->quantity,
                    'currency' => $stock->currency
                ];
            }
        }

        $sort_by = request('sort_by');
        $sort_direction = request('sort_direction', 'asc');

        if ($sort_by) {
            $result = collect($result)->sortBy($sort_by);
            if ($sort_direction === 'desc') {
                $result = $result->reverse();
            }
            $result = $result->values()->all();
        }

        return $result;
    }
}
