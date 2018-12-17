<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Offer\Framework\OfferLineItemReferenceEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\TaxProviderInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class TaxProvider implements TaxProviderInterface
{
    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @var ContextService
     */
    private $contextService;

    /**
     * @var ListProductServiceInterface
     */
    private $productService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param Shopware_Components_Config $config
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     * @param ContextService $contextService
     * @param ListProductServiceInterface $productService
     * @param ModelManager $modelManager
     */
    public function __construct(
        Shopware_Components_Config $config,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        ContextService $contextService,
        ListProductServiceInterface $productService,
        ModelManager $modelManager
    ) {
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->contextService = $contextService;
        $this->productService = $productService;
        $this->config = $config;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $lineItemListId
     * @param OwnershipContext $ownershipContext
     * @return float
     */
    public function getDiscountTax(int $lineItemListId, OwnershipContext $ownershipContext): float
    {
        $taxAutoMode = $this->config->get('sTAXAUTOMODE');

        if (!$taxAutoMode) {
            $discountValue = $this->config->get('sDISCOUNTTAX');

            return (($discountValue / 100) + 1);
        }

        $references = $this->offerLineItemReferenceRepository->fetchAllForList($lineItemListId, $ownershipContext);

        $shopContext = $this->contextService->createShopContext($this->getShopId());

        $taxes = array_reduce(
            $references,
            function ($taxes, OfferLineItemReferenceEntity $reference) use ($shopContext) {
                $product = @$this->productService->get($reference->referenceNumber, $shopContext);

                if (!$product) {
                    return $taxes;
                }

                $taxes[] = $product->getTax()->getTax();

                return $taxes;
            }
        );

        if (!$taxes) {
            return 1.19;
        }

        return (max($taxes) / 100) + 1;
    }

    /**
     * @param OfferLineItemReferenceEntity $reference
     * @return float
     */
    public function getProductTax(OfferLineItemReferenceEntity $reference): float
    {
        $shopContext = $this->contextService->createShopContext($this->getShopId());

        $product = @$this->productService->get($reference->referenceNumber, $shopContext);

        return ($product->getTax()->getTax() / 100) +1;
    }

    /**
     * @return int
     */
    private function getShopId(): int
    {
        try {
            $shop = Shopware()->Container()->get('shop');

            if (!$shop) {
                throw new ServiceNotFoundException('Unable to load shop through container');
            }

            return $shop->getId();
        } catch (ServiceNotFoundException $e) {
            return $this->modelManager
                ->getRepository(Shop::class)
                ->getDefault()
                ->getId();
        }
    }
}
