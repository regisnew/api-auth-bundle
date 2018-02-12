<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Token;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenParser;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JwtAuthenticator extends Authenticator
{
    private $tokenParser;
    private $identityClaim;

    public function __construct(Extractor $extractor, TokenParser $tokenParser, string $identityClaim = null)
    {
        parent::__construct($extractor);

        $this->tokenParser = $tokenParser;
        $this->identityClaim = $identityClaim;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->tokenParser->isValid($credentials);
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $jwtToken = $this->tokenParser->parse($credentials);

        if (null === $username = $jwtToken->get($this->identityClaim ?? Token::SUBJECT)) {
            throw new AuthenticationException('Username could not be identified.');
        }

        return $userProvider->loadUserByUsername($username);
    }
}