<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\ValueObject;

final class Rule
{
    private string $name;
    private Expression $expression;
    private Discount $discount;

    public function __construct(string $name, Expression $expression, Discount $discount)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->discount = $discount;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function evaluate(array $context): bool
    {
        return $this->expression->evaluate($context);
    }

    public function getDiscount(): Discount
    {
        return $this->discount;
    }
}
