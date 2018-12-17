<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\SearchBundleDBAL\KeywordFinderInterface;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\Keyword;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\KeywordFinder;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexerInterface;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\TermHelperInterface;
use Shopware\Bundle\SearchBundleDBAL\SearchTermQueryBuilderInterface;
use Shopware_Components_Config;

class OrderNumberSearchTermQueryBuilder implements SearchTermQueryBuilderInterface
{
    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var KeywordFinder
     */
    private $keywordFinder;

    /**
     * @var TermHelperInterface
     */
    private $termHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param Shopware_Components_Config $config
     * @param Connection $connection
     * @param KeywordFinderInterface $keywordFinder
     * @param SearchIndexerInterface $searchIndexer
     * @param TermHelperInterface $termHelper
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        Shopware_Components_Config $config,
        Connection $connection,
        KeywordFinderInterface $keywordFinder,
        SearchIndexerInterface $searchIndexer,
        TermHelperInterface $termHelper,
        AuthenticationService $authenticationService
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->keywordFinder = $keywordFinder;
        $this->termHelper = $termHelper;
        $this->authenticationService = $authenticationService;

        $searchIndexer->validate();
    }

    /**
     * Required table fields:
     *  - product_id : id of the product, used as join
     *
     * @param $term
     *
     * @return QueryBuilder|null
     */
    public function buildQuery($term)
    {
        $keywords = $this->keywordFinder->getKeywordsOfTerm($term);
        $tables = $this->getSearchTables();

        if (!$this->authenticationService->isB2b() && (empty($keywords) || empty($tables))) {
            return null;
        }

        $query = $this->buildQueryFromKeywords($keywords, $tables, $term);

        $this->addToleranceCondition($query);
        $query->select(
            [
                'a.id as product_id',
                '(' . $this->getRelevanceSelection() . ') as ranking',
            ]
        );

        $enableAndSearchLogic = $this->config->get('enableAndSearchLogic', false);
        if ($enableAndSearchLogic) {
            $this->addAndSearchLogic($query, $term);
        }

        return $query;
    }

    /**
     * @return string
     * @internal
     */
    protected function getRelevanceSelection(): string
    {
        return 'sr.relevance
        + IF(a.topseller = 1, 50, 0)
        + IF(a.datum >= DATE_SUB(NOW(),INTERVAL 7 DAY), 25, 0)';
    }

    /**
     * Generates a single query builder from the provided keywords array.
     *
     * @param Keyword[] $keywords
     * @param $tables
     * @param mixed $term
     *
     * @return QueryBuilder
     * @internal
     */
    protected function buildQueryFromKeywords($keywords, $tables, $term): QueryBuilder
    {
        $keywordSelection = [];
        foreach ($keywords as $match) {
            $keywordSelection[] = 'SELECT ' . $match->getRelevance() . ' as relevance, ' . $this->connection->quote($match->getTerm()) . ' as term, ' . $match->getId() . ' as keywordID';
        }
        $keywordSelection = implode("\n             UNION ALL ", $keywordSelection);

        $tablesSql = [];

        if (!empty($keywordSelection)) {
            foreach ($tables as $table) {
                $query = $this->connection->createQueryBuilder();
                $alias = 'st' . $table['tableID'];

                $query->select(['MAX(sf.relevance * sm.relevance) as relevance', 'sm.keywordID', 'term']);
                $query->from('(' . $keywordSelection . ')', 'sm');
                $query->innerJoin('sm', 's_search_index', 'si', 'sm.keywordID = si.keywordID');
                $query->innerJoin('si', 's_search_fields', 'sf', 'si.fieldID = sf.id AND sf.relevance != 0 AND sf.tableID = ' . $table['tableID']);

                $query->groupBy('articleID')
                    ->addGroupBy('sm.term')
                    ->addGroupBy('sf.id');

                if (!empty($table['referenz_table'])) {
                    $query->addSelect($alias . '.articleID as articleID');
                    $query->innerJoin('si', $table['referenz_table'], $alias, 'si.elementID = ' . $alias . '.' . $table['foreign_key']);
                } elseif (!empty($table['foreign_key'])) {
                    $query->addSelect($alias . '.id as articleID');
                    $query->innerJoin('si', 's_articles', $alias, 'si.elementID = ' . $alias . '.' . $table['foreign_key']);
                } else {
                    $query->addSelect('si.elementID as articleID');
                }

                $tablesSql[] = $query->getSQL();
            }
        }

        // Add the b2b suite custom order number to the search query
        if ($this->authenticationService->isB2b()) {
            $tablesSql[] = $this->getOrderNumberSql($term);
        }

        $tablesSql = "\n" . implode("\n     UNION ALL\n", $tablesSql);

        $subQuery = $this->connection->createQueryBuilder();
        $subQuery->select(['srd.articleID', 'SUM(srd.relevance) as relevance', 'COUNT(DISTINCT term) as termCount']);
        $subQuery->from('(' . $tablesSql . ')', 'srd')
            ->groupBy('srd.articleID')
            ->orderBy('relevance', 'DESC')
            ->setMaxResults(5000);

        $query = $this->connection->createQueryBuilder();
        $query->from('(' . $subQuery->getSQL() . ')', 'sr')
            ->innerJoin('sr', 's_articles', 'a', 'a.id = sr.articleID');

        return $query;
    }

    /**
     * @param string $term
     * @return string
     * @internal
     */
    protected function getOrderNumberSql(string $term): string
    {
        $context = $this->authenticationService->getIdentity()->getOwnershipContext();
        $query = $this->connection->createQueryBuilder();
        $query->select(['10000 as relevance', 'sm.id', 'sm.custom_ordernumber as term', 'sad.articleID'])
            ->from('b2b_order_number', 'sm')
            ->innerJoin('sm', 's_articles_details', 'sad', 'sm.product_details_id = sad.id')
            ->where('sm.custom_ordernumber like ' . $this->connection->quote($term . '%'))
            ->andWhere('context_owner_id = ' . $context->contextOwnerId);

        return $query->getSQL();
    }

    /**
     * Calculates the search tolerance and adds an where condition
     * to the query.
     *
     * @param QueryBuilder $query
     * @internal
     */
    protected function addToleranceCondition(QueryBuilder $query)
    {
        $distance = $this->config->get('fuzzySearchMinDistancenTop', 20);
        $query->select('MAX(' . $this->getRelevanceSelection() . ") / 100 * $distance");

        //calculates the tolerance limit
        if ($distance) {
            $query->andWhere('(' . $this->getRelevanceSelection() . ') > (' . $query->getSQL() . ')');
        }
    }

    /**
     * Get all tables and columns that might be involved in this search request as an array
     *
     * @return array
     * @internal
     */
    protected function getSearchTables(): array
    {
        return $this->connection->fetchAll("
            SELECT STRAIGHT_JOIN
                st.id AS tableID,
                st.table,
                st.where,
                st.referenz_table, st.foreign_key,
                GROUP_CONCAT(sf.id SEPARATOR ', ') AS fieldIDs,
                GROUP_CONCAT(sf.field SEPARATOR ', ') AS `fields`
            FROM s_search_fields sf FORCE INDEX (tableID)
                INNER JOIN s_search_tables st
                    ON st.id = sf.tableID
                    AND sf.relevance != 0
            GROUP BY sf.tableID
       ");
    }

    /**
     * checks if the given result set matches all search terms
     *
     * @param QueryBuilder $query
     * @param string       $term
     * @internal
     */
    protected function addAndSearchLogic($query, $term)
    {
        $searchTerms = $this->termHelper->splitTerm($term);
        $query->andWhere('termCount >= ' . count($searchTerms));
    }
}
