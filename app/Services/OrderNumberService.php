<?php

namespace App\Services;

use Illuminate\Support\Str;

class OrderNumberService
{
    public function salesOrderNumber(): string
    {
        return $this->generate('SO');
    }

    public function purchaseOrderNumber(): string
    {
        return $this->generate('PO');
    }

    private function generate(string $prefix): string
    {
        return sprintf(
            '%s-%s%s',
            $prefix,
            now()->format('YmdHis'),
            Str::upper(Str::random(4))
        );
    }
}
