<?php

namespace App\Http\Requests;

class MemberFilterRequest extends FilterRequest
{
    protected function allowedSorts(): array
    {
        return ['id', 'first_name', 'last_name', 'outstanding_balance', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'last_name';
    }
}
