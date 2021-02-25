<?php


namespace OpenDialogAi\GraphQLClient;


use \Exception;
use Throwable;

class GraphQLClientQueryErrorException extends Exception
{

    protected array $queryErrors;

    /**
     * GraphQLClientQueryErrorException constructor.
     *
     * @param  string          $message
     * @param  array           $queryErrors
     * @param  int             $code
     * @param  Throwable|null  $previous
     */
    public function __construct(string $message, array $queryErrors, $code = 0, Throwable $previous = null)
    {
        $this->queryErrors = $queryErrors;
        parent::__construct($message, $code, $previous);
    }
}
