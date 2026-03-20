<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
        <h2>Order Receipt</h2>
        <p>Hello {{ $salesOrder->customer?->full_name }},</p>
        <p>Thank you for your order. Your receipt for <strong>{{ $salesOrder->order_number }}</strong> is attached below in summary form.</p>
        <p><strong>Store:</strong> {{ $salesOrder->store?->name ?? 'N/A' }}</p>
        <p><strong>Officer:</strong> {{ $salesOrder->user?->name }}</p>
        <p><strong>Order date:</strong> {{ optional($salesOrder->order_date)->format('M d, Y') }}</p>
        <p><strong>Payment status:</strong> {{ ucfirst($salesOrder->payment_status) }}</p>
        <p><strong>Payment method:</strong> {{ $salesOrder->payment_method ? strtoupper($salesOrder->payment_method) : 'Credit / Pay later' }}</p>
        @if ($salesOrder->due_date)
            <p><strong>Due date:</strong> {{ optional($salesOrder->due_date)->format('M d, Y') }}</p>
        @endif

        <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; margin-top: 16px;">
            <thead>
                <tr>
                    <th align="left">Item</th>
                    <th align="left">Qty</th>
                    <th align="left">Unit Price</th>
                    <th align="left">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesOrder->items as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 16px;"><strong>Subtotal:</strong> ${{ number_format($salesOrder->subtotal, 2) }}</p>
        <p><strong>Tax:</strong> ${{ number_format($salesOrder->tax, 2) }}</p>
        <p><strong>Discount:</strong> ${{ number_format($salesOrder->discount, 2) }}</p>
        <p><strong>Total:</strong> ${{ number_format($salesOrder->total, 2) }}</p>

        <p>Regards,<br>{{ config('app.name', 'ERP System') }}</p>
    </body>
</html>
