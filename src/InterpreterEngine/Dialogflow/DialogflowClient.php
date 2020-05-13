<?php

namespace OpenDialogAi\InterpreterEngine\Dialogflow;

use Exception;
use Google\ApiCore\ValidationException;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUCustomClient;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUCustomRequest;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLURequestFailedException;
use OpenDialogAi\InterpreterEngine\Interpreters\AbstractNLUInterpreter\AbstractNLUResponse;

class DialogflowClient extends AbstractNLUCustomClient
{
    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var string
     */
    private $defaultProjectId;

    /**
     * @var array
     */
    private $projects;

    /**
     * @var array
     */
    private $credentials;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $this->languageCode = $config['language_code'] ?? 'en-GB';
        $this->projects = $config['project_ids'] ?? [];
        $this->credentials = $config['credentials'] ?? [];
    }

    /**
     * @return string
     */
    public function getDefaultProjectId(): string
    {
        return $this->defaultProjectId;
    }

    /**
     * @param string $defaultProjectId
     */
    public function setDefaultProjectId(string $defaultProjectId): void
    {
        $this->defaultProjectId = $defaultProjectId;
    }

    /**
     * @inheritDoc
     * @throws AbstractNLURequestFailedException
     * @throws ValidationException
     */
    public function sendRequest($message, $projectId = null): AbstractNLUCustomRequest
    {
        $projectId = $projectId ?? $this->getDefaultProjectId();
        $client = $this->getClientForProject($projectId);

        $queryResult = null;
        try {
            $project = $this->projects[$projectId];
            $sessionId = ContextService::getUserContext()->getUserId();
            $session = $client->sessionName($project, $sessionId ?: uniqid());

            $textInput = $this->getText($message, $this->languageCode);
            $queryInput = $this->getQueryInput($textInput);

            $response = $client->detectIntent($session, $queryInput);
            $queryResult = $response->getQueryResult();
        } catch (Exception $e) {
            Log::warning(sprintf('Exception caught during Dialogflow request: %s', $e->getMessage()));
        } finally {
            $client->close();
        }

        return new DialogflowRequest($queryResult);
    }

    /**
     * @inheritDoc
     */
    public function createResponse($response): AbstractNLUResponse
    {
        return new DialogflowResponse($response);
    }

    /**
     * @param $text
     * @param $languageCode
     * @return TextInput
     */
    private function getText($text, $languageCode): TextInput
    {
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        return $textInput;
    }

    /**
     * @param TextInput $textInput
     * @return QueryInput
     */
    private function getQueryInput(TextInput $textInput): QueryInput
    {
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        return $queryInput;
    }

    /**
     * Gets the credentials path to use for the project
     *
     * @param $projectId
     * @return SessionsClient
     * @throws ValidationException
     */
    public function getClientForProject($projectId): SessionsClient
    {
        if (is_null($projectId) || empty($projectId)) {
            if (empty($this->credentials)) {
                throw new AbstractNLURequestFailedException(
                    'No Dialogflow credentials are specified in the Interpreter Engine configuration file.'
                );
            } else {
                $projectIds = array_keys($this->credentials);
                $projectId = $projectIds[0];

                Log::info(sprintf('No Dialogflow project ID was specified, defaulting to %s.', $projectId));
            }
        } else {
            if (!isset($this->credentials[$projectId])) {
                throw new AbstractNLURequestFailedException(
                    sprintf('No credentials path found for Dialogflow agent (%s)', $projectId)
                );
            }
        }

        $credentialsPath = $this->credentials[$projectId];
        return new SessionsClient([
            'credentials' => $credentialsPath
        ]);
    }
}
