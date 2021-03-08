<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\VirtualIntent;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;
use Symfony\Component\Serializer\SerializerInterface;

class VirtualIntentCollectionNormalizer extends CollectionNormalizer
{
    protected SerializerInterface $serializer;



    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === VirtualIntentCollection::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $collection = new VirtualIntentCollection();
        foreach($data as $datum) {
            $collection->add($this->serializer->denormalize($datum, VirtualIntent::class));
        }
        return $collection;
    }

}
