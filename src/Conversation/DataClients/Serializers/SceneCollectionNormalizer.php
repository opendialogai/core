<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;


class SceneCollectionNormalizer extends CollectionNormalizer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new SceneCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Scene::class));
        }
        return $collection;
    }


     public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
     {
             return $type === SceneCollection::class;
     }
 }

