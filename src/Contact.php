<?php

declare(strict_types=1);

namespace Mralston\Quake;

use Carbon\Carbon;
use Exception;

class Contact extends Record
{
    public ?string $id;
    public ?string $firstName;
    public ?string $lastName;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;
    public ?string $telephone;
    public array $entities = [];

    protected $dates = [
        'createdAt',
        'updatedAt',
    ];

    public function createFlowInstance(Flow $flow, array $parameters = []): FlowInstance
    {
        if (empty($this->client)) {
            throw new Exception('Client not set.');
        }

        return $this->client->createFlowInstance($flow, $this, $parameters);
    }
}
