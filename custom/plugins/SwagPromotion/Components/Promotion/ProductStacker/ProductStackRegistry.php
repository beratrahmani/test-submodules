<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagPromotion\Components\Promotion\ProductStacker;

/**
 * ProductStackRegistry knows all available product stackers
 */
class ProductStackRegistry
{
    /**
     * @var ProductStacker[]
     */
    protected $stacker;

    /**
     * @param \IteratorAggregate $stacker
     */
    public function __construct($stacker = [])
    {
        $this->stacker = $stacker;
    }

    /**
     * @param ProductStacker $instance
     */
    public function addStacker(ProductStacker $instance)
    {
        $this->stacker[] = $instance;
    }

    /**
     * @param string $name
     *
     * @throws StackerNotFoundException
     *
     * @return ProductStacker
     */
    public function getStacker($name)
    {
        foreach ($this->stacker as $stacker) {
            if ($stacker->supports($name)) {
                return $stacker;
            }
        }

        throw new StackerNotFoundException(
            sprintf(
                'The stacker for %s in not registered.',
                $name
            )
        );
    }
}
