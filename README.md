# Quake SDK

This is PHP SDK for Quake.
The Quake platform provides WhatsApp and SMS message flow capabilities useful for recruitment, lead qualification and
other automated two-way communication.

Visit the [Quake](https://www.quake.co.uk/) for more information.

---
## Installation

You can install the package via composer:

```bash
composer require mralston/quake-sdk
```
## Basic Usage

```php

use Mralston\Quake\Client;
use Mralston\Quake\Contact;
use Mralston\Quake\Flow;

// Log in
$client = new Client(
    $username,
    $password,
    $companyId
);

// Create contact
$contact = $client->createContact(
    $firstName,
    $lastName,
    $mobileNumber
)

// Delete contact
$client->deleteContact($contact);

// Create flow instance
$flowInstance = $client->createFlowInstance(
    $flow,
    $contact,
    $parameters
);

// Fetch flow instance by ID
$flowInstance = $client->showFlowInstance($id);

// Invite a flow instance (trigger the flow)
$client->inviteFlowInstance($flowInstance);

// Fetch all flow instances
foreach ($client->listFlowInstances() as $flowInstance) {
    dump($flowInstance);
}

// Fetch all flows
foreach ($client->listFlows() as $flow) {
    dump($flow);
}

// Fetch all entities
foreach ($client->listEntities() as $entity) {
    dump($entity);
}
```

## Fluent API

Many of the objects exposed by the API support method chaining.

```php
$client->createContact(
    $firstName,
    $lastName,
    $mobileNumber
)->createFlowInstance(
    Flow::make(['id' => $flowId])
)->invite();

```

## Laravel

**Configuration**

In Laravel, you can publish the config file with:
```bash
php artisan vendor:publish --provider="Mralston\QuakeSdk\QuakeServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'username' => env('QUAKE_USERNAME'),
    'password' => env('QUAKE_PASSWORD'),
    'company_id' => env('QUAKE_COMPANY_ID'),
    'api_endpoint' => env('QUAKE_API_ENDPOINT')
];
```

Configure the three environment variables with your username, password and company ID.
These are UUIDs supplied by Quake.

```dotenv
QUAKE_USERNAME=
QUAKE_PASSWORD=
QUAKE_COMPANY_ID=
QUAKE_API_ENDPOINT=
```

**Dependency Injection**

In addition to the method chaining described in the fluent API section above, the Laravel integration takes care of
authentication automatically. All you need to do is grab an instance of the client from the container and start using it.

You can use dependency injection to get a pre-authenticated instance of the client:

```php
use Illuminate\Http\Request;
use Mralston\Quake\Client;

class MyController
{
    public function create(Request $request, Client $client)
    {
        // Create new contact using POST data
        $contact = $client->createContact(
            $request->input('first_name'),
            $request->input('last_name'),
            $request->input('mobile'),
        )
    }
}
```

Alternatively, you can resolve an instance of the client from the container:

```php
use Mralston\Quake\Client;

$client = app(Client::class);
```

**Facade**

In true Laravel tradition, you can also use a facade (along with method chaining, of course!).

```php
use Mralston\Quake\Facades\Quake;
use Mralston\Quake\Flow;

Quake::createContact(
    $firstName,
    $lastName,
    $mobileNumber
)->createFlowInstance(
    Flow::make(['id' => $flowId])
)->invite();
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Matt Ralston](https://github.com/mralston)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
