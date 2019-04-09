<?php

namespace OpenDialogAi\SensorEngine\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorEngine;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;

class WebchatIncomingController extends BaseController
{

    /**
     * Create a new controller instance.
     *
     * @param  SensorEngine  $sensorEngine
     * @param  OpenDialogController  $odController
     * @return void
     */
    public function __construct(SensorEngine $sensorEngine, OpenDialogController $odController)
    {
        $this->sensorEngine = $sensorEngine;
        $this->odController = $odController;
        $this->webchatSensor = $this->sensorEngine->getSensor(SensorEngine::WEBCHAT_SENSOR);
    }

    public function receive(IncomingWebchatMessage $request)
    {
        $messageType = $request->input('notification');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");

        // Get the Utterance.
        $utterance = $this->webchatSensor->interpret($request);

        // Get the response.
        $message = $this->odController->runConversation($utterance);
        Log::debug("Sending response: {$message->getText()}");

        return response($message->getMessageToPost(), 200);
    }
}
