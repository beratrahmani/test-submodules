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

namespace SwagTicketSystem\Tests\Functional\Controllers\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Shop\Locale;
use SwagTicketSystem\Models\Ticket\Type;
use SwagTicketSystem\Tests\ControllerTestTrait;
use SwagTicketSystem\Tests\KernelTestCaseTrait;
use Symfony\Component\DependencyInjection\Container;

require_once __DIR__ . '/../../../../Controllers/Backend/Ticket.php';

class TicketTest extends TestCase
{
    use KernelTestCaseTrait;
    use ControllerTestTrait;

    /**
     * @dataProvider provider
     */
    public function test_get_action_success($action)
    {
        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $ctrl = new TicketControllerMock($this->Request(), $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->$action();

        $this->assertTrue($view->getAssign('success'));
    }

    public function test_update_form_action()
    {
        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 16);
        $request->setParam('ticketTypeid', 2);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->updateFormAction();

        $this->assertTrue($view->getAssign('success'));
    }

    public function test_create_ticket_type()
    {
        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('name', 'test');
        $request->setParam('gridcolor', '#fff000');

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->createTicketTypeAction();

        $this->assertTrue($view->getAssign('success'));

        $type = self::getContainer()->get('models')->getRepository(Type::class)->findOneBy(['name' => 'test']);

        self::assertSame('test', $type->getName());
        self::assertSame('#fff000', $type->getGridColor());
    }

    public function test_update_ticket_type()
    {
        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('name', 'test1');
        $request->setParam('id', 1);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->updateTicketTypeAction();

        $this->assertTrue($view->getAssign('success'));

        $type = self::getContainer()->get('models')->getRepository(Type::class)->findOneBy(['name' => 'test1']);

        self::assertSame('test1', $type->getName());
        self::assertSame(1, $type->getId());
    }

    public function test_update_ticket()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (4, '55b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 4);
        $request->setParam('employeeId', 1);
        $request->setParam('statusId', 2);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->updateTicketAction();

        $this->assertTrue($view->getAssign('success'));

        $ticketStatus = (int) $connection->executeQuery('SELECT statusID FROM `s_ticket_support` WHERE id = 4')->fetchColumn();

        $this->assertSame(2, $ticketStatus);
    }

    public function test_ticket_deletion()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (5, '66b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 5);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->destroyTicketAction();

        $this->assertTrue($view->getAssign('success'));

        $ticket = $connection->executeQuery('SELECT id FROM `s_ticket_support` WHERE id = 5')->fetchColumn();

        $this->assertFalse($ticket);
    }

    public function test_update_mail()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support_mails` (`id`, `name`, `description`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `sys_dependent`, `isocode`, `shop_id`)
VALUES (11, 'CUSTOM', 'Benachrichtigung - Neues Ticket', 'info@example.com', 'Mein Absendername', 'Es liegt ein neues Ticket vor', 'Es liegt ein neues Ticket für Sie bereit. Die TicketID lautet: {sTicketID}', 'Es liegt ein neues Ticket für Sie bereit. Die TicketID lautet: {sTicketID}', 1, '', 1, 'de', 1);
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 11);
        $request->setParam('name', 'CUSTOMCHANGE');
        $request->setParam('shopId', 1);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->updateMailAction();

        $this->assertTrue($view->getAssign('success'));

        $name = $connection->executeQuery('SELECT name FROM `s_ticket_support_mails` WHERE id = 11')->fetchColumn();

        $this->assertSame('CUSTOMCHANGE', $name);
    }

    public function test_mail_deletion()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support_mails` (`id`, `name`, `description`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `sys_dependent`, `isocode`, `shop_id`)
VALUES (12, 'CUSTOM', 'Benachrichtigung - Neues Ticket', 'info@example.com', 'Mein Absendername', 'Es liegt ein neues Ticket vor', 'Es liegt ein neues Ticket für Sie bereit. Die TicketID lautet: {sTicketID}', 'Es liegt ein neues Ticket für Sie bereit. Die TicketID lautet: {sTicketID}', 1, '', 1, 'de', 1);
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 12);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->destroyMailAction();

        $this->assertTrue($view->getAssign('success'));

        $mail = $connection->executeQuery('SELECT id FROM `s_ticket_support_mails` WHERE id = 12')->fetchColumn();

        $this->assertFalse($mail);
    }

    public function test_get_ticket_history()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (5, '66b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        $sql = <<<SQL
INSERT INTO `s_ticket_support_history` (`id`, `ticketID`, `swUser`, `subject`, `message`, `receipt`, `support_type`, `receiver`, `direction`, `attachment`, `statusId`)
VALUES (2, 5, '', 'Antwort zu Ihrem Ticket #1', '<p>Standardvorlage Antwort auf das Ticket #1</p>', '2018-01-12 10:16:05', 'direct', 'test@example.com', 'OUT', '', 1);
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('id', 5);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->getTicketHistoryAction();

        $this->assertTrue($view->getAssign('success'));
        $this->assertGreaterThan(0, $view->getAssign('total'));
    }

    public function test_get_ticket_for_customer()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (5, '66b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        /** @var Locale $locale */
        $locale = self::getContainer()->get('models')->getRepository(Locale::class)->find(1);
        $view = $this->View();
        $request = $this->Request();
        $request->setParam('customerID', 1);
        $request->setParam('statusId', 1);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer(), new AuthMock($locale));

        $ctrl->preDispatch();
        $ctrl->getTicketsForCustomerAction();

        $this->assertTrue($view->getAssign('success'));
        $this->assertSame(1, $view->getAssign('total'));
    }

    public function provider()
    {
        return [
            ['getWidgetListAction'],
            ['getListAction'],
            ['getStatusListAction'],
            ['getMailListAction'],
            ['getCustomerListAction'],
            ['getEmployeeListAction'],
            ['getTicketTypesAction'],
            ['getShopsWithSubmissionsAction'],
            ['getShopsWithOutSubmissionsAction'],
            ['getFormsAction'],
            ['getCurrentEmployeeIdAction'],
        ];
    }
}

class TicketControllerMock extends \Shopware_Controllers_Backend_Ticket
{
    private $authMock;

    /**
     * @param \Enlight_Controller_Request_Request   $request
     * @param \Enlight_Controller_Response_Response $response
     * @param \Enlight_View_Default                 $view
     * @param Container                             $container
     */
    public function __construct(
        \Enlight_Controller_Request_Request $request,
        \Enlight_Controller_Response_Response $response,
        \Enlight_View_Default $view,
        Container $container,
        AuthMock $authMock
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->container = $container;
        $this->authMock = $authMock;
    }

    public function get($name)
    {
        if (strtolower($name) === 'auth') {
            return $this->authMock;
        }

        return $this->container->get($name);
    }
}

class AuthMock
{
    /**
     * @var Locale
     */
    private $locale;

    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    public function getIdentity()
    {
        $identity = new \stdClass();
        $identity->id = 1;
        $identity->roleID = 1;
        $identity->localeID = 1;
        $identity->active = 1;

        $locale = $this->locale;

        $identity->locale = $locale;

        return $identity;
    }
}
