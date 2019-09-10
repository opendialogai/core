<?php

namespace OpenDialogAi\SensorEngine\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\SensorEngine\SensorInterface;
use OpenDialogAi\ResponseEngine\LinkClickInterface;
use Illuminate\Routing\Controller as BaseController;
use OpenDialogAi\SensorEngine\Service\SensorService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\SensorEngine\Contracts\IncomingMessageInterface;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\Contracts\IncomingControllerInterface;

class WebchatIncomingController extends BaseController implements IncomingControllerInterface
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

    /**
     * {@inheritdoc}
     */
    public function receive(IncomingMessageInterface $request): Response
    {
        $messageType = $request->input('notification');

        // Log that the message was successfully received.
        Log::info("Webchat endpoint received a valid message of type ${messageType}.");

        // Get the Utterance.
        $utterance = $this->webchatSensor->interpret($request);

        // Save "url_click" messages.
        if ($utterance instanceof WebchatUrlClickUtterance) {
            $linkClick = app()->make(LinkClickInterface::class);
            $linkClick->save($utterance);

            return response(null, 200);
        }

        /** @var WebChatMessages $messageWrapper */
        $messageWrapper = $this->odController->runConversation($utterance);

        Log::debug(sprintf('Sending response: %s', json_encode($messageWrapper->getMessageToPost())));

        $messages = $messageWrapper->getMessageToPost();
        if (count($messages) == 1) {
            return response(reset($messages), 200);
        }

        return response($messageWrapper->getMessageToPost(), 200);
    }
}
