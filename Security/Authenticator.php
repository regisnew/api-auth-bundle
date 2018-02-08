<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * @see https://symfony.com/doc/current/_images/authentication-guard-methods.png
 */
class Authenticator extends AbstractGuardAuthenticator
{
    private $authHeader;

    public function __construct(string $authHeader)
    {
        $this->authHeader = $authHeader;
    }

    public function supports(Request $request): bool
    {
        $header = $request->headers->get($this->authHeader, '');

        return $header ? 0 === strpos($header, 'Token ') : false;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $code = Response::HTTP_UNAUTHORIZED;

        return JsonResponse::create(['message' => Response::$statusTexts[$code]], $code);
    }

    public function getCredentials(Request $request): array
    {
        return ['token' => substr($request->headers->get($this->authHeader), 6)];
    }

    public function getUser($credentials, UserProviderInterface $provider): UserInterface
    {
        if ($provider instanceof ApiKeyUserProvider) {
            return $provider->loadUserByApiKey($credentials['token']);
        }

        return $provider->loadUserByUsername($credentials['token']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $code = Response::HTTP_FORBIDDEN;

        return JsonResponse::create(['message' => Response::$statusTexts[$code]], $code);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}