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

namespace SwagPromotion\Components\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class ProductService implements ProductServiceInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ListProductService
     */
    private $productService;

    /**
     * @var LegacyStructConverter
     */
    private $converter;

    /**
     * ArticleService constructor.
     *
     * @param Connection                  $databaseConnection
     * @param ContextServiceInterface     $contextService
     * @param ListProductServiceInterface $productService
     * @param LegacyStructConverter       $converter
     */
    public function __construct(
        Connection $databaseConnection,
        ContextServiceInterface $contextService,
        ListProductServiceInterface $productService,
        LegacyStructConverter $converter
    ) {
        $this->databaseConnection = $databaseConnection;
        $this->contextService = $contextService;
        $this->productService = $productService;
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFreeGoods(array $articleIds, $promotionId)
    {
        $query = $this->databaseConnection->createQueryBuilder();
        $query->select('ordernumber')
            ->from('s_articles_details', 'variant')
            ->where('variant.kind = 1')
            ->andWhere('variant.articleID IN (:ids)')
            ->setParameter(':ids', $articleIds, Connection::PARAM_INT_ARRAY);

        $numbers = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $context = $this->contextService->getShopContext();
        $products = $this->productService->getList($numbers, $context);
        $articles = $this->converter->convertListProductStructList($products);

        $returnValue = [];
        foreach ($articles as $article) {
            $article['promotionId'] = $promotionId;
            $article['variants'] = $this->getVariantData($article['articleID']);
            $returnValue[] = $article;
        }

        return $returnValue;
    }

    /**
     * @param int $articleId
     *
     * @return array
     */
    private function getVariantData($articleId)
    {
        $sArticles = Shopware()->Modules()->Articles();

        $query = $this->databaseConnection->createQueryBuilder();
        $query->select(['detail.ordernumber'])
            ->from('s_articles_details', 'detail')
            ->where('detail.articleID = :id')
            ->andWhere('detail.instock > 0')
            ->setParameter(':id', $articleId);

        $orderNumbers = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $variants = [];
        foreach ($orderNumbers as $orderNumber) {
            $additionalText = $sArticles->sGetArticleNameByOrderNumber($orderNumber);
            $variants[] = [
                'orderNumber' => $orderNumber,
                'additionalText' => $additionalText,
            ];
        }

        return $variants;
    }
}
