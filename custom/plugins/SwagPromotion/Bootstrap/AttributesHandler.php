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

namespace SwagPromotion\Bootstrap;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;

class AttributesHandler
{
    const BASKET_ATTRIBUTE_TABLE = 's_order_basket_attributes';

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CrudService  $crudService
     * @param ModelManager $modelManager
     */
    public function __construct(CrudService $crudService, ModelManager $modelManager)
    {
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    public function installAttributes()
    {
        $this->crudService->update(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_promotion_id',
            TypeMapping::TYPE_INTEGER
        );

        $this->crudService->update(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_is_free_good_by_promotion_id',
            TypeMapping::TYPE_STRING
        );

        $this->crudService->update(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_is_shipping_free_promotion',
            TypeMapping::TYPE_BOOLEAN
        );

        $this->generateAttributeModels();
    }

    public function uninstallAttributes()
    {
        $this->crudService->delete(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_promotion_id'
        );

        $this->crudService->delete(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_is_free_good_by_promotion_id'
        );

        $this->crudService->delete(
            self::BASKET_ATTRIBUTE_TABLE,
            'swag_is_shipping_free_promotion'
        );

        $this->generateAttributeModels();
    }

    private function generateAttributeModels()
    {
        $this->modelManager->generateAttributeModels([self::BASKET_ATTRIBUTE_TABLE]);
    }
}
