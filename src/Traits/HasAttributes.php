<?php

declare(strict_types=1);

namespace Mralston\Quake\Traits;

use Carbon\Carbon;

trait HasAttributes
{
    protected $dates = [];

    public function fill($attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (!$this->fillable($key)) {
                continue;
            }

            if (in_array($key, $this->dates)) {
                $this->$key = Carbon::parse($value);
                continue;
            }

            $this->$key = $value;
        }

        return $this;
    }

    public function fillable(string $attribute)
    {
        return property_exists($this, $attribute);
    }
}
