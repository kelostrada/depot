<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

function round_up ( $value, $precision ) {
    $pow = pow ( 10, $precision );
    return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow;
}

class StockController extends Controller
{
    public function index()
    {
        return view('admin.import_stock');
    }

    public function importStock(Request $request)
    {
        $company = $request->get('company');
        $content = $request->get('invoice_content');

        if ($company == "Blackfire New") {
            $this->addBlackfireNewStock($content);
        }

        if ($company == "Blackfire") {
          $this->addBlackfireStock($content);
        }

        if ($company == "Ynaris") {
          $this->addYnarisStock($content);
        }

        return redirect('admin/stock');
    }

    private function addBlackfireNewStock($content) {
        // find invoice name and date
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//h1[contains(text(), 'Posted invoice detail')]");
        $invoice_name = trim(str_replace('Posted invoice detail', '', $crawler->text()));
        $invoice_name = trim(str_replace(chr(194) . chr(160), '', $invoice_name));

        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody/tr[td[text()='Document date']]/td[2]");
        $carbon = Carbon::createFromFormat('d/m/Y', $crawler->text());
        $invoice_date = $carbon->isoFormat("YYYY-MM-DD");

        $invoice = Invoice::firstOrNew(['name' => $invoice_name]);
        $invoice->name = $invoice_name;
        $invoice->date = $invoice_date;

        // find tables that have headers (only invoice related tables have headers in blackfire invoices)
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table[thead/tr/th]");

        $data = [];

        foreach ($crawler as $table) {
            $table = new Crawler($table);

            // Get headers first
            $headers = [];
            $ths = $table->filterXPath("table/thead/tr/th");
            foreach ($ths as $th) {
                $headers[] = trim($th->nodeValue);
            }

            // Get data rows
            $trs = $table->filterXPath("table/tbody/tr[td]");
            foreach($trs as $tr) {
                $row = [];
                $tr = new Crawler($tr);
                $tds = $tr->filter('td');
                foreach ($tds as $index => $td) {
                    $row[$headers[$index]] = $td->nodeValue;
                }
                $data[] = $row;
            }
        }


        $data = array_filter($data, function($row) {
            return count($row) >= 9;
        });

        $data = array_values($data);

        $data = array_map(function($row) {
            $ref = trim($row["Item No."]);
            $name = trim($row["Title"]);
            $quantity = trim($row["Qty"]);

            if (array_key_exists("Discount", $row)) {
                $discount = floatval(str_replace(["%", ","], ['', "."], trim($row["Discount"])));
            } else {
                $discount = 0;
            }

            $price = round_up(floatval(trim(str_replace(["€", ","], ["", "."], $row["Price"]))) * (100.0 - $discount) / 100.0, 2);

            return [
                'ref' => $ref,
                'upc' => '',
                'name' => $name,
                'quantity' => $quantity,
                'price' => $price,
                'currency' => 'EUR'
            ];
        }, $data);

        $grouped_data = [];
        foreach ($data as $item) {
            $ref = $item['ref'];
            if (!isset($grouped_data[$ref])) {
                $grouped_data[$ref] = $item;
            } else {
                $grouped_data[$ref]['quantity'] += $item['quantity'];
            }
        }
        $data = array_values($grouped_data);

        $invoice->save();
        $this->addProductsStocks($data, $invoice);
    }

    private function addBlackfireStock($content) {
        // find invoice name and date
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody/tr[td[text()='Invoice ID']]/td[2]");
        $invoice_name = $crawler->text();

        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody/tr[td[text()='Invoice Date']]/td[2]");
        $carbon = Carbon::createFromFormat('d.m.Y', $crawler->text());
        $invoice_date = $carbon->isoFormat("YYYY-MM-DD");

        $invoice = Invoice::firstOrNew(['name' => $invoice_name]);
        $invoice->name = $invoice_name;
        $invoice->date = $invoice_date;
        $invoice->save();

        // find tables that have headers (only invoice related tables have headers in blackfire invoices)
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody[tr/th]");
        $data = [];

        foreach ($crawler as $tbody) {
            $tbody = new Crawler($tbody);
            $tbody = $tbody->filterXPath("tbody/tr[td]");
            foreach($tbody as $tr) {
                $row = [];
                $tr = new Crawler($tr);
                $tr = $tr->filter('td');
                foreach ($tr as $td) {
                    $row[] = $td->nodeValue;
                }
                $data[] = $row;
            }
        }

        $data = array_filter($data, function($row) {
            return count($row) == 7;
        });

        $data = array_values($data);

        $data = array_map(function($row) {
            $ref_upc = explode("\n", $row[1]);
            $ref = trim($ref_upc[0]);
            $upc = isset($ref_upc[1]) ? trim($ref_upc[1]) : "";
            $name = $row[2];

            if (strpos($name, "UP - ") === 0) {
                $ref = "UP-{$ref}";
            }

            if (strpos($name, "Dragon Shield") === 0) {
                $ref = "AT-{$ref}";
            }

            return [
                'ref' => $ref,
                'upc' => $upc,
                'name' => $row[2],
                'quantity' => $row[4],
                'price' => floatval(trim(str_replace(["€", ","], ["", "."], $row[5]))),
                'currency' => 'EUR'
            ];
        }, $data);

        $this->addProductsStocks($data, $invoice);
    }

    private function addYnarisStock($content) {
      // echo $content;
        // find invoice name and date
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody/tr[td/p[text()='Facture']]/td[2]");
        $invoice_name = $crawler->text();

        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table/tbody/tr[td/p[text()='Date de facturation']]/td[2]");
        $carbon = Carbon::createFromFormat('d/m/Y', $crawler->text());
        $invoice_date = $carbon->isoFormat("YYYY-MM-DD");

        $invoice = Invoice::firstOrNew(['name' => $invoice_name]);
        $invoice->name = $invoice_name;
        $invoice->date = $invoice_date;
        $invoice->save();

        // find tables that have headers (only invoice related tables have headers in blackfire invoices)
        $crawler = new Crawler($content);
        $crawler = $crawler->filterXPath("//table[thead/tr/th]/tbody");
        $data = [];

        foreach ($crawler as $tbody) {
            $tbody = new Crawler($tbody);
            $tbody = $tbody->filterXPath("tbody/tr[td]");
            foreach($tbody as $tr) {
                $row = [];
                $tr = new Crawler($tr);
                $tr = $tr->filter('td');
                foreach ($tr as $td) {
                    $row[] = $td->nodeValue;
                }
                $data[] = $row;
            }
        }

        $data = array_filter($data, function($row) {
            return count($row) == 5;
        });

        $data = array_values($data);

        $data = array_map(function($row) {
            $ref = str_replace(" ", "-", trim($row[0]));

            if (strpos($ref, "VGE-V-") === 0) {
                if (strpos($ref, "-SP") !== false) {
                    $ref = str_replace("-SP", "SP", $ref);
                }
                $ref = "{$ref}-EN";
            }

            return [
                'ref' => $ref,
                'upc' => '',
                'name' => trim($row[1]),
                'quantity' => (int)trim($row[2]),
                'price' => floatval(trim(str_replace(["€", ","], ["", "."], $row[3]))),
                'currency' => 'EUR'
            ];
        }, $data);

        $this->addProductsStocks($data, $invoice);
    }

    private function addProductsStocks($data, $invoice) {
        $data = array_filter($data, function($row) {
            return $row['price'] > 0;
        });

        $data = array_values($data);

        foreach ($data as $product_data) {
            $products = Product::where('ref', $product_data['ref'])
                ->orWhere(function($q) use ($product_data) {
                    return $q->where('upc', $product_data['upc'])->where('upc', '!=', '')->whereNotNull('upc');
                })->get();
            $foundProductsCount = count($products);

            if ($foundProductsCount > 1) {
                throw new \Exception("Product conflict: {$product_data['ref']} {$product_data['upc']}");
            }

            if ($foundProductsCount == 1) {
                $product = $products[0];
                if (empty($product->upc)) {
                    $product->upc = $product_data['upc'];
                    $product->save();
                }
            } else {
                $product = new Product();
                $product->quantity = 0;
                $product->price = 0;
                $product->name = $product_data['name'];
                $product->ref = $product_data['ref'];
                $product->upc = $product_data['upc'];
                $product->save();
            }

            $stock = $invoice->stocks()->where('product_id', '=', $product->id)->first();
            if (!$stock) {
                $stock = new Stock();
                $stock->product_id = $product->id;
                $stock->invoice_id = $invoice->id;
            }
            $stock->quantity = $product_data['quantity'];
            $stock->price = $product_data['price'];
            $stock->currency = $product_data['currency'];
            $stock->save();
        }
    }
}
