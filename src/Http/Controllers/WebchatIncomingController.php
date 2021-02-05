<?php

namespace OpenDialogAi\Core\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatUrlClickUtterance;
use OpenDialogAi\ResponseEngine\LinkClickInterface;
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

    /**
     * Receives an incoming message, transforms into an utterance, passes through the OD controller and returns the
     * correct response
     *
     * @param IncomingWebchatMessage $request
     * @return Response
     * @throws BindingResolutionException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws NodeDoesNotExistException
     */
    public function receive(IncomingWebchatMessage $request): Response
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
        if (count($messages) == 1 && reset($messages)) {
            return response(reset($messages), 200);
        }

        return response($messageWrapper->getMessageToPost(), 200);
    }
}
