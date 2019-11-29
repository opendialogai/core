<?php


namespace OpenDialogAi\Core\NlpEngine\MicrosoftRepository;

use Psr\Http\Message\ResponseInterface;

/**
 * Class MsLanguageEntity
 *
 * @package OpenDialogAi\Core\NlpEngine\MicrosoftRepository
 */
class MsLanguageEntity
{
    private $name;
    private $isoName;
    private $score;

    public function __construct(ResponseInterface $response)
    {
        $entity = $this->formatResponse($response);

        return $this->createMsEntity($entity[0]); //this could be a looped array in teh future
    }

    /**
     * @param $entity
     * @return $this
     */
    private function createMsEntity($entity): MsLanguageEntity
    {
        $this->name = $entity['name'];
        $this->isoName = $entity['iso6391Name'];
        $this->score = $entity['score'];

        return $this;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    private function formatResponse(ResponseInterface $response): array
    {
        $entity = json_decode($response->getBody()->getContents(), true)['documents'][0]['detectedLanguages'];

        return $entity;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIsoName(): string
    {
        return $this->isoName;
    }

    /**
     * @return string
     */
    public function getScore(): string
    {
        return $this->score;
    }
}
