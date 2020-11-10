<?php declare(strict_types=1);

/**
 * Use this exception when you need a dispatcher to put a full stop to everything
 * the message of the exception will be used as the response body.
 *
 * Setting the correct headers in the response is left to the user.
 */
namespace LDL\Http\Router\Response\Exception;

class CustomResponseException extends ResponseException
{

}