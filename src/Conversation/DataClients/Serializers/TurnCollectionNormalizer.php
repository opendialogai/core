<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;


class TurnCollectionNormalizer extends CollectionNormalizer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new TurnCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Turn::class));
        }
        return $collection;
    }


     public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
     {
             return $type === TurnCollection::class;
     }
 }

