<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\ValueObject;

use Hazemnoor\RulesEngine\Exception\InvalidArgumentException;

final class Discount
{
    public const TYPE_FLAT = 'flat';
    public const TYPE_PERCENTAGE = 'percentage';

    /**
     * @var float positive value
     */
    private float $value;

    /**
     * @var string self::TYPE_*
     */
    private string $type;

    /**
     * @var Expression The condition that this discount applies to
     */
    private Expression $expression;

    private function __construct(float $value, string $type, Expression $expression)
    {
        $this->value = $value;
        $this->type = $type;
        $this->expression = $expression;
    }

    public static function newFlat(float $value, Expression $expression): self
    {
        if ($value < 0) {
            InvalidArgumentException::create('value', $value, "Flat discount value can't be less than zero.");
        }

        return new self($value, self::TYPE_FLAT, $expression);
    }

    public static function newPercentage(int $value, Expression $expression): self
    {
        if ($value > 100 || $value < 0) {
            InvalidArgumentException::create('value', $value, "Percentage discount value must be between [0, 100]");
        }

        return new self($value, self::TYPE_PERCENTAGE, $expression);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function evaluate(array $context): bool
    {
        return $this->expression->evaluate($context);
    }
}
