<?php

namespace App\Exception;

use ApiBundle\Utils\Variables;
use JMS\Serializer\Annotation as Serializer;
use Throwable;

class ApiException extends \RuntimeException
{

    /**
     * @var int
     *
     * @Serializer\Expose
     */
    private $statusCode = 400;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
//        TODO: Variables
//        $error = Variables::$ERRORS[$message] ?? null;
        $error = null;
        if ($error) {
            $code = $error['code'] ?? 0;
            $message = $error['message'] ?? $message;
            $this->statusCode = $error['statusCode'] ?? 500;
        }
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
