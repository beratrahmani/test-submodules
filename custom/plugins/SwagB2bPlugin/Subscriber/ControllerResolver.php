<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;

class ControllerResolver implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_B2bAddressSelect' => 'getAddressSelectControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_B2bCategorySelect' => 'getCategorySelectControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_B2bBudgetSelect' => 'getBudgetSelectControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_B2bCart' => 'getCartControllerPath',
        ];
    }

    /**
     * @return string
     */
    public function getAddressSelectControllerPath(): string
    {
        return __DIR__ . '/../Controllers/ExtendedFrontend/B2bAddressSelect.php';
    }

    /**
     * @return string
     */
    public function getCategorySelectControllerPath(): string
    {
        return __DIR__ . '/../Controllers/ExtendedFrontend/B2bCategorySelect.php';
    }

    /**
     * @return string
     */
    public function getBudgetSelectControllerPath(): string
    {
        return __DIR__ . '/../Controllers/ExtendedFrontend/B2bBudgetSelect.php';
    }

    /**
     * @return string
     */
    public function getCartControllerPath(): string
    {
        return __DIR__ . '/../Controllers/ExtendedFrontend/B2bCart.php';
    }
}
