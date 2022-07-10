<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine;

use Hazemnoor\RulesEngine\Entity\Cart;
use Hazemnoor\RulesEngine\ValueObject\Rule;

final class RulesEngine
{
    /**
     * @var Rule[]
     */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Check Cart against all Rules, returning the first matching Rule
     */
    public function run(Cart $cart): ?Rule
    {
        foreach ($this->rules as $rule) {
            if ($rule->evaluate(['cart' => $cart])) {
                $cart->applyDiscount($rule->getDiscount());

                return $rule;
            }
        }

        return null;
    }
}
