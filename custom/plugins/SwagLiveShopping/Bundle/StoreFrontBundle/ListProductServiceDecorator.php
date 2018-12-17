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

namespace SwagLiveShopping\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagLiveShopping\Components\LiveShoppingInterface;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;

class ListProductServiceDecorator implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $coreService;

    /**
     * @var LiveShoppingInterface
     */
    private $liveShopping;

    /**
     * @param ListProductServiceInterface $coreService
     * @param LiveShoppingInterface       $liveShopping
     */
    public function __construct(
        ListProductServiceInterface $coreService,
        LiveShoppingInterface $liveShopping
    ) {
        $this->coreService = $coreService;
        $this->liveShopping = $liveShopping;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ProductContextInterface $context)
    {
        /** @var ListProduct[] $products */
        $products = $this->coreService->getList($numbers, $context);

        $mapping = $this->liveShopping->haveVariantsLiveShopping($products, $context);

        foreach ($products as $product) {
            $attribute = new Attribute([
                'has_live_shopping' => array_key_exists($product->getNumber(), $mapping),
                'live_shopping' => [],
            ]);

            $product->addAttribute('live_shopping', $attribute);

            list($data, $liveShopping) = $this->liveShopping->getLiveShoppingByNumber($product->getNumber());

            if (!$liveShopping instanceof LiveShoppingModel) {
                continue;
            }

            $product->setAllowBuyInListing(false);

            $attribute->set('live_shopping', $data);

            $product->getListingPrice()->setCalculatedPrice($liveShopping->getCurrentPrice());

            /** @var LiveShoppingModel $liveShopping */
            if ($liveShopping->getLimited()) {
                $unit = $product->getUnit();

                if ($liveShopping->getPurchase() > 0) {
                    $unit->setMaxPurchase($liveShopping->getPurchase());
                }

                if ($unit->getMaxPurchase() > $liveShopping->getQuantity()) {
                    $unit->setMaxPurchase($liveShopping->getQuantity());
                }
            }
        }

        return $products;
    }
}
