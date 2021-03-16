<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{
    protected SerializerInterface $serializer;

    const ID = 'id';
    const TYPE = 'type';
    const VALUE = 'value';

    const FIELDS = [
        self::ID,
        self::TYPE,
        self::VALUE,
    ];

    const COMPOSITE_VALUE = 'composite_value';
    const SCALAR_VALUE = 'scalar_value';
    const IS_COMPOSITE_CHILD = 'is_composite_child';

    /**
     * @param Attribute $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($object instanceof CompositeAttribute) {
            $value = json_encode([
                self::COMPOSITE_VALUE => $object->getAttributes()
                    ->map(fn (string $id, Attribute $attribute) =>
                        $this->normalize($attribute, $format, $context + [self::IS_COMPOSITE_CHILD => true])
                    )
                    ->values()
                    ->toArray()
            ]);
        } else {
            $value = [
                self::SCALAR_VALUE => $object->getValue()
            ];

            if (!isset($context[self::IS_COMPOSITE_CHILD]) || !$context[self::IS_COMPOSITE_CHILD]) {
                $value = json_encode($value);
            }
        }

        return [
            self::ID => $object->getId(),
            self::TYPE => $object::getType(),
            self::VALUE => $value
        ];
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Attribute;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return Attribute
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): Attribute
    {
        if (!isset($data[self::ID]) || !isset($data[self::TYPE]) || !isset($data[self::VALUE])) {
            throw new UnexpectedValueException();
        }

        try {
            $attributeClass = resolve(AttributeTypeServiceInterface::class)->getAttributeTypeClass($data[self::TYPE]);
        } catch (AttributeTypeNotRegisteredException $e) {
            throw new UnexpectedValueException();
        }

        if (is_array($data[self::VALUE])) {
            // This will only be the case if we are recursively denormalising a composite attribute
            $value = $data[self::VALUE];
        } else {
            $value = json_decode($data[self::VALUE], true);
        }

        if (isset($value[self::COMPOSITE_VALUE])) {
            $valueMap = new Map();

            foreach ($value[self::COMPOSITE_VALUE] as $compositeChild) {
                $valueMap->put($compositeChild[self::ID], $compositeChild);
            }

            $value = $valueMap->map(fn ($key, $attribute) => $this->denormalize($attribute, $type, $format, $context));
        } else {
            $value = $value[self::SCALAR_VALUE];
        }

        return new $attributeClass($data[self::ID], $value);
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === Attribute::class;
    }
}
