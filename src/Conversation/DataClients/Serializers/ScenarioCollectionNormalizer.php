<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;


class ScenarioCollectionNormalizer extends CollectionNormalizer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new ScenarioCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Scenario::class));
        }
        return $collection;
    }

     public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
     {
             return $type === ScenarioCollection::class;
     }
 }

