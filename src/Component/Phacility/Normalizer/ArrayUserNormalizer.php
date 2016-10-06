<?php

namespace Component\Phacility\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ArrayUserNormalizer extends AbstractNormalizer
{

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = NULL, array $context = [])
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = NULL)
    {
        return FALSE;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = NULL, array $context = [])
    {
        $normalizedUsers = [];
        foreach ($object['data'] as $phacilityUser) {
            $normalizedUsers[$phacilityUser['phid']] = $phacilityUser['fields']['realName'];
        }

        return $normalizedUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = NULL)
    {
        return is_array($data) && 'array' === $format;
    }
}