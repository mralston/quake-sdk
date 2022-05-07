<?php

namespace Mralston\Quake\Facades;

use Illuminate\Support\Facades\Facade;
use Mralston\Quake\Client;
use Mralston\Quake\Contact;
use Mralston\Quake\Entity;
use Mralston\Quake\Flow;
use Mralston\Quake\FlowInstance;

/**
 * @method static setCompanyId(string $companyId)
 * @method static createContact(string $firstName, string $lastName, string $telephone, array $channels = [], ?string $companyId = null): Contact
 * @method static deleteContact(Contact $contact): bool
 * @method static listContacts(): Generator
 * @method static showContact(Contact $contact): Contact
 * @method static createFlowInstance(Flow $flow, Contact $contact, array $parameters = []): FlowInstance
 * @method static showFlowInstance(string $id): FlowInstance
 * @method static inviteFlowInstance(FlowInstance $flowInstance): FlowInstance
 * @method static listFlowInstances(): Generator
 * @method static listFlows(): Generator
 * @method static showFlow(string $id): Flow
 * @method static listEntities(): Generator
 * @method static showEntity(string $id): Entity
 * @method static resolveWebhookChallenge(string $crcToken, ?string $webhookSecret = null)
 *
 * @see \Mralston\Quake\Client
 */
class Quake extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
