<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

class IntentCollectionNormalizer extends CollectionNormalizer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new IntentCollection();
        foreach ($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Intent::class));
        }
        return $collection;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === IntentCollection::class;
    }
}

