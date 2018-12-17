<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\Bundle\SearchBundle\ConditionInterface;

class VariantCondition implements ConditionInterface
{
    const NAME = 'variant';

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
