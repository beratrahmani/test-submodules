<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Service;

class CrudServiceRequest
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $allowedKeys;

    /**
     * @param array $data
     * @param array $allowedKeys
     */
    public function __construct(array $data, array $allowedKeys)
    {
        $this->data = $data;
        $this->allowedKeys = $allowedKeys;
    }

    /**
     * @return array
     */
    public function getFilteredData(): array
    {
        $filteredData = [];

        foreach ($this->allowedKeys as $key) {
            if (!array_key_exists($key, $this->data)) {
                continue;
            }

            $filteredData[$key] = $this->data[$key];
        }

        return $filteredData;
    }

    /**
     * @param string $key
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @return mixed
     */
    public function requireParam(string $key)
    {
        if (!in_array($key, $this->allowedKeys, true)) {
            throw new \DomainException(sprintf('Trying to require an invalid key "%s"', $key));
        }

        if (!array_key_exists($key, $this->data)) {
            throw new \InvalidArgumentException(sprintf('Trying to require a missing key "%s"', $key));
        }

        return $this->data[$key];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasValueForParam(string $key): bool
    {
        try {
            $value = $this->requireParam($key);
        } catch (\DomainException $e) {
            return false;
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return (bool) $value;
    }
}
