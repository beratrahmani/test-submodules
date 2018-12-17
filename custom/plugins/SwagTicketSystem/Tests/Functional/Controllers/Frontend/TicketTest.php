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

namespace SwagTicketSystem\Tests\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use SwagTicketSystem\Tests\ControllerTestTrait;
use SwagTicketSystem\Tests\KernelTestCaseTrait;

require_once __DIR__ . '/../../../../Controllers/Frontend/Ticket.php';

class TicketTest extends TestCase
{
    use KernelTestCaseTrait;
    use ControllerTestTrait;

    public function test_listing_action()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (4, '55b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        /** @var Connection $connection */
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        $view = $this->View();
        $request = $this->Request();

        $session = self::getContainer()->get('session');
        $session->offsetSet('sUserId', 1);

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer());
        $ctrl->preDispatch();
        $ctrl->listingAction();

        $this->assertSame('listing', $view->getAssign('sAction'));
        $this->assertSame(1.0, $view->getAssign('sNumberPages'));
        $this->assertCount(1, $view->getAssign('entries'));
        $this->assertCount(1, $view->getAssign('ticketStore'));
    }

    public function test_request_action_without_auth()
    {
        $view = $this->View();
        $request = $this->Request();

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer());
        $ctrl->preDispatch();
        $ctrl->requestAction();

        $this->assertSame('index', $this->Request()->getActionName());
        $this->assertSame('account', $this->Request()->getControllerName());
    }

    public function test_detail_action()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (6, '55b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        /** @var Connection $connection */
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        $view = $this->View();
        $request = $this->Request();
        $request->setParam('sAID', '55b206fa6cfb84a1ae108649716baa03');

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer());
        $ctrl->preDispatch();
        $ctrl->detailAction();

        $this->assertNotEmpty($view->getAssign('ticketDetails'));
        $this->assertSame(6, $view->getAssign('ticketDetails')['id']);
        $this->assertEmpty($view->getAssign('userAttachments'));
        $this->assertEmpty($view->getAssign('ticketHistoryDetails'));
        $this->assertFalse((bool) $view->getAssign('sUserLoggedIn'));
    }

    public function test_send_answer_action()
    {
        $sql = <<<SQL
INSERT INTO `s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `formId`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`)
VALUES (7, '77b206fa6cfb84a1ae108649716baa03', 1, 0 , 1, 1, 16, 'test@example.com', 'Subject', 'Message', '2018-01-01 16:00:00', '2018-01-01 16:00:00', 'a:4:{i:0;a:4:{s:4:"name";s:6:"anrede";s:5:"label";s:6:"Anrede";s:3:"typ";s:6:"select";s:5:"value";s:4:"Herr";}i:1;a:4:{s:4:"name";s:7:"vorname";s:5:"label";s:7:"Vorname";s:3:"typ";s:4:"text";s:5:"value";s:3:"Max";}i:2;a:4:{s:4:"name";s:8:"nachname";s:5:"label";s:8:"Nachname";s:3:"typ";s:4:"text";s:5:"value";s:10:"Mustermann";}i:3;a:4:{s:4:"name";s:7:"telefon";s:5:"label";s:7:"Telefon";s:3:"typ";s:4:"text";s:5:"value";s:0:"";}}');
SQL;
        /** @var Connection $connection */
        $connection = self::getContainer()->get('models')->getConnection();
        $connection->executeQuery($sql);

        $view = $this->View();
        $request = $this->Request();
        $request->setParam('ticketId', 7);
        $request->setParam('sAnswer', 'This is my answer!');

        $ctrl = new TicketControllerMock($request, $this->Response(), $view, self::getContainer());
        $ctrl->preDispatch();
        $ctrl->sendAnswerAction();

        $history = $connection->executeQuery('SELECT * FROM s_ticket_support_history WHERE ticketID = 7')->fetchAll();

        $this->assertNotEmpty($history);
        $this->assertSame('7', $history[0]['ticketID']);
        $this->assertSame('This is my answer!', $history[0]['message']);
        $this->assertSame('1', $history[0]['statusId']);
    }
}

class TicketControllerMock extends \Shopware_Controllers_Frontend_Ticket
{
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
        Container $container
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->container = $container;
    }
}
