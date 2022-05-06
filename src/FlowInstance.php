<?php

declare(strict_types=1);

namespace Mralston\Quake;

use Carbon\Carbon;
use Exception;

class FlowInstance extends Record
{
    public ?string $id;
    public ?string $flowId;
    public ?string $contactId;
    public ?string $state;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;
    public ?Carbon $invitedAt;
    public ?Carbon $startedAt;
    public ?Carbon $completedAt;
    public ?Carbon $expiresAt;
    public array $entities;

    protected $dates = [
        'createdAt',
        'updatedAt',
        'invitedAt',
        'startedAt',
        'completedAt',
        'expiresAt',
    ];

    public function invite(): FlowInstance
    {
        if (empty($this->client)) {
            throw new Exception('Client not set.');
        }

        $this->client->inviteFlowInstance($this);

        return $this;
    }
}
