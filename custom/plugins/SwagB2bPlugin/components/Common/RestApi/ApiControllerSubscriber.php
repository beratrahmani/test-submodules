<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi;

use Enlight\Event\SubscriberInterface;

class ApiControllerSubscriber implements SubscriberInterface
{
    const CONTROLLER_NAME = 'SwagB2BController';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Api_B2b' => 'onGetControllerPathApi',
            'Enlight_Controller_Dispatcher_ControllerPath_Api_' . self::CONTROLLER_NAME => 'onGetControllerPathApi',
        ];
    }

    /**
     * @return string
     */
    public function onGetControllerPathApi()
    {
        Shopware()->Front()->Request()->setControllerName(self::CONTROLLER_NAME);

        return __DIR__ . '/' . self::CONTROLLER_NAME . '.php';
    }
}
