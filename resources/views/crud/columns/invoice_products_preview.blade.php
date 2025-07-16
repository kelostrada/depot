@php
    $stocks = $entry->stocks()->with('product')->get();
@endphp

<div class="products-preview">
    @if($stocks->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped border">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Product Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Price (PLN)</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $stock)
                        @php
                            $product = $stock->product;
                            $realPrice = $stock->getRealPriceAttribute();
                            $total = $realPrice * $stock->quantity;
                        @endphp
                        <tr>
                            <td>{{ $product ? $product->ref : 'N/A' }}</td>
                            <td class="text-truncate" style="max-width: 300px;" title="{{ $product ? $product->name : 'N/A' }}">{{ $product ? $product->name : 'N/A' }}</td>
                            <td>{{ $stock->quantity }}</td>
                            <td>{{ $stock->price }}&nbsp;{{ $stock->currency }}</td>
                            <td>{{ number_format($realPrice, 2) }}&nbsp;PLN</td>
                            <td>{{ number_format($total, 2) }}&nbsp;PLN</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $totalValue = $entry->getValue();
        @endphp
        <div class="alert alert-info mt-3">
            <strong>Total Invoice Value: {{ number_format($totalValue, 2) }} PLN</strong>
        </div>
    @else
        <div class="alert alert-warning">No products found in this invoice.</div>
    @endif
</div>
