<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StockRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class StockCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Stock');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock');
        $this->crud->setEntityNameStrings('stock', 'stocks');

        $this->crud->addField([
            'label' => "Product",
            'type' => 'select',
            'name' => 'product_id', // the db column for the foreign key
            'entity' => 'product', // the method that defines the relationship in your Model
            'attribute' => 'ref', // foreign key attribute that is shown to user
            'model' => "App\Models\Product" // foreign key model
        ]);

        $this->crud->addField([
            'label' => "Invoice",
            'type' => 'select2',
            'name' => 'invoice_id', // the db column for the foreign key
            'entity' => 'invoice', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model' => "App\Models\Invoice" // foreign key model
        ]);

        $this->crud->addColumn([
            'type' => 'select',
            'name' => 'product_id',
            'entity' => 'product',
            'attribute' => 'ref',
            'model' => 'App\Models\Product'
        ]);

        $this->crud->addColumn([
            'type' => 'select',
            'name' => 'invoice_id',
            'entity' => 'invoice',
            'attribute' => 'name',
            'model' => 'App\Models\Invoice'
        ]);
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(StockRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
