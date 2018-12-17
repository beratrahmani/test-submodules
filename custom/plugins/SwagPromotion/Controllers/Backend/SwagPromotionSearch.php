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
use SwagPromotion\Components\MetaData\ValueSearch;

class Shopware_Controllers_Backend_SwagPromotionSearch extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * provides values for the rules
     *
     * @throws \RuntimeException
     */
    public function searchAction()
    {
        $valueSearch = new ValueSearch(
            $this->get('models'),
            $this->get('shopware_storefront.context_service'),
            $this->get('shopware_storefront.additional_text_service'),
            $this->get('snippets')
        );

        $limit = (int) $this->Request()->getParam('limit', 20);
        $page = (int) $this->Request()->getParam('page', 1);
        $field = $this->Request()->getParam('field');
        $searchTerm = $this->Request()->getParam('searchTerm');

        $offset = ($page - 1) * $limit;

        if (!$field) {
            throw new \RuntimeException('No field provided');
        }

        $result = $valueSearch->get($field, $offset, $limit, $searchTerm);
        $this->View()->assign($result);
    }
}
