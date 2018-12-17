<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_ActionEventArgs;

class CheckoutConfirmSubscriber implements SubscriberInterface
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @param Enlight_Components_Session_Namespace $session
     */
    public function __construct(Enlight_Components_Session_Namespace $session)
    {
        $this->session = $session;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'setOfferState',
        ];
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function setOfferState(Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->session->get('offerEntityId', false)) {
            return;
        }

        $args->getSubject()->View()->assign('isB2bOffer', true);
    }
}
