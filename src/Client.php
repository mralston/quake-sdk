<?php

declare(strict_types=1);

namespace Mralston\Quake;

use Carbon\Carbon;
use Exception;
use Generator;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mralston\Quake\Exceptions\MissingWebhookSecretException;
use Mralston\Quake\Exceptions\NoChannelsException;

class Client
{
    private HttpClient $http;

    private string $authApiEndpoint = 'https://www.leadcomplete.co.uk/api/oauth/token';
    private string $apiEndpoint = 'https://www.leadcomplete.co.uk/api/v1';

    private ?string $username;
    private ?string $password;

    private ?string $accessToken;
    private ?Carbon $accessTokenExpires;

    private ?string $companyId;

    private ?string $webhookSecret;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $username,
        string $password,
        string $companyId,
        string $apiEndpoint,
        ?string $webhookSecret = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->companyId = $companyId;

        $this->authApiEndpoint = $apiEndpoint . '/api/oauth/token';
        $this->apiEndpoint = $apiEndpoint . '/api/v1';

        $this->webhookSecret = $webhookSecret;

        $this->http = new HttpClient([
            'timeout' => 10
        ]);
    }

    public function setCompanyId(string $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    private function auth(bool $force = false): bool
    {
        if (
            !empty($this->accessToken) &&
            $this->accessTokenExpires->isAfter(Carbon::now()) &&
            !$force
        ) {
            return false;
        }

        $this->accessToken = null;
        $this->accessTokenExpires = null;

        $response = $this->http->post($this->authApiEndpoint, [
            'auth' => [
                $this->username,
                $this->password
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            throw new Exception('Quake authentication failed.');
        }

        $json = json_decode($response->getBody()->getContents());

        $this->accessToken = $json->access_token;
        $this->accessTokenExpires = Carbon::now()->addSeconds($json->expires_in);

        return true;
    }

    /**
     * @param string $companyId
     * @param Contact $contact
     * @param array $channels
     * @return Contact
     * @throws Exception
     */
    public function createContact(
        string $firstName,
        string $lastName,
        string $telephone,
        array $channels,
        ?string $companyId = null
    ): Contact {
        $this->auth();

        if (empty($channels)) {
            throw new NoChannelsException();
        }

        $companyId = $companyId ?? $this->companyId;

        $response = $this->http->post($this->apiEndpoint . '/contacts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
            'json' => [
                "contact" => [
                    "companyId" => $companyId,
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "telephone" => $this->e164($telephone),
                    "channels" => $channels
                ]
            ]
        ]);

        return new Contact(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @param Contact $contact
     * @return bool
     * @throws Exception
     */
    public function deleteContact(Contact $contact): bool
    {
        $this->auth();

        $response = $this->http->delete($this->apiEndpoint . '/contacts/' . $contact->id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
        ]);

        return true;
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function listContacts(): Generator
    {
        $this->auth();

        $page = 1;

        while (true) {
            $response = $this->http->get($this->apiEndpoint . '/contacts?page=' . $page, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);

            $json = json_decode($response->getBody()->getContents());

            if (count($json->data) == 0) {
                return;
            }

            foreach ($json->data as $contact) {
                yield new Contact(
                    $contact,
                    $this
                );
            }

            $page++;
        }
    }

    /**
     * @param Contact $contact
     * @return Contact
     * @throws Exception
     */
    public function showContact(Contact $contact): Contact
    {
        $this->auth();

        $response = $this->http->get($this->apiEndpoint . '/contacts/' . $contact->id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
        ]);

        return new Contact(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @param Flow $flow
     * @param Contact $contact
     * @return FlowInstance
     * @throws Exception
     */
    public function createFlowInstance(Flow $flow, Contact $contact, array $parameters = []): FlowInstance
    {
        $this->auth();

        $response = $this->http->post($this->apiEndpoint . '/flow-instances', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
            'json' => [
                'flowInstance' => [
                    'flowId' => $flow->id,
                    'contactId' => $contact->id,
                    'templateParameters' => $parameters,
                ]
            ]
        ]);

        return new FlowInstance(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @param FlowInstance $flowInstance
     * @return FlowInstance
     * @throws Exception
     */
    public function showFlowInstance(string $id): FlowInstance
    {
        $this->auth();

        $response = $this->http->get($this->apiEndpoint . '/flow-instances/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
        ]);

        return new FlowInstance(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @param FlowInstance $flowInstance
     * @return FlowInstance
     * @throws Exception
     */
    public function inviteFlowInstance(FlowInstance $flowInstance): FlowInstance
    {
        $this->auth();

        $response = $this->http->post($this->apiEndpoint . '/flow-instances/' . $flowInstance->id . '/invite', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);

        return new FlowInstance(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function listFlowInstances(): Generator
    {
        $this->auth();

        $page = 1;

        while (true) {
            $response = $this->http->get($this->apiEndpoint . '/flow-instances?page=' . $page, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);

            $json = json_decode($response->getBody()->getContents());

            if (count($json->data) == 0) {
                return;
            }

            foreach ($json->data as $flowInstance) {
                yield new FlowInstance(
                    $flowInstance,
                    $this
                );
            }

            $page++;
        }
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function listFlows(): Generator
    {
        $this->auth();

        $page = 1;

        while (true) {
            $response = $this->http->get($this->apiEndpoint . '/flows?page=' . $page, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);

            $json = json_decode($response->getBody()->getContents());

            if (count($json->data) == 0) {
                return;
            }

            foreach ($json->data as $flow) {
                yield new Flow(
                    $flow,
                    $this
                );
            }

            $page++;
        }
    }

    /**
     * @param Flow $flow
     * @return Flow
     * @throws Exception
     */
    public function showFlow(string $id): Flow
    {
        $this->auth();

        $response = $this->http->get($this->apiEndpoint . '/flows/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
        ]);

        return new Flow(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function listEntities(): Generator
    {
        $this->auth();

        $page = 1;

        while (true) {
            $response = $this->http->get($this->apiEndpoint . '/entities?page=' . $page, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);

            $json = json_decode($response->getBody()->getContents());

            if (count($json->data) == 0) {
                return;
            }

            foreach ($json->data as $entity) {
                yield new Entity(
                    $entity,
                    $this
                );
            }

            $page++;
        }
    }

    /**
     * @param Entity $entity
     * @return Entity
     * @throws Exception
     */
    public function showEntity(string $id): Entity
    {
        $this->auth();

        $response = $this->http->get($this->apiEndpoint . '/entities/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ],
        ]);

        return new Entity(
            json_decode($response->getBody()->getContents()),
            $this
        );
    }

    public function resolveWebhookChallenge(string $crcToken, ?string $webhookSecret = null)
    {
        if (empty($webhookSecret)) {
            $webhookSecret = $this->webhookSecret;
        }

        if (empty($webhookSecret)) {
            throw new MissingWebhookSecretException();
        }

        return json_encode([
            'response_token' => 'sha256=' . $this->generateHash($crcToken, $webhookSecret)
        ]);
    }

    public function validateWebhookRequest(?string $webhookSecret = null): bool
    {
        if (empty($webhookSecret)) {
            $webhookSecret = $this->webhookSecret;
        }

        if (empty($webhookSecret)) {
            throw new MissingWebhookSecretException();
        }

        // Validate timestamp
        if (empty($_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'])) {
            return false;
        }

        $timestamp = Carbon::createFromTimestamp($_SERVER['HTTP_X_WEBHOOK_TIMESTAMP']);

        if ($timestamp->isBefore(Carbon::now()->subMinute())) {
            return false;
        }

        // Check signature version is supported (currently only v1)
        if (($_SERVER['HTTP_X_WEBHOOK_SIGNATURE_VERSION'] ?? null) != 'v1') {
            return false;
        }

        // Validate signature
        if (empty($_SERVER['HTTP_X_WEBHOOK_SIGNATURE'])) {
            return false;
        }

        $requestHash = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];

        $calculatedHash = $this->generateHash(
            $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] .
            '.' .
            file_get_contents('php://input'),
            $webhookSecret
        );

        if ($requestHash != $calculatedHash) {
            return false;
        }

        return true;
    }

    private function generateHash($data, $secret)
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                $data,
                $secret,
                true
            )
        );
    }

    private function e164(string $telephone): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $numberProto = $phoneUtil->parse($telephone, 'GB');
        return $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
    }
}
