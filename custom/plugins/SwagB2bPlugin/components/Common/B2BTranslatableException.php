<?php declare(strict_types=1);

namespace Shopware\B2B\Common;

interface B2BTranslatableException extends B2BException
{
    /**
     * @return string
     */
    public function getTranslationMessage(): string;

    /**
     * @return array
     */
    public function getTranslationParams(): array;
}
