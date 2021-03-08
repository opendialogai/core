<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;


 class ConversationCollectionNormalizer extends CollectionNormalizer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new ConversationCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, Conversation::class));
        }
        return $collection;
    }


     public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
     {
             return $type === ConversationCollection::class;
     }
 }

