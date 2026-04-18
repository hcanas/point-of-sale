<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPersonName
{
    protected static array $personNameFields = ['first_name', 'middle_name', 'last_name', 'name_extension'];

    public function initializeHasPersonName(): void
    {
        $this->fillable = array_merge($this->fillable, self::$personNameFields);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $name = $this->first_name;

                if ($this->middle_name) {
                    $name .= ' '.$this->middle_name;
                }

                $name .= ' '.$this->last_name;

                if ($this->name_extension) {
                    $name .= ' '.$this->name_extension;
                }

                return $name;
            }
        );
    }

    protected function formalName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $name = $this->last_name.', '.$this->first_name;

                if ($this->name_extension) {
                    $name .= ' '.$this->name_extension;
                }

                if ($this->middle_name) {
                    $name .= ' '.$this->middle_name;
                }

                return $name;
            }
        );
    }
}
