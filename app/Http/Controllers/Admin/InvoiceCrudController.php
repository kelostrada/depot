<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\InvoiceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class InvoiceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvoiceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Invoice');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/invoice');
        $this->crud->setEntityNameStrings('invoice', 'invoices');

        $this->crud->addColumn([
            // n-n relationship (with pivot table)
            'label' => "Stocks", // Table column heading
            'type' => "select_multiple",
            'name' => 'stocks', // the method that defines the relationship in your Model
            'entity' => 'stocks', // the method that defines the relationship in your Model
            'attribute' => "product.ref", // foreign key attribute that is shown to user
            'model' => "App\Models\Stock", // foreign key model
            'visibleInTable' => false, // no point, since it's a large text
            'visibleInModal' => true, // would make the modal too big
            'visibleInExport' => false, // not important enough
            'visibleInShow' => true, // sure, why not
        ]);
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();

        $this->crud->addColumn([
            'name'     => 'created_at',
            'label'    => 'Created At',
            'type'     => 'closure',
            'function' => function($entry) {
                return $entry->created_at;
            }   
        ]);

        $this->crud->addColumn([
            'name'     => 'value',
            'label'    => 'Value',
            'type'     => 'model_function',
            'function_name' => 'getValue'
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(InvoiceRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
