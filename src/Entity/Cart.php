<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\Entity;

use Hazemnoor\RulesEngine\Traits\ApplyDiscountTrait;
use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Money;

final class Cart
{
    use ApplyDiscountTrait;

    private string $id;
    private Customer $customer;
    /**
     * @var Product[]
     */
    private array $products = [];
    private Money $originalPrice;
    private Money $price;
    private ?string $promoCode = null;
    private ?Discount $discount = null;

    public function __construct(string $id, Customer $customer)
    {
        $this->id = $id;
        $this->customer = $customer;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function addProduct(Product $product): void
    {
        $product->setCart($this);

        $this->products[] = $product;

        $this->updatePrice();
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getOriginalPrice(): Money
    {
        return $this->originalPrice;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function applyPromoCode(string $promoCode)
    {
        $this->promoCode = $promoCode;

        $this->updatePrice();
    }

    public function getPromoCode(): ?string
    {
        return $this->promoCode;
    }

    public function applyDiscount(Discount $discount): void
    {
        if ($this->alreadyHasDiscount()) {
            return;
        }

        foreach ($this->products as $product) {
            $product->applyDiscount($discount);
        }

        if (!$discount->evaluate(['object' => $this])) {
            return;
        }

        $this->discount = $discount;

        $this->updatePrice();
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function updatePrice(): void
    {
        if (empty($this->products)) {
            return;
        }

        $this->originalPrice = new Money(0, $this->products[0]->getPrice()->getCurrency());
        $this->price = clone $this->originalPrice;

        foreach ($this->products as $product) {
            $this->originalPrice = $this->originalPrice->addMoney($product->getOriginalPrice());
            $this->price = $this->price->addMoney($product->getPrice());
        }

        $this->price = $this->applyDiscountOnPrice($this->price, $this->discount);
    }

    /**
     * Check if Cart or any Product has Discount applied to it.
     */
    public function alreadyHasDiscount(): bool
    {
        if ($this->discount !== null) {
            return true;
        }

        foreach ($this->products as $product) {
            if ($product->getDiscount() !== null) {
                return true;
            }
        }

        return false;
    }
}
