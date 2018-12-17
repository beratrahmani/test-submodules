<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;

class CustomerStreamRepositoryDecorator implements CustomerStreamRepositoryInterface
{
    /**
     * @var CustomerStreamRepositoryInterface
     */
    private $customerStreamRepository;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CustomerStreamRepositoryInterface $customerStreamRepository
     * @param Connection $connection
     */
    public function __construct(
        CustomerStreamRepositoryInterface $customerStreamRepository,
        Connection $connection
    ) {
        $this->customerStreamRepository = $customerStreamRepository;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCustomerStreamEmotions($categoryId)
    {
        return $this->customerStreamRepository->hasCustomerStreamEmotions($categoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchBackendListing(array $ids)
    {
        return $this->customerStreamRepository->fetchBackendListing($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamsCustomerCount(array $streamIds)
    {
        return $this->customerStreamRepository->fetchStreamsCustomerCount($streamIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotIndexedCount()
    {
        $now = new \DateTime();

        return (int) $this->connection->fetchColumn(
            '
            SELECT COUNT(customers.id)
            FROM s_user customers
            LEFT JOIN b2b_debtor_contact b2b_contact
            ON customers.email = b2b_contact.email
            WHERE b2b_contact.id IS NULL
            AND customers.id NOT IN (
                SELECT search_index.id 
                FROM s_customer_search_index search_index
                WHERE search_index.index_time >= :indexTime
            )',
            [':indexTime' => $now->format('Y-m-d')]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerCount()
    {
        return (int) $this->connection->fetchColumn('
            SELECT COUNT(customers.id) 
            FROM s_user customers
            LEFT JOIN b2b_debtor_contact b2b_contact
            ON customers.email = b2b_contact.email
            WHERE b2b_contact.id IS NULL
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchSearchIndexIds($offset, $full = false)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('DISTINCT user.id');
        $query->from('s_user', 'user');

        $query->leftJoin('user', 'b2b_debtor_contact', 'b2bContact', 'user.email = b2bContact.email')
            ->andWhere('b2bContact.id IS NULL');

        if ($full) {
            $query->andWhere('user.id > :lastId');
            $query->setParameter(':lastId', $offset);
        } else {
            $query->andWhere(
                'user.id NOT IN (
                SELECT search_index.id 
                FROM s_customer_search_index search_index
                WHERE search_index.index_time >= :indexTime)'
            );
            $now = new DateTime();
            $query->setParameter(':indexTime', $now->format('Y-m-d'));
        }

        $query->setMaxResults(250);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCustomerAmount($streamId = null, $month = 12)
    {
        return $this->customerStreamRepository->fetchCustomerAmount($streamId, $month);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAmountPerStreamChart()
    {
        return $this->customerStreamRepository->fetchAmountPerStreamChart();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastFillIndexDate()
    {
        return $this->customerStreamRepository->getLastFillIndexDate();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamsForCustomers(array $customerIds)
    {
        return $this->customerStreamRepository->fetchStreamsForCustomers($customerIds);
    }
}
