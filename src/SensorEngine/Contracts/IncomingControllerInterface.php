<?php

declare(strict_types=1);

namespace OpenDialogAi\SensorEngine\Contracts;

use Illuminate\Http\Response;
use OpenDialogAi\SensorEngine\Contracts\IncomingMessageInterface;

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
