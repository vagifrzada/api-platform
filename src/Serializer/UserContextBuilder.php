<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function createFromRequest(
        Request $request,
        bool $normalization,
        array $extractedAttributes = null
    ): array {
        // Creating original context
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if ($resourceClass !== User::class) {
            return $context;
        }

        if ($this->isNormalizationContext($context, $normalization) && $this->canSeeResource()) {
            $context['groups'][] = 'admin:users:read';
        }

        return $context;
    }

    private function isNormalizationContext(array $context, bool $normalization): bool
    {
        return isset($context['groups']) && $normalization;
    }

    private function canSeeResource(): bool
    {
        return $this->authorizationChecker->isGranted(UserRole::ADMIN); // Super admin also can see.
    }
}
