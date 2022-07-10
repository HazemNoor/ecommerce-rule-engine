<?php

declare(strict_types=1);

use Hazemnoor\RulesEngine\Entity\Cart;
use Hazemnoor\RulesEngine\Entity\Customer;
use Hazemnoor\RulesEngine\Entity\Product;
use Hazemnoor\RulesEngine\Factory\ExpressionFactory;
use Hazemnoor\RulesEngine\RulesEngine;
use Hazemnoor\RulesEngine\ValueObject\Currency;
use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Money;
use Hazemnoor\RulesEngine\ValueObject\Rule;

require __DIR__ . '/../vendor/autoload.php';

$expressionFactory = new ExpressionFactory();

/** @var Rule[] $rules */
$rules = [
    new Rule(
        'Case A',
        $expressionFactory->create('!customer_is_new(cart) && count_unique_products(cart) > 4'),
        Discount::newFlat(5, $expressionFactory->create('is_cart(object)')),
    ),
    new Rule(
        'Case B',
        $expressionFactory->create( 'cart_has_duplicate_products(cart)'),
        Discount::newPercentage(
            100,
            $expressionFactory->create('is_product(object) && product_count(object) > 1 && same_product_max_count(object, 1)')
        ),
    ),
    new Rule(
        'Case C',
        $expressionFactory->create( 'customer_is_new(cart)'),
        Discount::newPercentage(
            100,
            $expressionFactory->create( 'is_product(object) && object.isSpecial()')
        ),
    ),
    new Rule(
        'Case D',
        $expressionFactory->create( 'promo_code(cart) === "Welcome1337"'),
        Discount::newPercentage(100, $expressionFactory->create( 'is_cart(object)')),
    ),
    new Rule(
        'Case E',
        $expressionFactory->create('cart_contains_product_id(cart, "e34328ef-922c-4a88-b005-25cdc5004e20") && cart_contains_product_id(cart, "024f090e-f403-4112-a0fc-0942911e2b65")'),
        Discount::newPercentage(
            100,
            $expressionFactory->create('is_product(object) && product_id(object) === "e34328ef-922c-4a88-b005-25cdc5004e20"')
        ),
    ),
];

$euro = new Currency('EUR');
$product1 = new Product(
    'e34328ef-922c-4a88-b005-25cdc5004e20',
    'MoonShiner Howling Wolf',
    new Money(55, clone $euro),
);
$product2 = new Product(
    '024f090e-f403-4112-a0fc-0942911e2b65',
    'MoonShiner The Answer',
    new Money(42, clone $euro),
);
$product3 = new Product(
    '27a931ac-c8e8-4625-8285-a6226ec18ad5',
    'MoonShiner Pro',
    new Money(13.37, clone $euro),
);
$product4 = new Product(
    '52a42649-ba58-4292-9c33-03c2f8626cb6',
    'MoonShiner Special',
    new Money(35, clone $euro),
    true,
);
$product5 = new Product(
    '1bb622bc-a115-4168-bff7-9df6028a36b5',
    'MoonShiner WeAreMoonShiner',
    new Money(65, clone $euro),
);

$repeatingCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', false);

$newCustomer = new Customer('be7575ed-e1e7-4953-9d71-583f92b93130', true);

$cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $newCustomer);
$cart->addProduct($product1);
$cart->addProduct($product2);
$cart->addProduct($product3);
$cart->addProduct($product4);
$cart->addProduct($product5);
$cart->applyPromoCode('Welcome1337');

$ruleManager = new RulesEngine($rules);
$appliedRule = $ruleManager->run($cart);
