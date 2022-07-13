<?php

declare(strict_types=1);

session_start();

use Hazemnoor\RulesEngine\Entity\Cart;
use Hazemnoor\RulesEngine\Entity\Customer;
use Hazemnoor\RulesEngine\Entity\Product;
use Hazemnoor\RulesEngine\Factory\ExpressionFactory;
use Hazemnoor\RulesEngine\Factory\RuleFactory;
use Hazemnoor\RulesEngine\RulesEngine;
use Hazemnoor\RulesEngine\ValueObject\Currency;
use Hazemnoor\RulesEngine\ValueObject\Money;

require __DIR__ . '/../vendor/autoload.php';

$ruleFactory = new RuleFactory(new ExpressionFactory());

$rules = $ruleFactory->createFromJsonFile(__DIR__ . '/../examples/rules.json');

/** @var Product[] $allProducts */
$allProducts = [
    new Product(
        'e34328ef-922c-4a88-b005-25cdc5004e20',
        'MoonShiner Howling Wolf',
        new Money(55, new Currency('EUR')),
    ),
    new Product(
        '024f090e-f403-4112-a0fc-0942911e2b65',
        'MoonShiner The Answer',
        new Money(42, new Currency('EUR'))
    ),
    new Product(
        '27a931ac-c8e8-4625-8285-a6226ec18ad5',
        'MoonShiner Pro',
        new Money(13.37, new Currency('EUR')),
    ),
    new Product(
        '52a42649-ba58-4292-9c33-03c2f8626cb6',
        'MoonShiner Special',
        new Money(35, new Currency('EUR')),
        true,
    ),
    new Product(
        '1bb622bc-a115-4168-bff7-9df6028a36b5',
        'MoonShiner WeAreMoonShiner',
        new Money(65, new Currency('EUR')),
    ),
];

if(isset($_POST['customer-type']) && in_array($_POST['customer-type'], ['new', 'repeating'])) {
    $_SESSION['customer-type'] = $_POST['customer-type'];
}
$customerType = $_SESSION['customer-type'] ?? 'new';

$customer = new Customer('bca0dd9f-9497-4efe-9d1c-f602be020d6b', $customerType === 'new');

$cart = new Cart('72428db8-73f7-476d-9ada-bdb45fb24fcf', $customer);

if(array_key_exists('buy', $_POST)) {
    $id = (int) $_POST['buy'];
    if(array_key_exists($id, $allProducts)) {
        $_SESSION['cart-products'][] = $id;
    }
}

if(array_key_exists('remove', $_POST)) {
    $id = (int) $_POST['remove'];
    if(array_key_exists($id, $_SESSION['cart-products'])) {
        unset($_SESSION['cart-products'][$id]);
        $_SESSION['cart-products'] = array_values($_SESSION['cart-products']);
    }
}

if(array_key_exists('empty', $_POST)) {
    $id = (int) $_POST['empty'];
    if($id === 1) {
        unset($_SESSION['cart-products']);
    }
}

$cartProducts = $_SESSION['cart-products'] ?? [];

foreach ($cartProducts as $cartProductId) {
    $cart->addProduct(clone $allProducts[$cartProductId]);
}

if(isset($_POST['promo'])) {
    $_SESSION['promo'] = $_POST['promo'];
}
$promo = $_SESSION['promo'] ?? '';

$cart->applyPromoCode($promo);

$ruleManager = new RulesEngine($rules);
$appliedRule = $ruleManager->run($cart);

$customerType = $customer->isNew() ? 'new' : 'repeating';
$otherCustomerType = $customer->isNew() ? 'repeating' : 'new';

$url = '//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

?>
<html lang="en">
<head>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <title>PHP eCommerce Rule engine</title>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <p>Products</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Hoodie</th>
                            <th scope="col">Price</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allProducts as $id => $product): ?>
                            <tr>
                                <td><?= $product->getName(); ?></td>
                                <td><?= $product->getOriginalPrice()->getAmount(); ?></td>
                                <td>
                                    <form action="<?= $url; ?>" method="post">
                                        <input type="hidden" name="buy" value="<?= $id; ?>" >
                                        <button type="submit" class="btn btn-primary btn-sm">Buy</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p>Rules, they are applied in order.</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Case</th>
                            <th scope="col">Condition</th>
                            <th scope="col">Discount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Case A</td>
                            <td>A repeating Customer & ordering more than 4 pieces of different hoodies</td>
                            <td>5 € Off the total</td>
                        </tr>
                        <tr>
                            <td>Case B</td>
                            <td>Ordered 2 pieces of the same product (more than one piece of a product)</td>
                            <td>Get the first one free</td>
                        </tr>
                        <tr>
                            <td>Case C</td>
                            <td>Never ordered before</td>
                            <td>Get the special “OneHoodie” for free</td>
                        </tr>
                        <tr>
                            <td>Case D</td>
                            <td>Entering the PromoCode - Welcome1337</td>
                            <td>Free Purchase</td>
                        </tr>
                        <tr>
                            <td>Case E</td>
                            <td>When there’s ProductA (MoonShiner Howling Wolf) & ProductB (MoonShiner The Answer) in the cart</td>
                            <td>Get first one free</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row">
                <div class="col">
                    <?php if(empty($cart->getProducts())): ?>
                    <p>Cart is empty</p>
                    <?php else: ?>
                    <p>Cart</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Hoodie</th>
                            <th>Original Price</th>
                            <th>Price</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($cart->getProducts() as $id => $cartProduct): ?>
                            <tr>
                                <td><?= $cartProduct->getName(); ?></td>
                                <td><?= $cartProduct->getOriginalPrice()->getAmount().' '
                                    .$cartProduct->getOriginalPrice()->getCurrency()->getCode(); ?></td>
                                <td><?= $cartProduct->getPrice()->getAmount().' '
                                    .$cartProduct->getPrice()->getCurrency()->getCode(); ?></td>
                                <td>
                                    <form action="<?= $url; ?>" method="post">
                                        <input type="hidden" name="remove" value="<?= $id; ?>" >
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?= $cart->getOriginalPrice()->getAmount().' '
                                .$cart->getOriginalPrice()->getCurrency()->getCode(); ?></th>
                            <th><?= $cart->getPrice()->getAmount().' '
                                .$cart->getPrice()->getCurrency()->getCode(); ?></th>
                            <th>
                                <form action="<?= $url; ?>" method="post">
                                    <input type="hidden" name="empty" value="1" >
                                    <button type="submit" class="btn btn-danger btn-sm">Empty Cart</button>
                                </form>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                    <div class="col">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td>
                                        <?php if ($cart->getPromoCode() !== null): ?>
                                            Applied promo is <b> <?= htmlspecialchars($cart->getPromoCode()); ?></b>
                                        <?php else: ?>
                                            No Promo code applied
                                        <?php endif; ?>
                                    </td>
                                <td>
                                    <form action="<?= $url; ?>" method="post">
                                        <input type="text" name="promo" class="form-control" >
                                        <br>
                                        <button type="submit" class="btn btn-primary btn-sm">Apply promo</button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Customer is a <b><?= $customerType; ?></b> customer
                                </td>
                                <td>
                                    <form action="<?= $url; ?>" method="post">
                                        <input type="hidden" name="customer-type" value="<?= $otherCustomerType ?>" >
                                        <button type="submit" class="btn btn-primary btn-sm">change to <?=
                                            $otherCustomerType; ?></button>
                                    </form>

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php if ($appliedRule !== null): ?>
                                        The Applied rule is <b> <?= $appliedRule->getName(); ?></b>
                                    <?php else: ?>
                                        No rule is applied yet.
                                    <?php endif; ?>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
