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

namespace SwagTicketSystem\Models\Ticket;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Form\Field;

/**
 * Repository for the Ticket model (SwagTicketSystem\Models\Ticket\Support).
 * <br>
 * The Ticket repository is responsible to load all Ticket data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * @param int $statusId
     * @param int $employeeId
     *
     * @return Query
     */
    public function getWidgetQuery($statusId, $employeeId, $order = [])
    {
        $builder = $this->getListQueryBuilder(null, $statusId, null, $order);
        /*
         * Filter all the tickets which are closed already.
         * Needed for the ticket widget.
         */

        $builder->andWhere('statusAlias.id != 4');
        $builder->andWhere(
            $builder->expr()->orX(
                $builder->expr()->eq('ticket.employeeId', $employeeId),
                $builder->expr()->eq('ticket.employeeId', '0')
            )
        );

        return $builder->getQuery();
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the tickets for the backend list
     *
     * @param int        $employeeId
     * @param int        $statusId
     * @param array|null $filter
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query
     */
    public function getListQuery($employeeId, $statusId, array $filter = null, array $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($employeeId, $statusId, $filter, $order);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int        $employeeId
     * @param int        $statusId
     * @param array|null $filter
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder($employeeId, $statusId, array $filter = null, array $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'ticket.id',
                'ticket.uniqueId',
                'customer.id as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.additional as additional',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode',
                'ticket.shopId as shopId',
                'ticket.isRead as isRead',
                'CONCAT(CONCAT(billing.firstname, \' \'), billing.lastname) as contact',
                'billing.company as company',
            ]
        )
            ->from(Support::class, 'ticket')
            ->leftJoin('ticket.customer', 'customer')
            ->leftJoin('customer.defaultBillingAddress', 'billing')
            ->leftJoin('ticket.status', 'statusAlias')
            ->leftJoin('ticket.type', 'type');

        if (!empty($order) && $order[0]['property'] === 'id' && !empty($order[0]['direction'])) {
            $order[0]['property'] = 'ticket.id';
        }

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($employeeId)) {
            $builder->where('ticket.employeeId = ?1');
            $builder->setParameter(1, $employeeId);
        }

        if (!empty($statusId)) {
            $builder->andWhere('statusAlias.id = :status');
            $builder->setParameter('status', $statusId);
        }

        if (!empty($filter) && isset($filter[0]['property']) && $filter[0]['property'] === 'free') {
            $builder->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->like('customer.email', ':search'),
                    $builder->expr()->like('billing.firstName', ':search'),
                    $builder->expr()->like('billing.lastName', ':search'),
                    $builder->expr()->like('billing.company', ':search'),
                    $builder->expr()->like('type.name', ':search'),
                    $builder->expr()->like('statusAlias.description', ':search'),
                    $builder->expr()->like('ticket.subject', ':search'),
                    $builder->expr()->like('ticket.message', ':search'),
                    $builder->expr()->like('ticket.id', ':search')
                )
            );
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the tickets for the frontend list
     *
     * @param int        $customerId
     * @param int|null   $statusId
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query
     */
    public function getFrontendTicketListQuery(
        $customerId,
        $statusId = null,
        array $order = null,
        $offset = null,
        $limit = null
    ) {
        $builder = $this->getFrontendTicketListQueryBuilder($customerId, $statusId, $order);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFrontendTicketListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int        $customerId
     * @param int|null   $statusId
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getFrontendTicketListQueryBuilder($customerId, $statusId = null, array $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'ticket.id',
                'ticket.uniqueId',
                'customer.id as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode',
                'CONCAT(CONCAT(billing.firstname, \' \'), billing.lastname) as contact',
                'billing.company as company',
            ]
        )
            ->from(Support::class, 'ticket')
            ->leftJoin('ticket.customer', 'customer')
            ->leftJoin('customer.defaultBillingAddress', 'billing')
            ->leftJoin('ticket.status', 'statusAlias')
            ->leftJoin('ticket.type', 'type');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($customerId)) {
            $builder->where('customer.id = ?1');
            $builder->setParameter(1, $customerId);
        }

        if ($statusId !== null) {
            $builder->andWhere('statusAlias.id = :status');
            $builder->setParameter('status', $statusId);
        }

        return $builder;
    }

    /**
     * @param int        $customerId
     * @param int|null   $statusId
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     * @param array      $filter
     *
     * @return Query
     */
    public function getCustomerTicketListQuery(
        $customerId,
        $statusId = null,
        $order = null,
        $offset = null,
        $limit = null,
        array $filter
    ) {
        $builder = $this->getFrontendTicketListQueryBuilder($customerId, $statusId, $order);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }
        if (!empty($filter) && isset($filter[0]['property']) && $filter[0]['property'] === 'free') {
            $builder->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->like('billing.firstname', ':search'),
                    $builder->expr()->like('billing.lastname', ':search'),
                    $builder->expr()->like('billing.company', ':search'),
                    $builder->expr()->like('type.name', ':search'),
                    $builder->expr()->like('statusAlias.description', ':search'),
                    $builder->expr()->like('ticket.subject', ':search'),
                    $builder->expr()->like('ticket.message', ':search')
                )
            );
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        return $builder->getQuery();
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a single ticket by its id
     *
     * @param int $ticketId
     *
     * @return Query
     */
    public function getTicketDetailQueryById($ticketId)
    {
        $builder = $this->getTicketDetailQueryBuilderById($ticketId);

        return $builder->getQuery();
    }

    /**
     * Returns an instance of the Doctrine\ORM\Query object which selects a single ticket by its unique id
     *
     * @param string $ticketUniqueId
     *
     * @return Query
     */
    public function getTicketDetailQueryByUniqueId($ticketUniqueId)
    {
        $builder = $this->getTicketDetailQueryBuilderByUniqueId($ticketUniqueId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $ticketUniqueId
     *
     * @return QueryBuilder
     */
    public function getTicketDetailQueryBuilderByUniqueId($ticketUniqueId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'ticket.id',
                'ticket.uniqueId',
                'ticket.userId as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.closed as closed',
                'statusAlias.responsible as responsible',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode',
                'ticket.additional as additional',
            ]
        )
            ->from(Support::class, 'ticket')
            ->leftJoin('ticket.status', 'statusAlias')
            ->leftJoin('ticket.type', 'type')
            ->where('ticket.uniqueId = :ticketUniqueId')
            ->setParameter('ticketUniqueId', $ticketUniqueId)
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder;
    }

    /**
     * Gets the actual unique id and returns the builder of the unique id query builder.
     *
     * @param int $ticketId
     *
     * @return QueryBuilder
     */
    public function getTicketDetailQueryBuilderById($ticketId)
    {
        $dbalQueryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $uniqueId = $dbalQueryBuilder->select('uniqueID')
            ->from('s_ticket_support', 'ticket')
            ->where('id = :ticketId')
            ->setParameter(':ticketId', $ticketId)
            ->execute()
            ->fetchColumn();

        return $this->getTicketDetailQueryBuilderByUniqueId($uniqueId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the ticket history
     *
     * @param int      $ticketId
     * @param int|null $limit
     *
     * @return Query
     */
    public function getTicketHistoryQuery($ticketId, $limit = null)
    {
        $builder = $this->getTicketHistoryQueryBuilder($ticketId, $limit);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketHistoryQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $ticketId
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    public function getTicketHistoryQueryBuilder($ticketId, $limit = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(
            [
                'history.id as id',
                'ticket.id as ticketId',
                'ticket.email as email',
                'history.swUser as swUser',
                'history.subject as subject',
                'history.message as message',
                'history.receipt as receipt',
                'history.supportType as supportType',
                'history.receiver as receiver',
                'history.direction as direction',
                'history.statusId as statusId',
            ]
        )
            ->from(Support::class, 'ticket')
            ->leftJoin('ticket.history', 'history')
            ->where('history.ticketId = :ticketId')
            ->setParameter('ticketId', $ticketId)
            ->orderBy('history.receipt', 'DESC');

        if ($limit) {
            $builder->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the status list for the backend combo box
     *
     * @return Query
     */
    public function getStatusListQuery()
    {
        $builder = $this->getStatusListQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getStatusListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getStatusListQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['status'])->from(Status::class, 'status');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the mail list for the backend module
     *
     * @param int|null $locale
     * @param bool     $onlyCustomSubmissions
     * @param bool     $onlyDefaultSubmission
     *
     * @return Query
     */
    public function getMailListQuery($locale = null, $onlyCustomSubmissions = false, $onlyDefaultSubmission = false)
    {
        $builder = $this->getMailListQueryBuilder($locale, $onlyCustomSubmissions, $onlyDefaultSubmission);

        return $builder->getQuery();
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the right submissions for
     * sending the submission over the system
     *
     * @param string   $name
     * @param int|null $shopId
     *
     * @return Query
     */
    public function getSystemSubmissionQuery($name, $shopId = null)
    {
        $builder = $this->getSystemSubmissionQueryBuilder($name, $shopId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSystemSubmissionQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $name
     * @param int    $shopId
     *
     * @return QueryBuilder
     */
    public function getSystemSubmissionQueryBuilder($name, $shopId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['mail'])
            ->from(Mail::class, 'mail')
            ->leftJoin('mail.shop', 'shop')
            ->where('mail.systemDependent = 1')
            ->andWhere('mail.name = :name')
            ->setParameter('name', $name)
            ->setFirstResult(0)
            ->setMaxResults(1);

        if (!empty($shopId)) {
            $builder->andWhere('shop.id = :shopId')
                ->setParameter('shopId', $shopId);
        } else {
            $builder->andWhere('shop.id = 1');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the mail list for the backend module
     *
     * @param array      $filter
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query
     */
    public function getTicketTypeListQuery(array $filter, array $order = null, $offset, $limit)
    {
        $builder = $this->getTicketTypeListQueryBuilder($filter, $order);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketTypeListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array      $filter
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getTicketTypeListQueryBuilder(array $filter, array $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'type.id as id',
                'type.name as name',
                'type.gridColor as gridColor',
            ]
        )
            ->from(Type::class, 'type');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($filter) && isset($filter[0]['property']) && $filter[0]['property'] === 'free') {
            $builder->andWhere('type.name LIKE :search')
                ->orWhere('type.gridColor LIKE :search')
                ->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the upload form field for current answer
     *
     * @param int $ticketAnswerId
     *
     * @return Query
     */
    public function getTicketUploadValueQuery($ticketAnswerId)
    {
        $builder = $this->getTicketUploadValueQueryBuilder($ticketAnswerId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketUploadValueQuery" function.
     *
     * @param int $ticketAnswerId
     *
     * @return QueryBuilder
     */
    public function getTicketUploadValueQueryBuilder($ticketAnswerId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['field.value'])
            ->from(History::class, 'history')
            ->leftJoin(Support::class, 'support', Join::WITH, 'support.id = history.ticketId')
            ->leftJoin(Field::class, 'field', Join::WITH, 'field.formId = support.formId')
            ->where('history.id = :ticketHistory')
            ->andWhere("field.typ = 'upload'")
            ->setParameter('ticketHistory', $ticketAnswerId);

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getMailListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int|null $locale
     * @param bool     $onlyCustomSubmissions
     * @param bool     $onlyDefaultSubmission
     *
     * @return QueryBuilder
     */
    private function getMailListQueryBuilder($locale, $onlyCustomSubmissions, $onlyDefaultSubmission)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'mail.id as id',
                'mail.name as name',
                'mail.description as description',
                'mail.fromMail as fromMail',
                'mail.fromName as fromName',
                'mail.subject as subject',
                'mail.content as content',
                'mail.contentHTML as contentHTML',
                'mail.isHTML as isHTML',
                'mail.attachment as attachment',
                'mail.systemDependent as systemDependent',
                'mail.isoCode as isoCode',
                'shop.id as shopId',
                'shop.name as shopName',
            ]
        )
            ->from(Mail::class, 'mail')
            ->leftJoin('mail.shop', 'shop')
            ->orderBy('shop.name', 'ASC');

        if (!empty($locale)) {
            $builder->andWhere('shop.id = :locale')
                ->setParameter('locale', $locale);
        }

        if ($onlyDefaultSubmission) {
            $builder->andWhere("mail.name = 'sSTANDARD'");

            return $builder;
        }

        if ($onlyCustomSubmissions) {
            $builder->andWhere('mail.systemDependent = 0');
        }

        return $builder;
    }
}
