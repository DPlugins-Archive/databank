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
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (
            ($object instanceof Blob && $object->getSnippet()->getNamespace() === 'scripts-organizer')
            || ($object instanceof Revision && $object->getBlob()->getSnippet()->getNamespace() === 'scripts-organizer')
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
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->denormalize($data, $type, $format, $context);

        if (
            ($data instanceof Blob && $data->getSnippet()->getNamespace() === 'scripts-organizer')
            || ($data instanceof Revision && $data->getBlob()->getSnippet()->getNamespace() === 'scripts-organizer')
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
        return $data instanceof Blob || $data instanceof Revision;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $type === Blob::class;
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
        if ($decoded === false) return false;

        if (!preg_match('/[a-z0-9\!\$\&\\\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i', $encoded)) return false;

        return $decoded;
    }
}
