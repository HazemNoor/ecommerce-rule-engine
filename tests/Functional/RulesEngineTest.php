<?php

declare(strict_types=1);

namespace Tests\Hazemnoor\RulesEngine\Functional;

use Hazemnoor\RulesEngine\Entity\Cart;
use Hazemnoor\RulesEngine\Entity\Customer;
use Hazemnoor\RulesEngine\Entity\Product;
use Hazemnoor\RulesEngine\Factory\ExpressionFactory;
use Hazemnoor\RulesEngine\RulesEngine;
use Hazemnoor\RulesEngine\ValueObject\Currency;
use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Money;
use Hazemnoor\RulesEngine\ValueObject\Rule;
use PHPUnit\Framework\TestCase;

final class RulesEngineTest extends TestCase
{
    public function testRuleCaseA(): void
    {
        $expressionFactory = new ExpressionFactory();

        $rule = new Rule(
            'Case A',
            $expressionFactory->create('!customer_is_new(cart) && count_unique_products(cart) > 4'),
            Discount::newFlat(5, $expressionFactory->create('is_cart(object)')),
        );

        $repeatingCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', false);

        $products = [
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('024f090e-f403-4112-a0fc-0942911e2b65', 'MoonShiner The Answer', 42),
            $this->getProduct('27a931ac-c8e8-4625-8285-a6226ec18ad5', 'MoonShiner Pro', 13.37),
            $this->getProduct('52a42649-ba58-4292-9c33-03c2f8626cb6', 'MoonShiner Special', 35, true),
            $this->getProduct('1bb622bc-a115-4168-bff7-9df6028a36b5', 'MoonShiner WeAreMoonShiner', 65),
        ];

        $cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $repeatingCustomer);

        foreach ($products as $product) {
            $cart->addProduct($product);
        }

        self::assertTrue($cart->getPrice()->equals($cart->getOriginalPrice()));

        $ruleManager = new RulesEngine([$rule]);
        $appliedRule = $ruleManager->run($cart);

        self::assertSame($rule, $appliedRule);

        self::assertSame(210.37, $cart->getOriginalPrice()->getAmount());
        self::assertSame(205.37, $cart->getPrice()->getAmount());

        self::assertSame($rule->getDiscount(), $cart->getDiscount());
    }

    public function testRuleCaseB(): void
    {
        $expressionFactory = new ExpressionFactory();

        $rule = new Rule(
            'Case B',
            $expressionFactory->create('cart_has_duplicate_products(cart)'),
            Discount::newPercentage(
                100,
                $expressionFactory->create(
                    'is_product(object) && product_count(object) > 1 && same_product_max_count(object, 1)'
                )
            ),
        );

        $repeatingCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', false);

        $products = [
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('024f090e-f403-4112-a0fc-0942911e2b65', 'MoonShiner The Answer', 42),
            $this->getProduct('27a931ac-c8e8-4625-8285-a6226ec18ad5', 'MoonShiner Pro', 13.37),
            $this->getProduct('52a42649-ba58-4292-9c33-03c2f8626cb6', 'MoonShiner Special', 35, true),
            $this->getProduct('1bb622bc-a115-4168-bff7-9df6028a36b5', 'MoonShiner WeAreMoonShiner', 65),
        ];

        $cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $repeatingCustomer);

        foreach ($products as $product) {
            $cart->addProduct($product);
        }

        self::assertTrue($cart->getPrice()->equals($cart->getOriginalPrice()));

        $ruleManager = new RulesEngine([$rule]);
        $appliedRule = $ruleManager->run($cart);

        self::assertSame($rule, $appliedRule);

        self::assertSame(265.37, $cart->getOriginalPrice()->getAmount());
        self::assertSame(210.37, $cart->getPrice()->getAmount());

        self::assertSame(0.0, $cart->getProducts()[0]->getPrice()->getAmount());
        self::assertSame(55.0, $cart->getProducts()[0]->getOriginalPrice()->getAmount());
        self::assertSame($rule->getDiscount(), $cart->getProducts()[0]->getDiscount());
    }

    public function testRuleCaseC(): void
    {
        $expressionFactory = new ExpressionFactory();

        $rule = new Rule(
            'Case C',
            $expressionFactory->create('customer_is_new(cart)'),
            Discount::newPercentage(
                100,
                $expressionFactory->create('is_product(object) && object.isSpecial()')
            ),
        );

        $newCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', true);

        $products = [
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('024f090e-f403-4112-a0fc-0942911e2b65', 'MoonShiner The Answer', 42),
            $this->getProduct('27a931ac-c8e8-4625-8285-a6226ec18ad5', 'MoonShiner Pro', 13.37),
            $this->getProduct('52a42649-ba58-4292-9c33-03c2f8626cb6', 'MoonShiner Special', 35, true),
            $this->getProduct('1bb622bc-a115-4168-bff7-9df6028a36b5', 'MoonShiner WeAreMoonShiner', 65),
        ];

        $cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $newCustomer);

        foreach ($products as $product) {
            $cart->addProduct($product);
        }

        self::assertTrue($cart->getPrice()->equals($cart->getOriginalPrice()));

        $ruleManager = new RulesEngine([$rule]);
        $appliedRule = $ruleManager->run($cart);

        self::assertSame($rule, $appliedRule);

        self::assertSame(210.37, $cart->getOriginalPrice()->getAmount());
        self::assertSame(175.37, $cart->getPrice()->getAmount());

        self::assertSame(0.0, $cart->getProducts()[3]->getPrice()->getAmount());
        self::assertSame(35.0, $cart->getProducts()[3]->getOriginalPrice()->getAmount());
        self::assertSame($rule->getDiscount(), $cart->getProducts()[3]->getDiscount());
    }

    public function testRuleCaseD(): void
    {
        $expressionFactory = new ExpressionFactory();

        $rule = new Rule(
            'Case D',
            $expressionFactory->create('promo_code(cart) === "Welcome1337"'),
            Discount::newPercentage(100, $expressionFactory->create('is_cart(object)')),
        );

        $newCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', true);

        $products = [
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('024f090e-f403-4112-a0fc-0942911e2b65', 'MoonShiner The Answer', 42),
            $this->getProduct('27a931ac-c8e8-4625-8285-a6226ec18ad5', 'MoonShiner Pro', 13.37),
            $this->getProduct('52a42649-ba58-4292-9c33-03c2f8626cb6', 'MoonShiner Special', 35, true),
            $this->getProduct('1bb622bc-a115-4168-bff7-9df6028a36b5', 'MoonShiner WeAreMoonShiner', 65),
        ];

        $cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $newCustomer);

        foreach ($products as $product) {
            $cart->addProduct($product);
        }

        $cart->applyPromoCode('Welcome1337');

        self::assertTrue($cart->getPrice()->equals($cart->getOriginalPrice()));

        $ruleManager = new RulesEngine([$rule]);
        $appliedRule = $ruleManager->run($cart);

        self::assertSame($rule, $appliedRule);

        self::assertSame(210.37, $cart->getOriginalPrice()->getAmount());
        self::assertSame(0.0, $cart->getPrice()->getAmount());

        self::assertSame($rule->getDiscount(), $cart->getDiscount());
    }

    public function testRuleCaseE(): void
    {
        $expressionFactory = new ExpressionFactory();

        $rule = new Rule(
            'Case E',
            $expressionFactory->create(
                'cart_contains_product_id(cart, "e34328ef-922c-4a88-b005-25cdc5004e20") && cart_contains_product_id(cart, "024f090e-f403-4112-a0fc-0942911e2b65")'
            ),
            Discount::newPercentage(
                100,
                $expressionFactory->create(
                    'is_product(object) && product_id(object) === "e34328ef-922c-4a88-b005-25cdc5004e20"'
                )
            ),
        );

        $newCustomer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', true);

        $products = [
            $this->getProduct('e34328ef-922c-4a88-b005-25cdc5004e20', 'MoonShiner Howling Wolf', 55),
            $this->getProduct('024f090e-f403-4112-a0fc-0942911e2b65', 'MoonShiner The Answer', 42),
            $this->getProduct('27a931ac-c8e8-4625-8285-a6226ec18ad5', 'MoonShiner Pro', 13.37),
            $this->getProduct('52a42649-ba58-4292-9c33-03c2f8626cb6', 'MoonShiner Special', 35, true),
            $this->getProduct('1bb622bc-a115-4168-bff7-9df6028a36b5', 'MoonShiner WeAreMoonShiner', 65),
        ];

        $cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $newCustomer);

        foreach ($products as $product) {
            $cart->addProduct($product);
        }

        self::assertTrue($cart->getPrice()->equals($cart->getOriginalPrice()));

        $ruleManager = new RulesEngine([$rule]);
        $appliedRule = $ruleManager->run($cart);

        self::assertSame($rule, $appliedRule);

        self::assertSame(210.37, $cart->getOriginalPrice()->getAmount());
        self::assertSame(155.37, $cart->getPrice()->getAmount());

        self::assertSame(0.0, $cart->getProducts()[0]->getPrice()->getAmount());
        self::assertSame(55.0, $cart->getProducts()[0]->getOriginalPrice()->getAmount());
        self::assertSame($rule->getDiscount(), $cart->getProducts()[0]->getDiscount());
    }

    private function getProduct(string $id, string $name, float $amount, bool $special = false): Product
    {
        return new Product($id, $name, new Money($amount, new Currency('EUR')), $special);
    }
}
