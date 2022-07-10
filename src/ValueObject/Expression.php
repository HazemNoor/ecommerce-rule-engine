<?php

namespace Hazemnoor\RulesEngine\ValueObject;

use Hazemnoor\RulesEngine\Exception\InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function is_bool;

final class Expression
{
    private ExpressionLanguage $expressionEngine;
    private string $expression;

    public function __construct(ExpressionLanguage $expressionEngine, string $expression = 'true')
    {
        $this->expressionEngine = $expressionEngine;
        $this->expression = $expression;
    }

    public function evaluate(array $context = []): bool
    {
        $evaluatedExpression = $this->expressionEngine->evaluate($this->expression, $context);

        if (is_bool($evaluatedExpression)) {
            return $evaluatedExpression;
        }

        throw InvalidArgumentException::create(
            'expression',
            $this->expression,
            'Invalid expression, must evaluate to boolean.'
        );
    }
}
