<?php

namespace OpenDialogAi\SensorEngine\Contracts;

use Illuminate\Http\Response;

interface IncomingControllerInterface
{
    /**
     * It receives an incoming request
     *
     * @param IncomingMessageInterface $request The request
     *
     * @return Response
     */
    public function receive(IncomingMessageInterface $request): Response;
}
