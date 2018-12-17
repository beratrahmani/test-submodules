<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\Validator\ValidationException;
use Symfony\Component\Validator\ConstraintViolationList;

class OrderNumberFileValidationException extends ValidationException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', int $code = 0, \Exception $previous = null)
    {
        $entity = new OrderNumberFileEntity();
        $violations = new ConstraintViolationList([]);
        parent::__construct($entity, $violations, $message, $code, $previous);
    }

    /**
     * @param ValidationException $exception
     */
    public function addViolations(ValidationException $exception)
    {
        $this->getViolations()->addAll($exception->getViolations());
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->getViolations());
    }
}
