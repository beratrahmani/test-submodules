<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface TranslationServiceInterface
{
    /**
     * @param string $name
     * @param string $namespace
     * @param string $default
     * @return string
     */
    public function get(string $name, string $namespace, string $default): string;
}
