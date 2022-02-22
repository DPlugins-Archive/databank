<?php

namespace App\Serializer\Normalizer;

use App\Entity\Blob;
use App\Entity\Revision;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BlobNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface, SerializerAwareInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (
            ($object instanceof Blob && 'scripts-organizer' === $object->getSnippet()->getNamespace())
            || ($object instanceof Revision && 'scripts-organizer' === $object->getBlob()->getSnippet()->getNamespace())
        ) {
            if (isset($data['content'])) {
                $data['content'] = base64_encode($data['content']);
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        $data = $this->decorated->denormalize($data, $type, $format, $context);

        if (
            ($data instanceof Blob && 'scripts-organizer' === $data->getSnippet()->getNamespace())
            || ($data instanceof Revision && 'scripts-organizer' === $data->getBlob()->getSnippet()->getNamespace())
        ) {
            $content = $this->base64_decode($data->getContent());
            if (false !== $content) {
                $data->setContent($content);
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return false;

        return $this->decorated->supportsNormalization($data, $format) && ($data instanceof Blob || $data instanceof Revision);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return false;

        return $this->decorated->supportsDenormalization($data, $type, $format) && Blob::class === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    private function base64_decode($encoded)
    {
        $decoded = base64_decode($encoded, true);
        if (false === $decoded) {
            return false;
        }

        if (!preg_match('/[a-z0-9\!\$\&\\\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i', (string) $encoded)) {
            return false;
        }

        return $decoded;
    }
}
