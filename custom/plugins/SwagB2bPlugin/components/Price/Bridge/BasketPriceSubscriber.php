<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Price\Framework\PriceEntity;
use Shopware\B2B\Price\Framework\PriceRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class BasketPriceSubscriber implements SubscriberInterface
{
    /**
     * @var PriceRepository
     */
    private $priceRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'updateBasketPrice',
        ];
    }

    /**
     * @param PriceRepository $priceRepository
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        PriceRepository $priceRepository,
        AuthenticationService $authenticationService
    ) {
        $this->priceRepository = $priceRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return array
     */
    public function updateBasketPrice(Enlight_Event_EventArgs $args): array
    {
        $article = $args->getReturn();

        if (!$this->authenticationService->isB2b()) {
            return $article;
        }

        $quantity = (int) $args->get('quantity');

        $orderNumber = $article['ordernumber'];

        $debtorId = $this->authenticationService
            ->getIdentity()->getOwnershipContext()->shopOwnerUserId;

        try {
            $priceEntity = $this->priceRepository->fetchPriceByDebtorIdAndOrderNumberAndQuantity($debtorId, $orderNumber, $quantity);

            /** @var $priceEntity PriceEntity */
            $article['price'] = $priceEntity->price;
        } catch (NotFoundException $e) {
            // nth
        }

        return $article;
    }
}
