<?php

namespace Luxid\Exceptions;

use Exception;

class ForbiddenException extends \Exception
{
    protected $message = 'You don\'t have permission to accss this page.';
    protected $code = 403;
}
