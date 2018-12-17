<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Validator;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationBuilder
{
    const CAUSE_IS_UNIQUE = 'isUnique';
    const CAUSE_STARTS_WITH = 'startsWith';
    const CAUSE_IS_GREATER_THAN = 'isGreaterThan';
    const CAUSE_IS_NO_MY_SQL_KEYWORD = 'isNoMySqlKeyword';
    const CAUSE_IS_A_LANGUAGE_CODE = 'isALanguageCode';
    const CAUSE_STARTS_WITH_ALPHABETIC_CHARACTER = 'startsWithAlphabeticCharacter';
    const CAUSE_CONTAINS = 'contains';

    /**
     * @var array
     */
    private $assertions = [];

    /**
     * @var null|array
     */
    private $currentAssertion;

    /**
     * Start asserting this property
     *
     * @param $propertyName
     * @param $propertyValue
     * @return $this
     */
    public function validateThat(string $propertyName, $propertyValue)
    {
        $this->finishAssert();

        $this->currentAssertion = [
            'name' => $propertyName,
            'value' => $propertyValue,
            'constraints' => [],
         ];

        return $this;
    }

    /**
     * Set prop must not be blank (required)
     *
     * @return $this
     */
    public function isNotBlank()
    {
        $this->addConstraint(new NotBlank());

        return $this;
    }

    /**
     * @return $this
     */
    public function isBool()
    {
        $this->addConstraint(new Type('bool'));

        return $this;
    }

    /**
     * @return $this
     */
    public function isString()
    {
        $this->addConstraint(new Type('string'));

        return $this;
    }

    /**
     * @return $this
     */
    public function isNumeric()
    {
        $this->addConstraint(new Type('numeric'));

        return $this;
    }

    /**
     * @return $this
     */
    public function isInt()
    {
        $this->addConstraint(new Type('int'));

        return $this;
    }

    /**
     * @param callable $isValid
     * @return $this
     */
    public function contains(callable $isValid)
    {
        $this->withCallback(
            function () use ($isValid): bool {
                return $isValid();
            },
            'This value "%value%" is not contained.',
            self::CAUSE_CONTAINS
        );

        return $this;
    }

    /**
     * Set prop must be email
     *
     * @return $this
     */
    public function isEmail()
    {
        $this->addConstraint(new Email());

        return $this;
    }

    /**
     * Set prop must be blank
     *
     * @return $this
     */
    public function isBlank()
    {
        $this->addConstraint(new Blank());

        return $this;
    }

    /**
     * Set prop must be in array
     *
     * @param  array $values
     * @return $this
     */
    public function isInArray(array $values)
    {
        $this->addConstraint(new Choice($values));

        return $this;
    }

    /**
     * Bridge Specifications here!
     *
     * @param  callable $isValid
     * @return $this
     */
    public function isUnique(callable $isValid)
    {
        $this->withCallback(
            function () use ($isValid): bool {
                return $isValid();
            },
            'This value is already used.',
            self::CAUSE_IS_UNIQUE
        );

        return $this;
    }

    /**
     * @param $string
     * @return $this
     */
    public function startsWith(string $string)
    {
        $this->withCallback(
            function ($value) use ($string): bool {
                return 0 === strpos($value, $string);
            },
            'This value must start with "%string%".',
            self::CAUSE_STARTS_WITH,
            [
                '%string%' => $string,
            ]
        );

        return $this;
    }

    /**
     * @param int $int
     * @param bool $strict
     * @return $this
     */
    public function isGreaterThan(int $int, bool $strict = false)
    {
        $this->withCallback(
            function ($value) use ($int): bool {
                return $value > $int;
            },
            'The value %value% must be greater than %int%.',
            self::CAUSE_IS_GREATER_THAN,
            [
                '%int%' => $int,
            ],
            $strict
        );

        return $this;
    }

    /**
     * @param int $int
     * @return $this
     */
    public function isGreaterEqualThan(int $int)
    {
        $this->withCallback(
            function ($value) use ($int): bool {
                return $value >= $int;
            },
            'The value %value% must be greater equal than %int%.',
            self::CAUSE_IS_GREATER_THAN,
            [
                '%int%' => $int,
            ],
            true
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function isNoMySqlKeyword()
    {
        $this->withCallback(
            function ($value): bool {
                return !in_array(strtoupper($value), MySQL55Keywords::KEYWORDS, true);
            },
            'This value must not be a MySQL reserved word.',
            self::CAUSE_IS_NO_MY_SQL_KEYWORD
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function isALanguageCode()
    {
        $this->withCallback(
            function ($value): bool {
                $locale = Intl::getLocaleBundle()->getLocaleName($value);

                return (bool) $locale;
            },
            'This language code is not valid.',
            self::CAUSE_IS_A_LANGUAGE_CODE
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function startsWithAlphabeticCharacter()
    {
        $this->withCallback(
            function ($value): bool {
                $found = preg_match('/^([a-zA-Z])/', $value);

                return (1 === $found);
            },
            'The value %value% must start with a alphabetic character.',
            self::CAUSE_STARTS_WITH_ALPHABETIC_CHARACTER
        );

        return $this;
    }

    /**
     * @param  ValidatorInterface $validator
     * @return Validator
     */
    public function getValidator(ValidatorInterface $validator)
    {
        $this->finishAssert();

        $ret = new Validator($validator);

        /**
         * @var mixed
         * @var Constraint $constraint
         */
        foreach ($this->assertions as $assertion) {
            $fieldName = $assertion['name'];
            $value = $assertion['value'];
            $constraints = $assertion['constraints'];

            $ret->addConstraint(
                $fieldName,
                $value,
                $constraints
            );
        }

        $this->assertions = [];

        return $ret;
    }

    /**
     * @internal
     * @param Constraint $constraint
     */
    protected function addConstraint(Constraint $constraint)
    {
        if (!$this->currentAssertion) {
            throw new \LogicException('You must set a property before adding constraints');
        }

        $this->currentAssertion['constraints'][] = $constraint;
    }

    /**
     * @internal
     */
    protected function finishAssert()
    {
        if (!$this->currentAssertion) {
            return;
        }

        $this->assertions[] = $this->currentAssertion;
        $this->currentAssertion = null;
    }

    /**
     * @param callable $isValid return true if valid, false otherwise
     * @param string $message
     * @param string $cause
     * @param array $parameters
     * @param bool $strict
     * @return $this
     */
    public function withCallback(callable $isValid, string $message, string $cause, array $parameters = [], bool $strict = false)
    {
        $fieldName = $this->currentAssertion['name'];

        $this->addConstraint(new Callback(function ($value, ExecutionContextInterface $context) use ($isValid, $fieldName, $message, $cause, $parameters, $strict) {
            if (!$strict && !$value) {
                return;
            }

            if ($isValid($value)) {
                return;
            }

            $constraintViolationBuilder = $context->buildViolation($message);
            $constraintViolationBuilder->setParameter('%value%', $value);
            $constraintViolationBuilder->setCause($cause);

            foreach ($parameters as $key => $parameter) {
                $constraintViolationBuilder->setParameter($key, $parameter);
            }

            $constraintViolationBuilder->atPath($fieldName)
                ->addViolation();
        }));

        return $this;
    }
}
