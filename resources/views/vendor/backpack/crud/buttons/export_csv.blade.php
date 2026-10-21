{{-- "Eksport CSV" on the invoice list: free stand-in for Backpack PRO's export buttons (see InvoiceExportController). --}}
@if ($crud->hasAccess('list'))
    <a href="{{ route('admin.invoice.export_csv') }}" class="btn btn-outline-primary" bp-button="export_csv">
        <i class="la la-file-csv"></i> <span>Eksport CSV</span>
    </a>
@endif
