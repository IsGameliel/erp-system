<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;

class InventoryService
{
    public function applySalesOrder(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status !== SalesOrder::STATUS_DELIVERED) {
            return;
        }

        foreach ($salesOrder->items as $item) {
            $item->product()->decrement('stock_quantity', $item->quantity);
        }
    }

    public function revertSalesOrder(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status !== SalesOrder::STATUS_DELIVERED) {
            return;
        }

        foreach ($salesOrder->items as $item) {
            $item->product()->increment('stock_quantity', $item->quantity);
        }
    }

    public function applyPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_RECEIVED) {
            return;
        }

        foreach ($purchaseOrder->items as $item) {
            $item->product()->increment('stock_quantity', $item->quantity);
        }
    }

    public function revertPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_RECEIVED) {
            return;
        }

        foreach ($purchaseOrder->items as $item) {
            $item->product()->decrement('stock_quantity', $item->quantity);
        }
    }
}
