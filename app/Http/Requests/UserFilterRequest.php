<?php

namespace App\Http\Requests;

class UserFilterRequest extends FilterRequest
{
    protected function allowedSorts(): array
    {
        return ['first_name', 'last_name', 'username', 'role', 'created_at'];
    }
}
