<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;


class BehaviorsCollectionNormalizer extends CollectionNormalizer
{

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === BehaviorsCollection::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new BehaviorsCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Behavior::class));
        }
        return $collection;
    }

}
