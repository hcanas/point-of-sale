<?php

namespace App\Http\Requests;

class ProductFilterRequest extends FilterRequest
{
    protected function allowedSorts(): array
    {
        return ['name', 'barcode', 'stock', 'price'];
    }
}
