<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Eksport CSV" button on the invoice list: a free stand-in for Backpack PRO's
 * export buttons (Backpack 4 exported the list from the browser).
 *
 * Same columns as the list: Name, Date, Created At, Value. All invoices,
 * newest first (the list's default order). Formatted for Polish Excel:
 * UTF-8 with BOM, ';' separator, CRLF line ends, decimal comma, ISO dates.
 */
class InvoiceExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM: Excel otherwise reads UTF-8 as cp1250

            $this->writeRow($out, ['Name', 'Date', 'Created At', 'Value']);

            Invoice::query()->orderByDesc('id')->chunk(200, function ($invoices) use ($out) {
                foreach ($invoices as $invoice) {
                    $this->writeRow($out, [
                        $this->text($invoice->name),
                        $invoice->date,
                        (string) $invoice->created_at,
                        number_format((float) $invoice->getValue(), 2, ',', ''),
                    ]);
                }
            });

            fclose($out);
        }, 'invoices-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  resource  $out
     */
    private function writeRow($out, array $fields): void
    {
        fputcsv($out, $fields, ';', '"', '', "\r\n");
    }

    /**
     * Keep Excel from evaluating free text as a formula (CSV injection).
     */
    private function text(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
