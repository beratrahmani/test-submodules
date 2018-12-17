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

namespace SwagAboCommerce\Subscriber;

use Enlight\Event\SubscriberInterface;
use SwagAboCommerce\Services\AboOrderException;
use SwagAboCommerce\Services\DependencyProviderInterface;
use SwagAboCommerce\Services\OrderCronServiceInterface;

class OrderCronJob implements SubscriberInterface
{
    /**
     * @var OrderCronServiceInterface
     */
    private $orderCronService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param OrderCronServiceInterface   $orderCronService
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        OrderCronServiceInterface $orderCronService,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->orderCronService = $orderCronService;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_SwagAboCommerce_OrderCronJob' => 'onOrderCronJob',
        ];
    }

    /**
     * @return string
     */
    public function onOrderCronJob()
    {
        $orders = $this->orderCronService->getOverdueOrders();
        $request = $this->dependencyProvider->getFrontRequest();

        $output = PHP_EOL;

        if (empty($orders)) {
            $output .= sprintf('SwagAboCommerce: No due Abo-orders found%s', PHP_EOL);

            //Echo the output to the console.
            echo $output;

            //Set the return value of the cronjob itself.
            return $output;
        }

        $counter = 0;
        $occurredErrors = PHP_EOL;

        foreach ($orders as $item) {
            //If request equals NULL, the command was run by the CLI tools,
            //therefore it is required to set the new router context (and shop objects) manually.
            if ($request === null) {
                $this->orderCronService->setRouterContext($item['shopId']);
            }

            try {
                $result = $this->orderCronService->createOrder($item['aboId']);
                $output .= sprintf('Created new abo order: %s.%s', $result['data']['orderNumber'], PHP_EOL);
            } catch (AboOrderException $exception) {
                $occurredErrors .= $exception->getMessage() . PHP_EOL;
            }

            ++$counter;
        }

        $output .= sprintf(
            'SwagAboCommerce: %s Abo-Orders processed. %s',
            $counter,
            $occurredErrors
        );

        //Echo the output to the console.
        echo $output;

        //Set the return value of the cronjob itself.
        return $output;
    }
}
