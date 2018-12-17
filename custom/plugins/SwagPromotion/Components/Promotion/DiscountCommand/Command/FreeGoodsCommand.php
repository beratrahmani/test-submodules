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

namespace SwagPromotion\Components\Promotion\DiscountCommand\Command;

/**
 * Class FreeGoodsCommand instructs to add an absolute discount of $amount with the value of the free good article price
 */
class FreeGoodsCommand implements Command
{
    /** @deprecated since version 2.3.1 remove in version 3.0.0 */
    const FREE_GOODS_COMMNAD_NAME = self::FREE_GOODS_COMMAND_NAME;

    const FREE_GOODS_COMMAND_NAME = 'freeGoodsCommand';

    /**
     * @var float
     */
    private $amount;

    /**
     * FreeGoodsCommand constructor.
     *
     * @param float $amount
     */
    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::FREE_GOODS_COMMAND_NAME;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === $this->getName();
    }
}
