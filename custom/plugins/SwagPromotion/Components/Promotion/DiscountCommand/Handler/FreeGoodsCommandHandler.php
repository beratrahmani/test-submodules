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

namespace SwagPromotion\Components\Promotion\DiscountCommand\Handler;

use SwagPromotion\Components\Promotion\DiscountCommand\Command\Command;
use SwagPromotion\Components\Promotion\DiscountCommand\Command\FreeGoodsCommand;
use SwagPromotion\Components\Promotion\Tax;
use SwagPromotion\Components\Services\BasketService;
use SwagPromotion\Struct\Promotion;

class FreeGoodsCommandHandler implements CommandHandler
{
    /**
     * @var Tax
     */
    private $taxCalculator;

    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * @param Tax           $taxCalculator
     * @param BasketService $basketService
     */
    public function __construct(Tax $taxCalculator, BasketService $basketService)
    {
        $this->taxCalculator = $taxCalculator;
        $this->basketService = $basketService;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Command $command, Promotion $promotion, array $basket)
    {
        /** @var FreeGoodsCommand $command */
        $amount = $command->getAmount();

        // Do not insert discounts with amount 0
        if (!$amount) {
            return false;
        }

        $result = $this->taxCalculator->calculate(-$amount, $basket);

        $this->basketService->insertDiscount($promotion, -$amount, $result['net'], $result['taxRate']);

        return true;
    }

    /**
     * {@inheritdoc}
     **/
    public function supports($name)
    {
        return $name === FreeGoodsCommand::FREE_GOODS_COMMAND_NAME;
    }
}
