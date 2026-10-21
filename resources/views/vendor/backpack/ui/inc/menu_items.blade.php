{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Import Stocks" icon="la la-file-import" :link="backpack_url('import_stock')" />
<x-backpack::menu-item title="Stocks" icon="la la-boxes" :link="backpack_url('stock')" />
<x-backpack::menu-item title="Products" icon="la la-tag" :link="backpack_url('product')" />
<x-backpack::menu-item title="Invoices" icon="la la-file-invoice" :link="backpack_url('invoice')" />
<x-backpack::menu-item title="Rates" icon="la la-coins" :link="backpack_url('rate')" />
