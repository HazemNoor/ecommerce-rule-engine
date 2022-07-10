<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\Factory;

use Hazemnoor\RulesEngine\Entity\Cart;
use Hazemnoor\RulesEngine\Entity\Product;

function is_cart(object $object): bool
{
    return $object instanceof Cart;
}

function is_product(object $object): bool
{
    return $object instanceof Product;
}

function count_products(Cart $cart): int
{
    return \count($cart->getProducts());
}

function product_count(Product $product): int
{
    $count = 0;

    foreach ($product->getCart()->getProducts() as $aProduct) {
        if ($aProduct->getId() === $product->getId()) {
            $count += 1;
        }
    }

    return $count;
}

function customer_is_new(Cart $cart): bool
{
    return $cart->getCustomer()->isNew();
}

function count_unique_products(Cart $cart): int
{
    return \count(
        \array_unique(
            \array_map(static fn (Product $product): string => $product->getId(), $cart->getProducts())
        )
    );
}

function cart_has_duplicate_products(Cart $cart): bool
{
    $idsCount = \array_count_values(
        \array_map(static fn (Product $product): string => $product->getId(), $cart->getProducts())
    );

    foreach ($idsCount as $idCount) {
        if ($idCount > 1) {
            return true;
        }
    }

    return false;
}

function cart_contains_product_id(Cart $cart, string $productId): bool
{
    foreach ($cart->getProducts() as $product) {
        if ($product->getId() === $productId) {
            return true;
        }
    }

    return false;
}

function product_id(Product $product): string
{
    return $product->getId();
}

function promo_code(Cart $cart): ?string
{
    return $cart->getPromoCode();
}

function same_product_max_count(Product $product, int $maxCount): bool
{
    $count = 0;

    foreach ($product->getCart()->getProducts() as $aProduct) {
        if ($product->getId() === $aProduct->getId() && $aProduct->getDiscount() !== null) {
            $count += 1;
        }
    }

    return $count < $maxCount;
}
