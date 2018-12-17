<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\B2B\Shop\Framework\OrderRelationServiceInterface;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderRelationService implements OrderRelationServiceInterface
{
    /**
     * @var OrderRelationRepository
     */
    private $repository;

    /**
     * @var \Shopware_Components_Translation
     */
    private $translator;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param OrderRelationRepository $repository
     * @param \Shopware_Components_Translation $translator
     * @param ContainerInterface $container
     */
    public function __construct(
        OrderRelationRepository $repository,
        \Shopware_Components_Translation $translator,
        ContainerInterface $container
    ) {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * @param int $shippingId
     * @return string
     */
    public function getShippingNameForId(int $shippingId): string
    {
        $shippingData = $this->repository
            ->fetchShippingDataForId($shippingId);

        /** @var Shop $shop */
        $shop = $this->getCurrentShop();

        $shipping = $this->translateShipping($shippingData, $shop);

        return $shipping['name'];
    }

    /**
     * @param int $paymentId
     * @return string
     */
    public function getPaymentNameForId(int $paymentId): string
    {
        $paymentData = $this->repository
            ->fetchPaymentDataForId($paymentId);

        $shop = $this->getCurrentShop();

        $payment = $this->translatePayment($paymentData, $shop);

        return $payment['description'];
    }

    /**
     * @param array $shipping
     * @param Shop $shop
     * @return array
     */
    private function translateShipping(array $shipping, Shop $shop): array
    {
        $shippingTranslations = $this->translator
            ->readWithFallback(
                $shop->getId(),
                $this->extractFallbackId($shop),
                'config_dispatch'
            );

        $dispatchId = $shipping['id'];

        if (isset($shippingTranslations[$dispatchId]['dispatch_name'])) {
            $shipping['name'] = $shippingTranslations[$dispatchId]['dispatch_name'];
            $shipping['dispatch_name'] = $shippingTranslations[$dispatchId]['dispatch_name'];
        }

        return $shipping;
    }

    /**
     * @param array $paymentMethod
     * @param Shop $shop
     * @return array
     */
    private function translatePayment(array $paymentMethod, Shop $shop): array
    {
        $paymentTranslations = $this->translator
            ->readWithFallback(
                $shop->getId(),
                $this->extractFallbackId($shop),
                'config_payment'
            );

        $paymentId = $paymentMethod['id'];

        if (isset($paymentTranslations[$paymentId]['description'])) {
            $paymentMethod['description'] = $paymentTranslations[$paymentId]['description'];
        }

        return $paymentMethod;
    }

    /**
     * @param Shop $shop
     * @return int
     */
    private function extractFallbackId(Shop $shop): int
    {
        $fallbackId = 0;

        if ($shop->getFallback()) {
            return $shop
                ->getFallback()
                ->getId();
        }

        return $fallbackId;
    }

    /**
     * @return Shop
     */
    private function getCurrentShop(): Shop
    {
        /** @var Shop $shop */
        return $this
            ->container
            ->get('shop');
    }
}
