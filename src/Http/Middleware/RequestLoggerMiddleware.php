<?php

namespace OpenDialogAi\Core\Http\Middleware;

use Closure;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\Core\ResponseLog;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle request termination.
     *
     * @param  Request $request
     * @param  Response $response
     * @return mixed
     */
    public function terminate(Request $request, Response $response)
    {
        $requestLength = microtime(true) - LARAVEL_START;
        $memoryUsage = memory_get_usage();

        RequestLog::create([
            'url' => $request->url(),
            'query_params' => serialize($request->query()),
            'method' => $request->method(),
            'source_ip' => $request->ip(),
            // FIXME get this.
            'request_id' => '',
            'raw_request' => serialize($request->all()),
            'microtime' => DateTime::createFromFormat('U.u', LARAVEL_START)->format('Y-m-d H:i:s.u'),
        ])->save();

        ResponseLog::create([
            'request_length' => $requestLength,
            'memory_usage' => $memoryUsage,
            'http_status' => $response->getStatusCode(),
            'raw_response' => $response->__toString()
        ])->save();
    }
}
