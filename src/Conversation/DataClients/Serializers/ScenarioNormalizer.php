<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ScenarioNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {

        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Scenario;
    }
}
