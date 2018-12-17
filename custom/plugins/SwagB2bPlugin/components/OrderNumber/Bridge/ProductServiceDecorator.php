<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Bridge;

use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

class ProductServiceDecorator implements ProductServiceInterface
{
    /**
     * @var ProductServiceInterface
     */
    private $coreService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param ProductServiceInterface $coreService
     * @param AuthenticationService $authenticationService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        ProductServiceInterface $coreService,
        AuthenticationService $authenticationService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->coreService = $coreService;
        $this->authenticationService = $authenticationService;
        $this->orderNumberRepository = $orderNumberRepository;
    }

    /**
     * @param array $numbers
     * @param ProductContextInterface $context
     * @return Product[]
     */
    public function getList(array $numbers, ProductContextInterface $context): array
    {
        if ($this->authenticationService->isB2b()) {
            $ownerShipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
            $numbers = $this->orderNumberRepository->fetchOriginalOrderNumbers($numbers, $ownerShipContext);
        }

        return $this->coreService->getList($numbers, $context);
    }

    /**
     * @param $number
     * @param ProductContextInterface $context
     * @return Product
     */
    public function get($number, ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }
}
