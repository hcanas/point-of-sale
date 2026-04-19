<?php

namespace App\Http\Requests;

class UserFilterRequest extends FilterRequest
{
    protected function allowedSorts(): array
    {
        return ['last_name', 'username', 'role'];
    }

    protected function defaultSort(): string
    {
        return 'last_name';
    }
}
