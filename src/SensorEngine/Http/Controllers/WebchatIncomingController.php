<?php

namespace OpenDialogAi\SensorEngine\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\SensorInterface;
use OpenDialogAi\SensorEngine\Service\SensorService;

class WebchatIncomingController extends BaseController
{
    /** @var SensorService */
    private $sensorService;

    /** @var OpenDialogController */
    private $odController;

    /** @var SensorInterface */
    private $webchatSensor;


    /**
     * WebchatIncomingController constructor.
     * @param SensorService $sensorService
     * @param OpenDialogController $odController
     */
    public function __construct(
        SensorService $sensorService,
        OpenDialogController $odController
    ) {
        $this->sensorService = $sensorService;
        $this->odController = $odController;

        $this->webchatSensor = $this->sensorService->getSensor('sensor.core.webchat');
    }

    public function receive(IncomingWebchatMessage $request)
    {
        $messageType = $request->input('notification');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");

        // Get the Utterance.
        $utterance = $this->webchatSensor->interpret($request);

        /** @var WebChatMessage $message */
        $message = $this->odController->runConversation($utterance);
        Log::debug("Sending response: " . json_encode($message));

        // @todo - loop through messages and send all of them (collating in a single post)
        return response($message[0]->getMessageToPost(), 200);
    }
}
