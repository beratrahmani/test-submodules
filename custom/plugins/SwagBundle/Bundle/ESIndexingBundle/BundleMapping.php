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

namespace SwagBundle\Bundle\ESIndexingBundle;

use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class BundleMapping implements MappingInterface
{
    /**
     * @var MappingInterface
     */
    private $coreMapping;

    /**
     * @var IdentifierSelector
     */
    private $identifierSelector;

    /**
     * @param MappingInterface   $coreMapping
     * @param IdentifierSelector $identifierSelector
     */
    public function __construct(MappingInterface $coreMapping, IdentifierSelector $identifierSelector)
    {
        $this->coreMapping = $coreMapping;
        $this->identifierSelector = $identifierSelector;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->coreMapping->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop)
    {
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();

        $properties = [
            'valid_from' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            'valid_to' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ];

        $grouped = [];
        foreach ($customerGroups as $customerGroup) {
            $grouped[$customerGroup]['properties'] = $properties;
        }

        $mapping = $this->coreMapping->get($shop);

        $mapping['properties']['attributes']['properties']['swag_bundle'] = [
            'properties' => $grouped,
        ];

        return $mapping;
    }
}
