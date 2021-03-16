<?php


namespace OpenDialogAi\ContextEngine\DataClients;


use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
use OpenDialogAi\ContextEngine\Contracts\ContextDataClient;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\AttributeNormalizer;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use Symfony\Component\Serializer\Serializer;

class GraphAttributeDataClient implements ContextDataClient
{
    private GraphQLClientInterface $graphClient;

    public function __construct()
    {
        $this->graphClient = resolve(GraphQLClientInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function loadAttributes(string $contextId, string $userId): AttributeBag
    {
        $query = <<<'GQL'
            query getUser($userId: ID!) {
                getUser(id: $userId) {
                    contexts(filter: {name: $contextId}) {
                        attributes {
                            id
                            type
                            value
                        }
                    }
                }
            }
        GQL;

        $response = $this->graphClient->query($query, [
            'userId' => $userId,
            'contextId' => $contextId,
        ]);

        if ($response['data']['getUser'] === null) {
            throw new CouldNotLoadAttributeException();
        }

        $serializer = new Serializer([new AttributeNormalizer()], []);

        $attributesData = [];

        if (isset($response['data']['getUser']['contexts']) && count($response['data']['getUser']['contexts']) > 0) {
            $attributesData = $response['data']['getUser']['contexts'][0]['attributes'];
        }

        $attributes = new BasicAttributeBag();

        foreach ($attributesData as $attributeData) {
            $attribute = $serializer->denormalize($attributeData, Attribute::class);
            $attributes->addAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function loadAttribute(string $contextId, string $userId, string $attributeId): ?Attribute
    {
        $query = <<<'GQL'
            query getUser($userId: ID!) {
                getUser(id: $userId) {
                    contexts(filter: {name: $contextId}) {
                        attributes(filter: {id: $attributeId}) {
                            id
                            type
                            value
                        }
                    }
                }
            }
        GQL;

        $response = $this->graphClient->query($query, [
            'userId' => $userId,
            'contextId' => $contextId,
            'attributeId' => $attributeId,
        ]);

        if ($response['data']['getUser'] === null) {
            throw new CouldNotLoadAttributeException();
        }

        $serializer = new Serializer([new AttributeNormalizer()], []);

        $attributeData = null;

        if (isset($response['data']['getUser']['contexts']) && count($response['data']['getUser']['contexts']) > 0
            && isset($response['data']['getUser']['contexts'][0]['attributes'])
            && count($response['data']['getUser']['contexts'][0]['attributes']) > 0) {
            $attributeData = $response['data']['getUser']['contexts'][0]['attributes'][0];
        }

        if (is_null($attributeData)) {
            // The graph returned no data for the desired attribute, it hasn't been persisted before
            $attribute = null;
        } else {
            $attribute = $serializer->denormalize($attributeData, Attribute::class);
        }

        return $attribute;
    }

    /**
     * @inheritDoc
     */
    public function persistAttributes(string $contextId, string $userId, AttributeBag $attributes): void
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function persistAttribute(string $contextId, string $userId, Attribute $attribute): void
    {
        return;
    }
}
