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

namespace SwagLiveShopping\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ProductProviderInterface
     */
    private $coreProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var IdentifierSelector
     */
    private $identifierSelector;

    /**
     * @param ProductProviderInterface $coreProvider
     * @param Connection               $connection
     * @param IdentifierSelector       $identifierSelector
     */
    public function __construct(
        ProductProviderInterface $coreProvider,
        Connection $connection,
        IdentifierSelector $identifierSelector
    ) {
        $this->coreProvider = $coreProvider;
        $this->connection = $connection;
        $this->identifierSelector = $identifierSelector;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop, $numbers)
    {
        $products = $this->coreProvider->get($shop, $numbers);
        $shopId = $shop->getId();
        $customerGroups = $this->identifierSelector->getCustomerGroupKeys();
        $query = $this->connection->createQueryBuilder();

        $ids = array_map(function (Product $product) {
            return $product->getId();
        }, $products);

        $query->select([
            'al.article_id as array_key',
            'al.valid_from',
            'al.valid_to',
            'al.article_id',
            'GROUP_CONCAT(DISTINCT ccg.groupkey) AS customer_group_keys',
        ]);

        $query->from('s_articles_lives', 'al');
        $query->innerJoin('al', 's_articles_live_customer_groups', 'alcg', 'alcg.live_shopping_id = al.id');
        $query->innerJoin('alcg', 's_core_customergroups', 'ccg', 'ccg.id = alcg.customer_group_id');
        $query->innerJoin('al', 's_articles_live_shoprelations', 'als', 'als.live_shopping_id = al.id');
        $query->andWhere('al.article_id IN (:ids)');
        $query->andWhere('als.shop_id = :shopId');
        $query->groupBy('al.article_id');

        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->setParameter(':shopId', $shopId);
        $liveShoppings = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        /** @var Product $product */
        foreach ($products as $product) {
            if (!isset($liveShoppings[$product->getId()])) {
                continue;
            }
            $data = $liveShoppings[$product->getId()];

            $validFrom = new \DateTime($data['valid_from']);
            $validTo = new \DateTime($data['valid_to']);

            $data['valid_from'] = $validFrom->format('Y-m-d H:i:s');
            $data['valid_to'] = $validTo->format('Y-m-d H:i:s');

            $liveShoppingCustomerGroups = explode(',', $data['customer_group_keys']);
            foreach ($customerGroups as $customerGroup) {
                $data[$customerGroup] = in_array($customerGroup, $liveShoppingCustomerGroups);
            }
            $attribute = new Attribute($data);

            $product->addAttribute('live_shopping', $attribute);
        }

        return $products;
    }
}
