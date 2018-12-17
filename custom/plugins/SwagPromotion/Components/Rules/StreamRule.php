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

namespace SwagPromotion\Components\Rules;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use SwagPromotion\Components\Services\StreamService;

class StreamRule implements Rule
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var string[]
     */
    private $streamIds;

    /**
     * @var BaseProduct[]
     */
    private $productsInStream;

    /**
     * @var array
     */
    private $mainProductsOrderNumbers;

    /**
     * @param array    $products
     * @param string[] $streamIds
     */
    public function __construct(array $products, array $streamIds)
    {
        $this->productsInStream = [];
        $this->products = $products;
        $this->streamIds = $streamIds;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->mainProductsOrderNumbers = $this->getMainProductNumbers($this->products);

        /** @var StreamService $streamService */
        $streamService = Shopware()->Container()->get('swag_promotion.stream_service');

        foreach ($this->streamIds as $streamId) {
            $streamResult = $streamService->getProductNumbersFromStreamByStreamId(
                $streamId,
                array_merge(
                    array_column($this->products, 'ordernumber'),
                    array_values($this->mainProductsOrderNumbers)
                )
            );

            foreach ($streamResult as $number => $product) {
                if (!array_key_exists($number, $this->productsInStream)) {
                    $this->productsInStream[$number] = $product;
                }
            }
        }

        return $this->isProductInStream();
    }

    /**
     * @return bool
     */
    private function isProductInStream()
    {
        foreach ($this->products as $product) {
            if ($this->productsInStream[$product['ordernumber']]
                || $this->productsInStream[$this->mainProductsOrderNumbers[$product['product::id']]]
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ListProduct[] $products
     *
     * @return array
     */
    private function getMainProductNumbers(array $products)
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product['product::id'];
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();
        $result = $queryBuilder->select(['articleID', 'ordernumber'])
            ->from('s_articles_details')
            ->where('kind = 1')
            ->andWhere('articleID IN (:ids)')
            ->setParameter(':ids', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }
}
