<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::get('import_stock', 'StockController@index');
    Route::post('import_stock', 'StockController@importStock')->name('admin.import_stock');
    Route::post('product/{id}/quantity', 'ProductController@quantity');
    Route::get('invoice/export-csv', 'InvoiceExportController')->name('admin.invoice.export_csv');

    Route::crud('stock', 'StockCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::crud('invoice', 'InvoiceCrudController');
    Route::crud('rate', 'RateCrudController');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
