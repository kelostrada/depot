<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Product');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/product');
        $this->crud->setEntityNameStrings('product', 'products');

        $this->crud->addColumn([
            // run a function on the CRUD model and show its return value
            'name' => "stock",
            'label' => "Stocked", // Table column heading
            'type' => "model_function",
            'function_name' => 'stockQuantity', // the method in your Model
            'orderable' => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                $stocked = DB::table('products')
                    ->join('stocks', 'products.id', '=', 'stocks.product_id')
                    ->select('products.id', DB::raw('SUM(stocks.quantity) as stocked_quantity'))
                    ->groupBy('products.id');

                return $query
                    ->joinSub($stocked, 'stocked', function ($join) {
                        $join->on('products.id', '=', 'stocked.id');
                    })
                    ->orderBy('stocked.stocked_quantity', $columnDirection)->select('products.*');
            }
        ]);

        $this->crud->addColumn([
            'name' => 'quantity',
            'label' => 'Quantity',
            'type' => 'closure',
            'function' => function($entry) {
                $button_sub = "<button class='btn btn-sm btn-outline-info quantity-sub' data-id='$entry->id'>-</button>";
                $button_add = "<button class='btn btn-sm btn-outline-info quantity-add' data-id='$entry->id'>+</button>";
                return $button_sub . " <span data-id='$entry->id' class='quantity'>" . $entry->quantity . "</span> " . $button_add;
            }
        ]);

        $this->crud->addColumn([
            'name' => 'name',
            'limit' => 150
        ]);
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ProductRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
