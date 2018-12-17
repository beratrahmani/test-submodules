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

namespace SwagTicketSystem\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ControllerActionEventArgs;
use Shopware_Components_Acl as AclCmp;
use SwagTicketSystem\Components\DependencyProvider;

class Backend implements SubscriberInterface
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var AclCmp
     */
    private $acl;

    /**
     * @param DependencyProvider $dependencyProvider
     * @param AclCmp             $acl
     */
    public function __construct(DependencyProvider $dependencyProvider, AclCmp $acl)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->acl = $acl;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ControllerActionEventArgs::POST_SECURE_EVENT . '_Backend_Index' => 'onPostDispatchBackendIndex',
            ControllerActionEventArgs::POST_SECURE_EVENT . '_Backend_Customer' => 'onPostDispatchBackendCustomer',
            ControllerActionEventArgs::POST_SECURE_EVENT . '_Backend_Form' => 'onLoadBackendFormModule',
        ];
    }

    /**
     * @param ControllerActionEventArgs $args
     */
    public function onPostDispatchBackendIndex(ControllerActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        // if the controller action name equals "index" we have to extend the backend index application
        if ($request->getActionName() === 'index') {
            $view->assign('ticket_backendUserId', $this->dependencyProvider->getAuth()->getIdentity()->id);
            $view->extendsTemplate('backend/index/ticket_system_header.tpl');
            $view->extendsTemplate('backend/ticket/widget_app.js');
        }
    }

    /**
     * @param ControllerActionEventArgs $args
     */
    public function onPostDispatchBackendCustomer(ControllerActionEventArgs $args)
    {
        $currentEmployee = $this->dependencyProvider->getAuth()->getIdentity();

        //Check if the user has the correct privileges to perform the ticket actions
        if ($this->acl->isAllowed($currentEmployee->role, 'ticket')) {
            $request = $args->getSubject()->Request();

            // if the controller action name equals "load" we have to load all application components.
            if ($request->getActionName() === 'load') {
                $args->getSubject()->View()->extendsTemplate('backend/customer/view/detail/ticket_system_window.js');
            }

            if ($request->getActionName() === 'index') {
                $args->getSubject()->View()->extendsTemplate('backend/customer/ticket_system_app.js');
            }
        }
    }

    /**
     * @param ControllerActionEventArgs $args
     */
    public function onLoadBackendFormModule(ControllerActionEventArgs $args)
    {
        $request = $args->getSubject()->Request();

        if ($request->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate(
                'backend/form/view/main/uploadgrid.js'
            );
        }
    }
}
