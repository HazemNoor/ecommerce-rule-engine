<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\ValueObject;

use Hazemnoor\RulesEngine\Exception\InvalidArgumentException;

class Money
{
    private float $amount;

    private Currency $currency;

    public function __construct(float $amount, Currency $currency)
    {
        $this->setAmount($amount);
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function addMoney(Money $money): self
    {
        $this->checkSameCurrency($money);

        return new self($this->getAmount() + $money->getAmount(), $this->getCurrency());
    }

    public function subtractMoney(Money $money): self
    {
        $this->checkSameCurrency($money);

        return new self($this->getAmount() - $money->getAmount(), $this->getCurrency());
    }

    public function equals(Money $money): bool
    {
        return
            $this->currency->getCode() === $money->getCurrency()->getCode()
            && $this->amount === $money->getAmount();
    }

    private function setAmount(float $amount)
    {
        if ($amount < 0) {
            throw InvalidArgumentException::create('amount', $amount, 'Amount cannot be a negative value.');
        }

        $this->amount = $amount;
    }

    private function checkSameCurrency(Money $money)
    {
        if ($this->getCurrency()->getCode() !== $money->getCurrency()->getCode()) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Money must be of the same Currency ["%s" !== "%s"].',
                    $this->getCurrency()->getCode(),
                    $money->getCurrency()->getCode(),
                )
            );
        }
    }
}
