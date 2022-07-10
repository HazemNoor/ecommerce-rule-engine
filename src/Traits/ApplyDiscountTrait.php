<?php

namespace Hazemnoor\RulesEngine\Traits;

use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Money;

use function is_null;

trait ApplyDiscountTrait
{
    private function applyDiscountOnPrice(Money $price, ?Discount $discount): Money
    {
        if (is_null($discount)) {
            return $price;
        }

        $discountMoney = new Money(0, $price->getCurrency());

        if ($discount->getType() === Discount::TYPE_FLAT) {
            $discountMoney = new Money(
                $discount->getValue(),
                $price->getCurrency(),
            );
        } elseif ($discount->getType() === Discount::TYPE_PERCENTAGE) {
            $discountMoney = new Money(
                $price->getAmount() * $discount->getValue() / 100,
                $price->getCurrency(),
            );
        }

        if ($discountMoney->getAmount() > $price->getAmount()) {
            throw new \Exception(
                \sprintf(
                    "Discount amount %.2f can't be greater than Price amount %.2f",
                    $discountMoney->getAmount(),
                    $price->getAmount(),
                )
            );
        }

        return $price->subtractMoney($discountMoney);
    }
}
