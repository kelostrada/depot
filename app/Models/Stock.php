<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'stocks';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    protected $hidden = ['created_at', 'updated_at'];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Models\Invoice');
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
    public function getRealPriceAttribute()
    {
        $date = $this->invoice->date;
        $carbon = new Carbon($date);
        $rate = $this->getRate($carbon, $this->currency);
        $price = $this->price;
//        $price = str_replace(',', '.', $this->price);
        return round((double)$price * (double)$rate->value, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE
    |--------------------------------------------------------------------------
    */
    private function getRate(Carbon $carbon, $currency)
    {
        $rate = Rate::where(['date' => $carbon->toDateString(), 'currency' => $currency])->first();
        if ($rate) return $rate;
        $carbon->subDay();
        return $this->getRate($carbon, $currency);
    }

}
