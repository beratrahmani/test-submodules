<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Bridge;

use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

class ListProductServiceDecorator implements ListProductServiceInterface
{
    /**
     * @var ListProductServiceInterface
     */
    private $coreService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $numberRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param ListProductServiceInterface $coreService
     * @param OrderNumberRepositoryInterface $numberRepository
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ListProductServiceInterface $coreService,
        OrderNumberRepositoryInterface $numberRepository,
        AuthenticationService $authenticationService
    ) {
        $this->coreService = $coreService;
        $this->numberRepository = $numberRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param array $numbers
     * @param ProductContextInterface $context
     * @return ListProduct[]
     */
    public function getList(array $numbers, ProductContextInterface $context): array
    {
        if ($this->authenticationService->isB2b()) {
            $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();
            $numbers = $this->numberRepository->fetchOriginalOrderNumbers($numbers, $ownershipContext);
        }

        $products = $this->coreService->getList($numbers, $context);

        if ($this->authenticationService->isB2b()) {
            $products = $this->extendProducts($products);
        }

        return $products;
    }

    /**
     * @internal
     * @param ListProduct[] $products
     * @return ListProduct[]
     */
    protected function extendProducts(array $products): array
    {
        $ownerShip = $this->authenticationService->getIdentity()->getOwnershipContext();
        $customOrderNumbers = $this->numberRepository->fetchCustomOrderNumbers(array_keys($products), $ownerShip);

        foreach ($customOrderNumbers as $orderNumber => $customOrderNumber) {
            $product = $products[$orderNumber];

            $attribute = new Attribute([
                'custom_ordernumber' => $customOrderNumber,
            ]);
            $product->addAttribute('b2b_ordernumber', $attribute);
        }

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }
}
