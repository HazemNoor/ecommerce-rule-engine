<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\ValueObject;

use Hazemnoor\RulesEngine\Exception\InvalidArgumentException;

use function preg_match;
use function strtoupper;

final class Currency
{
    /**
     * @var string ISO 4217 currency code
     */
    private string $code;

    public function __construct(string $code)
    {
        $this->setCode($code);
    }

    public static function create(string $code): self
    {
        return new self($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    private function setCode(string $code)
    {
        $code = strtoupper($code);
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            throw InvalidArgumentException::create('code', $code, 'Not a valid ISO 4217 currency code.');
        }
        $this->code = $code;
    }
}
