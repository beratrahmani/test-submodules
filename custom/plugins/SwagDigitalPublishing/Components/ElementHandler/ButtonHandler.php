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

namespace SwagDigitalPublishing\Components\ElementHandler;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class ButtonHandler implements PopulateElementHandlerInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @param ListProductServiceInterface $listProductService
     * @param LegacyStructConverter       $legacyStructConverter
     */
    public function __construct(
        ListProductServiceInterface $listProductService,
        LegacyStructConverter $legacyStructConverter
    ) {
        $this->listProductService = $listProductService;
        $this->legacyStructConverter = $legacyStructConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle(array $element)
    {
        return $element['name'] === 'button';
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $element, ShopContextInterface $context)
    {
        if (!empty($element['link']) && !strpos($element['link'], 'http')) {
            $product = $this->listProductService->getList([$element['link']], $context);
            $product = array_shift($product);

            if ($product) {
                $element['product'] = $this->legacyStructConverter->convertListProductStruct($product);
            }
        }

        return $element;
    }
}
