<?php

declare(strict_types=1);

namespace Hazemnoor\RulesEngine\Factory;

use Exception;
use Hazemnoor\RulesEngine\ValueObject\Discount;
use Hazemnoor\RulesEngine\ValueObject\Rule;

use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function in_array;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class RuleFactory
{
    private ExpressionFactory $expressionFactory;

    public function __construct(ExpressionFactory $expressionFactory)
    {
        $this->expressionFactory = $expressionFactory;
    }

    /**
     * @return Rule[]
     */
    public function createFromJsonFile(string $jsonPath): array
    {
        if (!file_exists($jsonPath)) {
            throw new Exception('JSON File not exist');
        }

        $rulesData = json_decode(file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);

        return self::createFromArray($rulesData);
    }

    /**
     * @return Rule[]
     */
    public function createFromArray(array $rulesData): array
    {
        if (!self::isValidRulesData($rulesData)) {
            throw new Exception('Invalid Rules data provided');
        }

        $rules = [];

        foreach ($rulesData as $ruleData) {
            if ($ruleData['discount']['type'] === 'flat') {
                $discount = Discount::newFlat(
                    (float) $ruleData['discount']['value'],
                    $this->expressionFactory->create($ruleData['discount']['expression']),
                );
            } elseif ($ruleData['discount']['type'] === 'percentage') {
                $discount = Discount::newPercentage(
                    (int) $ruleData['discount']['value'],
                    $this->expressionFactory->create($ruleData['discount']['expression']),
                );
            }

            $rules[] = new Rule(
                $ruleData['name'],
                $this->expressionFactory->create($ruleData['expression']),
                $discount,
            );
        }

        return $rules;
    }

    private function isValidRulesData($rulesData): bool
    {
        if (!is_array($rulesData)) {
            return false;
        }

        foreach ($rulesData as $ruleData) {
            if (
                !array_key_exists('name', $ruleData)
                || !array_key_exists('expression', $ruleData)
                || !array_key_exists('discount', $ruleData)
            ) {
                return false;
            }

            if (
                !array_key_exists('type', $ruleData['discount'])
                || !array_key_exists('value', $ruleData['discount'])
                || !array_key_exists('expression', $ruleData['discount'])
            ) {
                return false;
            }

            if (!in_array($ruleData['discount']['type'], ['flat', 'percentage'], true)) {
                return false;
            }
        }

        return true;
    }
}
