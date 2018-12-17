<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Shop\Framework\CategoryNode;
use Shopware\B2B\Shop\Framework\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchChildren(int $parentId): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $rawResult = $queryBuilder
            ->select('*')
            ->addSelect('(SELECT COUNT(*) FROM s_categories innerCategory WHERE innerCategory.parent = category.id LIMIT 1) AS has_children')
            ->from('s_categories', 'category')
            ->where('category.parent = :parentId')
            ->orderBy('position')
            ->setParameter('parentId', $parentId)
            ->execute()
            ->fetchAll();

        $nodes = [];
        foreach ($rawResult as $row) {
            $nodes[] = new CategoryNode((int) $row['id'], $row['description'], (bool) $row['has_children']);
        }

        return $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProduct(int $categoryId, string $productNumber): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $rawResult = $queryBuilder
            ->select('COUNT(*)')
            ->from('s_articles_categories_ro', 'categoryAssignment')
            ->innerJoin('categoryAssignment', 's_articles', 'product', 'categoryAssignment.articleID = product.id')
            ->innerJoin('product', 's_articles_details', 'detail', 'product.id = detail.articleID')
            ->where('detail.ordernumber = :orderNumber AND categoryAssignment.categoryID = :categoryId')
            ->setParameter('orderNumber', $productNumber)
            ->setParameter('categoryId', $categoryId)
            ->execute()
            ->fetchColumn();

        return (bool) $rawResult;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNodeById(int $categoryId): CategoryNode
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $row = $queryBuilder
            ->select('*')
            ->addSelect('(SELECT COUNT(*) FROM s_categories innerCategory WHERE innerCategory.parent = category.id LIMIT 1) AS has_children')
            ->from('s_categories', 'category')
            ->where('category.id = :categoryId')
            ->orderBy('position')
            ->setParameter('categoryId', $categoryId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return new CategoryNode((int) $row['id'], $row['description'], (bool) $row['has_children']);
    }
}
