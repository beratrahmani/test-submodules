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

namespace SwagTicketSystem\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use SwagTicketSystem\Models\Ticket\Repository;
use SwagTicketSystem\Models\Ticket\Support;
use SwagTicketSystem\Tests\KernelTestCaseTrait;

class RepositoryTest extends TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Connection
     */
    private $connection;

    public function setUp()
    {
        $this->connection = self::getContainer()->get('dbal_connection');
        $this->connection->beginTransaction();

        $sql = file_get_contents(__DIR__ . '/../_fixtures/tickets.sql');
        $this->connection->executeQuery($sql);
    }

    public function tearDown()
    {
        $this->connection->rollBack();
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return self::getContainer()->get('models')->getRepository(Support::class);
    }

    public function test_get_widget_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getWidgetQuery(1, 0);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
        self::assertSame('33a106fa6cfb84a1ae108649716baa03', $result[0]['uniqueId']);
        self::assertSame('Message', $result[0]['message']);
        self::assertSame('offen', $result[0]['status']);
    }

    public function test_get_list_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getListQuery(0, 3, [], null, 0, 1);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertSame('66fhr6fa6cfb84a1ae108649716baa03', $result[0]['uniqueId']);
    }

    public function test_get_frontend_list_tickets_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getFrontendTicketListQuery(1);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertSame(1, $result[0]['id']);
        self::assertSame('Max Mustermann', $result[0]['contact']);
        self::assertSame('Muster GmbH', $result[0]['company']);
    }

    public function test_get_customer_tickets_list_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getCustomerTicketListQuery(2, null, null, null, null, [['property' => 'free', 'value' => 'b2b']]);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
        self::assertSame('B2B', $result[0]['company']);
    }

    public function test_get_ticket_detail_query_by_ticket_id()
    {
        $repository = $this->getRepository();

        $query = $repository->getTicketDetailQueryById(1);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertSame('33a106fa6cfb84a1ae108649716baa03', $result[0]['uniqueId']);
    }

    public function test_get_ticket_detail_query_by_ticket_uniqueid()
    {
        $repository = $this->getRepository();
        $uniqueId = '66fhr6fa6cfb84a1ae108649716baa03';

        $queryBuilder = $repository->getTicketDetailQueryBuilderByUniqueId($uniqueId);

        self::assertInstanceOf(QueryBuilder::class, $queryBuilder);

        $result = $queryBuilder->getQuery()->getArrayResult();

        self::assertNotEmpty($result);
        self::assertSame($uniqueId, $result[0]['uniqueId']);
    }

    public function test_get_ticket_history_list()
    {
        $repository = $this->getRepository();
        $ticketId = 1;

        $query = $repository->getTicketHistoryQuery($ticketId);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
    }

    public function test_status_list_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getStatusListQuery();

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();
        self::assertNotEmpty($result);
        self::assertCount(4, $result);
        self::assertSame(1, $result[3]['closed']);
    }

    public function test_mail_list_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getMailListQuery();

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();
        self::assertNotEmpty($result);
        self::assertCount(10, $result);
    }

    public function test_mail_list_query_only_custom_submissions()
    {
        $repository = $this->getRepository();

        $query = $repository->getMailListQuery(1, true);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(4, $result);
        self::assertSame('Shopware Demo', $result[0]['fromName']);
    }

    public function test_mail_list_query_only_default_submissions()
    {
        $repository = $this->getRepository();

        $query = $repository->getMailListQuery(1, false, true);

        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
        self::assertSame('Standardvorlage', $result[0]['description']);
    }

    public function test_system_submission_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getSystemSubmissionQuery('sSTRAIGHTANSWER', 1);
        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
        self::assertSame(1, $result[0]['id']);
    }

    public function test_tickettype_list_query()
    {
        $repository = $this->getRepository();

        $query = $repository->getTicketTypeListQuery([], null, 1, 25);
        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertSame('RMA', $result[0]['name']);
    }

    public function test_tickettype_list_query_with_filter()
    {
        $repository = $this->getRepository();

        $query = $repository->getTicketTypeListQuery([['property' => 'free', 'value' => 'fcc0']], null, 0, 25);
        self::assertInstanceOf(Query::class, $query);

        $result = $query->getArrayResult();

        self::assertNotEmpty($result);
        self::assertCount(1, $result);
        self::assertSame('RMA', $result[0]['name']);
    }
}
