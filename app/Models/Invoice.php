<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'invoices';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['name', 'date'];
    protected $hidden = ['created_at', 'updated_at'];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function stocks()
    {
        return $this->hasMany('App\Models\Stock');
    }

    public function getValue()
    {
        // var_dump($this->stocks()->get()->toArray());
        // return §1;
        return $this->stocks()->get()->reduce(function($acc, $stock) {
            return $acc + $stock->getRealPriceAttribute() * $stock->quantity;
        }, 0);
    }

    public function getProductsPreviewHtml()
    {
        $stocks = $this->stocks()->with('product')->get();
        $html = '<div class="products-preview">';

        if ($stocks->count() > 0) {
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-bordered table-striped border">';
            $html .= '<thead><tr>';
            $html .= '<th>Ref</th>';
            $html .= '<th>Product Name</th>';
            $html .= '<th>Qty</th>';
            $html .= '<th>Price</th>';
            $html .= '<th>Price (PLN)</th>';
            $html .= '<th>Total</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($stocks as $stock) {
                $product = $stock->product;
                $realPrice = $stock->getRealPriceAttribute();
                $total = $realPrice * $stock->quantity;

                $html .= '<tr>';
                $html .= '<td>' . ($product ? $product->ref : 'N/A') . '</td>';
                $html .= '<td>' . ($product ? $product->name : 'N/A') . '</td>';
                $html .= '<td>' . $stock->quantity . '</td>';
                $html .= '<td>' . $stock->price . ' ' . $stock->currency . '</td>';
                $html .= '<td>' . number_format($realPrice, 2) . ' PLN</td>';
                $html .= '<td>' . number_format($total, 2) . ' PLN</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';

            // Add total summary
            $totalValue = $this->getValue();
            $html .= '<div class="alert alert-info mt-3">';
            $html .= '<strong>Total Invoice Value: ' . number_format($totalValue, 2) . ' PLN</strong>';
            $html .= '</div>';
        } else {
            $html .= '<div class="alert alert-warning">No products found in this invoice.</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
