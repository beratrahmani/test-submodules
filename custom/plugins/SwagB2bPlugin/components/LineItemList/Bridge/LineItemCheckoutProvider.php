<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Bridge;

use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemCheckoutProviderInterface;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListSource;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware_Components_Config;

class LineItemCheckoutProvider implements LineItemCheckoutProviderInterface
{
    /**
     * @var LineItemBridgeRepository
     */
    private $bridgeRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

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
        $this->bridgeRepository = $bridgeRepository;
        $this->currencyService = $currencyService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function createList(LineItemListSource $source): LineItemList
    {
        $source = $this->testSource($source);

        $list = new LineItemList();
        $list->references = $this->extendReferencesFromCart($source);
        $list->currencyFactor = $this->currencyService->createCurrencyContext()->currentCurrencyFactor;

        $this->setListAmounts($list, $source);

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromCartId(string $cartId): LineItemList
    {
        $cart = $this->bridgeRepository->fetchCartDataById($cartId);

        $references = [];
        foreach ($cart as $shopLineItem) {
            if ((int) $shopLineItem['modus'] !== 0) {
                continue;
            }

            $reference = new LineItemReference();

            $reference->referenceNumber = $shopLineItem['ordernumber'];
            $reference->quantity = (int) $shopLineItem['quantity'];
            $reference->amount =  (string) $shopLineItem['price'];
            $reference->amountNet = (string) $shopLineItem['netprice'];
            $reference->mode = $shopLineItem['modus'];

            $references[] = $reference;
        }

        $list = new LineItemList();
        $list->references = $references;
        $list->currencyFactor = (float) $cart[0]['currencyFactor'];

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function updateList(LineItemList $list, LineItemListSource $lineItemListSources): LineItemList
    {
        $source = $this->testSource($lineItemListSources);

        $list->references = $this->extendReferencesFromCart($source, $list->references);

        $this->setListAmounts($list, $source);

        return $list;
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
            $orderNumbers[] = $orderNumber = $shopLineItem['ordernumber'];

            $reference = $this->findReference($references, $orderNumber);

            $reference->referenceNumber = $orderNumber;
            $reference->quantity = (int) $shopLineItem['quantity'];
            $reference->mode = $shopLineItem['modus'];
            $reference->amount = $shopLineItem['priceNumeric'];
            $reference->amountNet = $shopLineItem['netprice'];

            if ($this->config->get('sARTICLESOUTPUTNETTO')) {
                $reference->amount = round($reference->amountNet * ((100 + $shopLineItem['tax_rate']) / 100), 2);
            }
        }

        foreach ($references as $index => $reference) {
            if (!in_array($reference->referenceNumber, $orderNumbers, true)) {
                unset($references[$index]);
            }
        }

        return array_values($references);
    }

    /**
     * @internal
     * @param LineItemListSource $source
     * @throws \InvalidArgumentException
     * @return LineItemCheckoutSource
     */
    private function testSource(LineItemListSource $source): LineItemCheckoutSource
    {
        if (!$source instanceof LineItemCheckoutSource) {
            throw new \InvalidArgumentException('Invalid source class provided');
        }

        return $source;
    }

    /**
     * @internal
     * @param LineItemList $list
     * @param $source
     */
    protected function setListAmounts(LineItemList $list, $source)
    {
        $list->amount = $source->basketData['AmountNumeric'];
        $list->amountNet = $source->basketData['AmountNetNumeric'];

        if ($this->config->get('sARTICLESOUTPUTNETTO')) {
            $list->amount = $source->basketData['AmountWithTaxNumeric'];
        }
    }

    /**
     * @internal
     * @param LineItemReference[] $references
     * @param string $orderNumber
     * @return LineItemReference
     */
    protected function findReference(array &$references, string $orderNumber): LineItemReference
    {
        foreach ($references as $reference) {
            if (strcasecmp($reference->referenceNumber, $orderNumber) === 0) {
                return $reference;
            }
        }

        $reference = new LineItemReference();
        $references[] = $reference;

        return $reference;
    }
}
