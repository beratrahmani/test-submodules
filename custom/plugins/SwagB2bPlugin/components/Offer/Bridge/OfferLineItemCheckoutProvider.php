<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Bridge\LineItemBridgeRepository;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutProvider;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceEntity;
use Shopware_Components_Config;

class OfferLineItemCheckoutProvider extends LineItemCheckoutProvider
{
    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param LineItemBridgeRepository $bridgeRepository
     * @param CurrencyService $currencyService
     * @param Shopware_Components_Config $config
     */
    public function __construct(
        LineItemBridgeRepository $bridgeRepository,
        CurrencyService $currencyService,
        Shopware_Components_Config $config
    ) {
        parent::__construct($bridgeRepository, $currencyService, $config);
        $this->currencyService = $currencyService;
    }

    /**
     * @internal
     * @param LineItemCheckoutSource $source
     * @param LineItemReference[] $references
     * @return LineItemReference[]
     */
    protected function extendReferencesFromCart(LineItemCheckoutSource $source, array $references = []): array
    {
        $orderNumbers = [];
        foreach ($source->basketData['content'] as $shopLineItem) {
            if ((int) $shopLineItem['modus'] !== 0) {
                continue;
            }

            $orderNumbers[] = $orderNumber = $shopLineItem['ordernumber'];

            $this->applyReference($references, $orderNumber, $shopLineItem);
        }

        foreach ($references as $index => $reference) {
            if (!in_array($reference->referenceNumber, $orderNumbers, true)) {
                unset($references[$index]);
            }
        }

        return array_values($references);
    }

    /**
     * @param array $references
     * @param string $orderNumber
     * @param array $shopLineItem
     */
    private function applyReference(array &$references, string $orderNumber, array $shopLineItem)
    {

        /** @var OfferLineItemReferenceEntity $reference */
        $reference = $this->findReferenceForOffer($references, $orderNumber);

        $reference->referenceNumber = $orderNumber;
        $reference->mode = $shopLineItem['modus'];
        $reference->amount = $shopLineItem['priceNumeric'];
        $reference->amountNet = $shopLineItem['netprice'];
        $reference->discountCurrencyFactor = $this->currencyService
            ->createCurrencyContext()->currentCurrencyFactor;

        if (!$reference->discountAmount) {
            $reference->discountAmount = $reference->amount;
        }

        if (!$reference->discountAmountNet) {
            $reference->discountAmountNet = $reference->amountNet;
        }

        if (!($reference->quantity === (int) $shopLineItem['quantity'])) {
            $reference->discountAmountNet = $reference->amountNet;
            $reference->discountAmount = $reference->amount;
        }

        $reference->quantity = (int) $shopLineItem['quantity'];
    }

    /**
     * @param LineItemReference[] $references
     * @param string $orderNumber
     * @return OfferLineItemReferenceEntity
     */
    private function findReferenceForOffer(array &$references, string $orderNumber): OfferLineItemReferenceEntity
    {
        foreach ($references as $reference) {
            if (strcasecmp($reference->referenceNumber, $orderNumber) === 0) {
                return $reference;
            }
        }

        $reference = new OfferLineItemReferenceEntity();
        $references[] = $reference;

        return $reference;
    }
}
