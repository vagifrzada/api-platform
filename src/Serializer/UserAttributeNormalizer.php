<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserAttributeNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice.
        if (isset($context[self::class])) {
            return false;
        }

        return $data instanceof User;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->isUserHimself($object)) {
            $context['groups'][] = "owner:users:read";
        }

        $context[self::class] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    private function isUserHimself(User $user): bool
    {
        return $this->tokenStorage->getToken()->getUser() == $user; // Checking for instance.
    }
}