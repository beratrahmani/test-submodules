<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrderChangeTriggerCommand extends ShopwareCommand
{
    /**
     * @var OrderChangeQueueRepository
     */
    private $orderChangeQueueRepository;

    /**
     * @var OrderChangeTrigger
     */
    private $changeTrigger;

    /**
     * @param OrderChangeQueueRepository $orderChangeQueueRepository
     * @param OrderChangeTrigger $changeTrigger
     */
    public function __construct(
        OrderChangeQueueRepository $orderChangeQueueRepository,
        OrderChangeTrigger $changeTrigger
    ) {
        $this->orderChangeQueueRepository = $orderChangeQueueRepository;
        $this->changeTrigger = $changeTrigger;

        parent::__construct('b2b:order_change_trigger');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp('The <info>%command.name%</info> triggers the order change queue to update the b2b order context states.')
            ->setDescription('Trigger the order change queue for status updates')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'How many order contexts should be updated.', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');

        $this->triggerOrderChangeListener($limit);
    }

    /**
     * @param int $limit
     */
    public function triggerOrderChangeListener(int $limit)
    {
        foreach ($this->orderChangeQueueRepository->fetchAndClearQueueForCli($limit) as $orderId) {
            $this->changeTrigger->updateOrderContextFromOrder($orderId);
        }
    }
}
