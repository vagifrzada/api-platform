<?php

declare(strict_types=1);

namespace App\Security\Guard;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;

class TokenAuthenticator extends BaseAuthenticator
{
    public function getUser($preAuthToken, UserProviderInterface $userProvider): User
    {
        /** @var User $user */
        $user = parent::getUser($preAuthToken, $userProvider);

        $payload = $preAuthToken->getPayload();

        if (isset($payload['iat']) && $user->getPasswordChangedAt() > $payload['iat']) {
            throw new ExpiredTokenException();
        }

        return $user;
    }
}
