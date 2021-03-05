<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Conversation;

class ConversationNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Conversation;
    }
}
