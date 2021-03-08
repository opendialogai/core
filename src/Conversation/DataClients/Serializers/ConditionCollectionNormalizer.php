<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;

class ConditionCollectionNormalizer extends CollectionNormalizer
{
    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === ConditionCollection::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new ConditionCollection();
        foreach ($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Condition::class));
        }
        return $collection;
    }
}
