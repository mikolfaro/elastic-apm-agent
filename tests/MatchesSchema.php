<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests;

use JsonSchema\Constraints\Constraint as JsonConstraint;
use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;
use function join;
use function realpath;

class MatchesSchema extends Constraint
{
    /**
     * @var string $schemaPath
     */
    private $schemaPath;
    /**
     * @var Validator $validator
     */
    private $validator;

    public function __construct(string $schemaPath)
    {
        parent::__construct();

        $this->schemaPath = $schemaPath;
        $this->validator  = new Validator();
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool
    {
        $schema = (object) ['$ref' => 'file://' . realpath($this->schemaPath)];
        $this->validator->validate($other, $schema, JsonConstraint::CHECK_MODE_TYPE_CAST);

        return $this->validator->isValid();
    }

    public function toString(): string
    {
        return 'matches JSON schema';
    }

    /**
     * @inheritdoc
     */
    protected function additionalFailureDescription($other): string
    {
        $errors = [];
        foreach ($this->validator->getErrors() as $error) {
            $errors[] = "\t[" . $error['pointer'] . "\t" . $error['message'];
        }
        return join("\n", $errors);
    }
}
