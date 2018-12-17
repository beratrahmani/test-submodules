<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\B2B\Shop\Framework\SessionStorageInterface;

class SessionStorage implements SessionStorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value)
    {
        Shopware()->Session()->offsetSet($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        return Shopware()->Session()->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $key)
    {
        Shopware()->Session()->offsetUnset($key);
    }
}
