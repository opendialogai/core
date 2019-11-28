<?php

namespace OpenDialogAi\Core\NlpEngine\Service;

use OpenDialogAi\Core\NlpEngine\NlpLanguage;
use OpenDialogAi\NlpEngine\Service\NlpServiceInterface;

/**
 * Class MsNlpService
 *
 * @package OpenDialogAi\Core\NlpEngine\Service
 */
class MsNlpService implements NlpServiceInterface
{
    /** @var \OpenDialogAi\Core\NlpEngine\Client\MsClient  */
    private $client;

    /** @var string  */
    private $string;

    const LANGUAGE_DEFAULT = 'GB';

    public function __construct(string $string)
    {
        $this->client = app()->make('MsClient');
        $this->string = $string;
    }

    public function getLanguage(): NLPLanguage
    {
        $body = [
            'documents' => [
                [
                    'countryHint' => self::LANGUAGE_DEFAULT,
                    'id' => '1', // for now we set this to 1 as we aren't passing an array
                    'text' => $this->string,
                ],
            ],
        ];

        $response = $this->client->post(
            '/languages',
            [
                'form_params' => $body
            ]
        );

        $language = new NlpLanguage();
        $language->setLanguageName('English');
        $language->setIsoName('en');
        $language->setScore(1.0);

        return $language;
    }
}
