<?php

namespace OpenDialogAi\SensorEngine\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\SensorInterface;
use OpenDialogAi\SensorEngine\Service\SensorService;

class WebchatIncomingController extends BaseController
{
    /** @var OpenDialogController */
    private $odController;

    /** @var SensorInterface */
    private $webchatSensor;

    /**
     * WebchatIncomingController constructor.
     * @param SensorService $sensorService
     * @param OpenDialogController $odController
     */
    public function __construct(SensorService $sensorService, OpenDialogController $odController)
    {
        $this->odController = $odController;
        $this->webchatSensor = $sensorService->getSensor('sensor.core.webchat');
    }

    public function receive(IncomingWebchatMessage $request)
    {
        $messageType = $request->input('notification');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");

        // Get the Utterance.
        $utterance = $this->webchatSensor->interpret($request);

        // Ignore "url_click" messages.
        if ($utterance instanceof WebchatUrlClickUtterance) {
            return response(null, 200);
        }

        /** @var WebChatMessages $messageWrapper */
        $messageWrapper = $this->odController->runConversation($utterance);

        Log::debug(sprintf('Sending response: %s', json_encode($messageWrapper->getMessageToPost())));

        return response($messageWrapper->getMessageToPost(), 200);
    }
}
