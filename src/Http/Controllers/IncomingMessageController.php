<?php

namespace OpenDialogAi\Core\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorEngine;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;
use OpenDialogAi\Core\Http\Requests\IncomingWebchatMessage;

class IncomingChatController extends BaseController
{
    public function receive(IncomingWebchatMessage $request)
    {
        $messageType = $request->input('notification');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");

        // Get the Webchat Sensor.
        $sensorEngine = app()->make(SensorEngine::class);
        /* @var WebchatSensor $webchatSensor */
        $webchatSensor = $sensorEngine->getSensor(SensorEngine::WEBCHAT_SENSOR);

        // Get the Utterance.
        $utterance = $webchatSensor->interpret($request);

        // Pass the utterance to the OD Controller.
        $odController = app()->make(OpenDialogController::class);

        // Get the response.
        $message = $odController->runConversation($utterance);
        Log::debug("Sending response: {$message->getText()}");

        return response($message->getMessageToPost(), 200);
    }
}
