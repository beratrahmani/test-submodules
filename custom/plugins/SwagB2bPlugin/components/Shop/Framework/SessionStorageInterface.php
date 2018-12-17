<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface SessionStorageInterface
{
    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function remove(string $key);
}
