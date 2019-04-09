<?php

namespace OpenDialogAi\Core\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\SensorEngine\Sensors\WebchatSensor;
use OpenDialogAi\SensorEngine\Service\SensorEngine;
use OpenDialogAi\SensorEngine\SensorEngineServiceProvider;
use OpenDialogAi\Core\Http\Requests\IncomingWebchatMessage;

class IncomingChatController extends BaseController
{
    use ValidatesRequests;

    public function receive(IncomingWebchatMessage $request)
    {
        // Handle requests without the content object.
        if (!$content = $request->input('content')) {
            $content = [0 => 0];
        }

        // Wrap single messages in an array.
        if (!isset($content[0])) {
            $content = [$content];
        }

        // Validate the data for regular messages.
        if ($request->input('notification') === 'message') {
            $contentValid = $this->validateContent($content);

            if ($contentValid !== true) {
                return response()->json($contentValid, 400);
            }
        }

        $messageType = $request->input('notification');
        $userId      = $request->input('user_id');
        $author      = $request->input('author');
        $messageId   = $request->input('message_id');

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

    /**
     * @param array $content
     *
     * @return mixed
     */
    private function validateContent($content)
    {
        // Validate the message(s) received.
        foreach ($content as $message) {
            // Validate the message format.
            $messageValidator = Validator::make($message, [
                'author' => 'required|string',
                'type' => 'required|string|in:chat_open,trigger,url_click,webchat_form_response,' .
                    'webchat_list_response,text,button,button_response,image,longtext,longtext_response,' .
                    'typing,read,system,longtext_response',
                'data' => 'required|array',
            ]);

            if ($messageValidator->fails()) {
                $validationMessages = $messageValidator->messages();
                Log::info("Webchat endpoint received an invalid message. Errors were: ${validationMessages}");
                return $validationMessages;
            }
        }
        return true;
    }
}
