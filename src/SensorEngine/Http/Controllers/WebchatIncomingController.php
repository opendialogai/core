<?php

namespace OpenDialogAi\SensorEngine\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use OpenDialogAi\SensorEngine\Http\Requests\IncomingWebchatMessage;
use OpenDialogAi\SensorEngine\SensorInterface;
use OpenDialogAi\SensorEngine\Service\SensorService;

class WebchatIncomingController extends BaseController
{
    /** @var SensorService */
    private $sensorService;

    /** @var ContextService */
    private $contextService;

    /** @var ConversationEngineInterface  */
    private $conversationEngine;

    /** @var ResponseEngineServiceInterface */
    private $responseEngineService;

    /** @var OpenDialogController */
    private $odController;

    /** @var SensorInterface */
    private $webchatSensor;

    /** @var ActionEngineInterface */
    private $actionEngine;

    /**
     * WebchatIncomingController constructor.
     * @param SensorService $sensorService
     * @param ContextService $contextService
     * @param ConversationEngineInterface $conversationEngine
     * @param OpenDialogController $odController
     * @param ResponseEngineServiceInterface $responseEngineService
     */
    public function __construct(
        SensorService $sensorService,
        ContextService $contextService,
        ConversationEngineInterface $conversationEngine,
        OpenDialogController $odController,
        ResponseEngineServiceInterface $responseEngineService,
        ActionEngineInterface $actionEngine
    ) {
        $this->sensorService = $sensorService;
        $this->contextService = $contextService;
        $this->conversationEngine = $conversationEngine;
        $this->responseEngineService = $responseEngineService;
        $this->actionEngine = $actionEngine;
        $this->odController = $odController;

        $this->odController->setContextService($this->contextService);
        $this->odController->setConversationEngine($this->conversationEngine);
        $this->odController->setResponseEngine($responseEngineService);

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
