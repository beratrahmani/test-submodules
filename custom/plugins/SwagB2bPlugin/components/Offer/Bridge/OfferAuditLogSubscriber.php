<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\B2B\Offer\Framework\OfferContextRepository;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Bridge\OrderChangeTrigger;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferAuditLogSubscriber implements SubscriberInterface
{
    const ORDER_STATUS = 0;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OfferService $offerService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OfferService $offerService
    ) {
        $this->authenticationService = $authenticationService;
        $this->offerService = $offerService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderChangeTrigger::EVENT_NAME => 'writeLogEntry',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function writeLogEntry(Enlight_Event_EventArgs $args)
    {
        $orderContext = $args->get('orderContext');

        if (OfferContextRepository::STATUS_OFFER !== $args->get('oldStatus')
            || self::ORDER_STATUS !== $args->get('newStatus')
            || !$this->authenticationService->isB2b()
        ) {
            return;
        }

        $this->offerService
            ->createOfferCreatedStatusChangeLogEntry($orderContext->id, $this->authenticationService->getIdentity());
    }
}
