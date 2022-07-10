<?php

namespace Hazemnoor\RulesEngine\Factory;

use Hazemnoor\RulesEngine\ValueObject\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function get_defined_functions;
use function str_replace;
use function str_starts_with;

final class ExpressionFactory
{
    private const FUNCTIONS_NAMESPACE = 'hazemnoor\rulesengine\factory\\';

    private static ?ExpressionLanguage $expressionEngine = null;

    public function create(string $expression): Expression
    {
        return new Expression($this->getExpressionEngine(), $expression);
    }

    private function getExpressionEngine(): ExpressionLanguage
    {
        if (self::$expressionEngine !== null) {
            return self::$expressionEngine;
        }

        require_once __DIR__ . '/Helpers.php';

        self::$expressionEngine = new ExpressionLanguage();

        foreach (self::getExpressionFunctions() as $expressionFunction) {
            self::$expressionEngine->addFunction($expressionFunction);
        }

        return self::$expressionEngine;
    }

    /**
     * @return ExpressionFunction[]
     */
    private static function getExpressionFunctions(): array
    {
        $userDefinedFunctions = get_defined_functions()['user'];
        if (empty($userDefinedFunctions)) {
            return [];
        }

        $expressionFunctions = [];
        foreach ($userDefinedFunctions as $userDefinedFunction) {
            if (str_starts_with($userDefinedFunction, self::FUNCTIONS_NAMESPACE)) {
                $expressionFunctions[] = ExpressionFunction::fromPhp(
                    $userDefinedFunction,
                    str_replace(self::FUNCTIONS_NAMESPACE, '', $userDefinedFunction)
                );
            }
        }

        return $expressionFunctions;
    }
}
