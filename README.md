
## PHP eCommerce Rule engine

This is an eCommerce Rule engine, It will check the Cart against set of business rules and apply the first matching one.

### Usage
Checkout [examples](examples) or [tests](tests/Functional/RulesEngineTest.php)

### Installation
This package is still under development, but can be used in any framework using composer
- Add GitHub repository source
```shell
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:HazemNoor/ecommerce-rule-engine.git",
            "no-api": true
        }
    ]
```
- Add using composer require
```shell
composer require hazemnoor/ecommerce-rule-engine dev-main
```

## Development
1. Fetch project
```shell
git clone git@github.com:HazemNoor/ecommerce-rule-engine.git
cd ecommerce-rule-engine
```
2. Make sure to have `docker-compose` installed on your machine, then execute this command to build docker images
```shell
make build
```
3. Run this command to execute `composer install`
```shell
make install
```
4. You can start the server and goto [http://rule.localhost](http://rule.localhost) to test the package
```shell
make start
```
## Testing

You can run tests using this command
```shell
make test
```

## Other commands
- Checkout list of commands by typing
```shell
make
```

## Coding Style
The coding style used is [PSR-12](https://www.php-fig.org/psr/psr-12/) and is included with the testing command `make test` using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

## Code coverage
Run code coverage, an HTML report will be generated in `.code-coverage` directory
```shell
make coverage
```

---
<details>
  <summary>Task Details, click to expand</summary>


As we value loyal customers in our imaginary **MoonShiner Hoodies Merchandise Shop** with some additional gadgets.

We want to offer them special deals, when

| Case   | Condition                                                                 | Discount                             |
|--------|---------------------------------------------------------------------------|--------------------------------------|
| Case A | A repeating Customer & ordering more than 4 pieces of different hoodies   | 5 € Off the total                    |
| Case B | Ordered 2 pieces of the same product (_more than one piece of a product_) | Get the first one free               |
| Case C | Never ordered before                                                      | Get the special “OneHoodie” for free |
| Case D | Entering the PromoCode - Welcome1337                                      | Free Purchase                        |
| Case E | When there’s ProductA & ProductB in the cart                              | Get first one free                   |

### MVP Requirements
1. [x] Unit tests for all cases
2. [x] Rules should be maintainable/configurable through a structured JSON/Yaml/Excel file
3. [x] Rules based on quantity of articles & value of the cart - with conditions `>`, `==`, `<`
4. [x] Simple Frontend form for users to input their products & test the rules
5. [x] Consumable Symfony/Laravel package

### Optional
1. [ ] Connect to a [Google spreadsheet](https://docs.google.com/spreadsheets/d/1y4iaZfC0HvT8B_CPlhsfe4NbJd2k68fb00GuyrO87VU/) - as your primary data source.

| Hoodie                     | Price    |
|----------------------------|----------|
| MoonShiner Howling Wolf    | 55.00 €  |
| MoonShiner The Answer      | 42.00 €  |
| MoonShiner Pro             | 133.70 € |
| MoonShiner Special         | 35.00 €  |
| MoonShiner WeAreMoonShiner | 65.00 €  |

2. [ ] Database integration
</details>
