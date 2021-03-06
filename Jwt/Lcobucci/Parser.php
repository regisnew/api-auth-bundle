<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Token;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenParser;
use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Token as JwtToken;
use Lcobucci\JWT\Validation\Constraint;

final class Parser implements TokenParser
{
    /**
     * @var JwtConfiguration
     */
    private $config;

    /**
     * @var Constraint[]
     */
    private $constraints;

    public function __construct(JwtConfiguration $config, Clock $clock, array $issuers = null, string $audience = null)
    {
        $this->config = $config;

        $this
            ->addConstraint(new Constraint\ValidAt($clock))
            ->addConstraint(new Constraint\SignedWith($this->config->getSigner(), $this->config->getVerificationKey()))
        ;

        if ($issuers) {
            $this->addConstraint(new Constraint\IssuedBy(...$issuers));
        }

        if ($audience) {
            $this->addConstraint(new Constraint\PermittedFor($audience));
        }
    }

    public function isValid(string $jwt): bool
    {
        return $this->config->getValidator()->validate($this->parseJwt($jwt), ...$this->constraints);
    }

    public function parse(string $jwt): Token
    {
        return Token::fromClaims($this->parseJwt($jwt)->claims()->all());
    }

    private function addConstraint(Constraint $constraint): self
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    private function parseJwt(string $jwt): JwtToken
    {
        return $this->config->getParser()->parse($jwt);
    }
}
