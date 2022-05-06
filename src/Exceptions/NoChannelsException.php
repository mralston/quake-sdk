<?php

namespace Mralston\Quake\Exceptions;

use Exception;

class NoChannelsException extends Exception
{
    protected $message = 'No communication channels have been specified.';
}