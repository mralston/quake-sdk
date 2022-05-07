<?php

namespace Mralston\Quake\Exceptions;

use Exception;

class MissingWebhookSecretException extends Exception
{
    protected $message = 'The webhook secret has not been provided.';
}