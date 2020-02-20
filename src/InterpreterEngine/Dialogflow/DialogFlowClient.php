<?php

namespace OpenDialogAi\InterpreterEngine\DialogFlow;

use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;

class DialogFlowClient
{
    public function detectIntent($text, $sessionId, $languageCode = 'en-GB')
    {
        $projectId = config('opendialog.interpreter_engine.dialogflow.project_id');
        $path = config(
            'opendialog.interpreter_engine.dialogflow.credentials_path',
            resource_path('resources/credentials/dialogflow.json')
        );

        $sessionsClient = new SessionsClient([
            'credentials' => $path,
        ]);

        try {
            $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());

            // create text input
            $textInput = $this->getText($text, $languageCode);

            // create query input
            $queryInput = $this->getQueryInput($textInput);

            return $this->getMatch($sessionsClient, $session, $queryInput);

            //Some other useful methods
            //$queryText = $queryResult->getQueryText();
            //$intent = $queryResult->getIntent();
            //$displayName = $intent->getDisplayName();
            //$confidence = $queryResult->getIntentDetectionConfidence();
        } catch (\Exception $e) {
            dump($e);
        } finally {
            $sessionsClient->close();
        }
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
     * @param SessionsClient $sessionsClient
     * @param string         $session
     * @param QueryInput     $queryInput
     * @return string
     * @throws \Google\ApiCore\ApiException
     */
    private function getMatch(SessionsClient $sessionsClient, string $session, QueryInput $queryInput): string
    {
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();

        return $queryResult->getFulfillmentText();
    }
}
