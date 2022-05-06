<?php

declare(strict_types=1);

namespace Mralston\Quake;

use Carbon\Carbon;

class Entity extends Record
{
    public ?string $id;
    public ?string $name;
    public ?string $type;
    public ?array $uses;
    public ?array $values;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;

    protected $dates = [
        'createdAt',
        'updatedAt',
    ];
}
