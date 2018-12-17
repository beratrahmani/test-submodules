<?php declare(strict_types=1);

namespace Shopware\B2B\Common\MvcExtension;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;

class RoutingInterceptor
{
    /**
     * @param \Enlight_Controller_Action $controller
     * @param callable $dispatchCall
     */
    public function interceptException(\Enlight_Controller_Action $controller, callable $dispatchCall)
    {
        try {
            $dispatchCall();
        } catch (B2bControllerRedirectException $e) {
            $baseParams = [
                'action' => $e->getAction(),
                'controller' => $e->getController(),
                'module' => $e->getModule(),
            ];

            $controller->redirect(array_merge($baseParams, $e->getParams()));

            return;
        } catch (B2bControllerForwardException $e) {
            $controller->forward($e->getAction(), $e->getController(), $e->getModule(), $e->getParams());

            return;
        }
    }
}
