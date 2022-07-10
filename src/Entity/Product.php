<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\Entity;

use Hazemnoor\RulesEngine\Traits\ApplyDiscountTrait;
use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Money;

final class Product
{
    use ApplyDiscountTrait;

    private string $id;
    private string $name;
    private Money $originalPrice;
    private Money $price;
    private bool $special;
    private ?Cart $cart = null;
    private ?Discount $discount = null;

    public function __construct(string $id, string $name, Money $originalPrice, bool $special = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->originalPrice = $originalPrice;
        $this->price = clone $this->originalPrice;
        $this->special = $special;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOriginalPrice(): Money
    {
        return $this->originalPrice;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function isSpecial(): bool
    {
        return $this->special;
    }

    public function setCart(Cart $cart): void
    {
        if (!\is_null($this->cart)) {
            throw new \Exception(\sprintf('The product %s is already assigned to another cart.', $this->id));
        }

        $this->cart = $cart;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function applyDiscount(Discount $discount): void
    {
        if (!\is_null($this->discount)) {
            return;
        }

        if (!$discount->evaluate(['object' => $this])) {
            return;
        }

        $this->discount = $discount;

        $this->price = $this->applyDiscountOnPrice($this->price, $discount);

        $this->cart->updatePrice();
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }
}
