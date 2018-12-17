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

use PHPUnit\Framework\TestCase;
use SwagTicketSystem\Components\CreateAnswerAdapter;
use SwagTicketSystem\Components\CreateAnswerAdapterInterface;
use SwagTicketSystem\Components\TicketSystemInterface;
use SwagTicketSystem\Tests\DatabaseTestCaseTrait;
use SwagTicketSystem\Tests\KernelTestCaseTrait;

class CreateAnswerAdapterTest extends TestCase
{
    use KernelTestCaseTrait;
    use DatabaseTestCaseTrait;

    public function setUp()
    {
        $connection = static::getContainer()->get('dbal_connection');

        $sql = <<<SQL
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'mailer_mailer' LIMIT 1);
INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) 
VALUES (@elementId, 1, 's:4:"file";')
ON DUPLICATE KEY UPDATE `value` = 's:4:"file";';
SQL;
        $connection->executeQuery($sql);

        $sql = file_get_contents(__DIR__ . '/../_fixtures/tickets.sql');
        $connection->executeQuery($sql);
    }

    public function test_create_answer()
    {
        $adapter = $this->getComponent();

        $this->assertInstanceOf(CreateAnswerAdapterInterface::class, $adapter);

        $mailData = [
            'module' => 'backend',
            'controller' => 'Ticket',
            'action' => 'answerTicket',
            'employeeCombo' => '0',
            'shopId' => '1',
            'noNotify' => 'true',
            'onlyEmailAnswer' => '0',
            'email' => 'test@example.com',
            'cc' => '',
            'status' => '1',
            'senderAddress' => 'info@example.com',
            'senderName' => 'Max Mustermann',
            'subject' => 'Antwort zu ihrem Ticket {sTicketId}',
            'message' => 'Standardvorlage Antwort auf das Ticket {sTicketID}',
            'media-manager-selection' => '',
            'id' => '1',
            'isHTML' => 'false',
        ];

        $adapter->createAnswer($mailData);

        $dbConnection = self::getContainer()->get('dbal_connection');
        $sql = <<<SQL
SELECT * FROM `s_ticket_support_history` WHERE ticketID = 1 ORDER BY receipt DESC
SQL;
        $history = $dbConnection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        self::assertNotEmpty($history);
        self::assertSame($mailData['status'], $history[0]['statusId']);
        self::assertSame($mailData['id'], $history[0]['ticketID']);
        self::assertSame($mailData['subject'], $history[0]['subject']);
        self::assertSame('Standardvorlage Antwort auf das Ticket #1', $history[0]['message']);
        self::assertSame(TicketSystemInterface::ANSWER_DIRECTION_OUT, $history[0]['direction']);
        self::assertSame('manage', $history[0]['support_type']);
    }

    public function test_create_answer_with_email()
    {
        $adapter = $this->getComponent();

        $mailData = [
            'module' => 'backend',
            'controller' => 'Ticket',
            'action' => 'answerTicket',
            'employeeCombo' => '0',
            'shopId' => '1',
            'noNotify' => 'true',
            'onlyEmailAnswer' => '0',
            'email' => 'test@example.com',
            'cc' => '',
            'status' => '2',
            'senderAddress' => 'info@example.com',
            'senderName' => 'Max Mustermann',
            'subject' => 'Antwort zu ihrem Ticket {sTicketId}',
            'message' => 'Standardvorlage Antwort auf das Ticket {sTicketID}',
            'media-manager-selection' => '',
            'id' => '1',
            'isHTML' => 'false',
        ];

        $adapter->createAnswer($mailData);

        $dbConnection = self::getContainer()->get('dbal_connection');
        $sql = <<<SQL
SELECT * FROM `s_ticket_support_history` WHERE ticketID = 1 ORDER BY receipt DESC
SQL;
        $history = $dbConnection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

        self::assertNotEmpty($history);
        self::assertSame($mailData['status'], $history[0]['statusId']);
        self::assertSame($mailData['id'], $history[0]['ticketID']);
        self::assertSame($mailData['subject'], $history[0]['subject']);
        self::assertSame('Standardvorlage Antwort auf das Ticket #1', $history[0]['message']);
        self::assertSame(TicketSystemInterface::ANSWER_DIRECTION_OUT, $history[0]['direction']);
    }

    /**
     * @throws \Exception
     *
     * @return CreateAnswerAdapter
     */
    private function getComponent()
    {
        return static::getContainer()->get('swag_ticket_system.create_answer_adapter');
    }
}
