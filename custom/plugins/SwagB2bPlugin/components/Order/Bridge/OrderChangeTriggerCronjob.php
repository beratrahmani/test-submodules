<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Enlight\Event\SubscriberInterface;

class OrderChangeTriggerCronjob implements SubscriberInterface
{
    /**
     * @var OrderChangeTriggerCommand
     */
    private $command;

    /**
     * @param OrderChangeTriggerCommand $command
     */
    public function __construct(OrderChangeTriggerCommand $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_B2bOrderTriggerQueue' => 'onRunOrderTriggerQueue',
        ];
    }

    public function onRunOrderTriggerQueue()
    {
        $this->command->triggerOrderChangeListener(0);
    }
}
