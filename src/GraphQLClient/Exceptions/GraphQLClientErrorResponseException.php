<?php


namespace OpenDialogAi\GraphQLClient\Exceptions;
use Throwable;

class GraphQLClientErrorResponseException extends GraphQLClientException
{

    protected array $responseErrors;

    /**
     * GraphQLClientQueryErrorException constructor.
     *
     * @param  string          $message
     * @param  array           $responseErrors
     * @param  int             $code
     * @param  Throwable|null  $previous
     */
    public function __construct(string $message, array $responseErrors, $code = 0, Throwable $previous = null)
    {
        $this->responseErrors = $responseErrors;
        parent::__construct($message, $code, $previous);
    }
}
