<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class CartAccessResult
{
    /**
     * @var array
     */
    public $information = [];

    /**
     * @var int
     */
    public $errorCount = 0;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var bool
     */
    private $clearable = true;

    /**
     * @param string $sender
     * @param string $error
     * @param array $params
     */
    public function addError(string $sender, string $error, array $params = [])
    {
        $this->errors[] = new ErrorMessage($sender, $error, $params);
        $this->errorCount++;
    }

    /**
     * @param string $sender
     * @param string $type
     * @param array $params
     */
    public function addInformation(string $sender, string $type, array $params)
    {
        $this->information[] = new InformationMessage($sender, $type, $params);
    }

    /**
     * @param CartAccessResult[] $results
     */
    public function addErrors(self ... $results)
    {
        foreach ($results as $result) {
            foreach ($result->getErrors() as $error) {
                $this->errors[] = $error;
            }
        }

        $this->errorCount = count($this->errors);
    }

    /**
     * @param bool $set
     */
    public function setClearable(bool $set = true)
    {
        $this->clearable = $set;
    }

    /**
     * @return bool
     */
    public function isClearable(): bool
    {
        return $this->clearable;
    }

    /**
     * @return ErrorMessage[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return (bool) $this->errorCount;
    }
}
