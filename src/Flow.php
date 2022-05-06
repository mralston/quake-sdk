<?php

declare(strict_types=1);

namespace Mralston\Quake;

use Carbon\Carbon;

class Flow extends Record
{
    public ?string $id;
    public ?string $name;
    public ?string $purpose;
    public ?string $closingText;
    public ?int $maxTime;
    public ?string $invitationTemplateId;
    public ?string $companyId;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;

    protected $dates = [
        'createdAt',
        'updatedAt',
    ];
}
