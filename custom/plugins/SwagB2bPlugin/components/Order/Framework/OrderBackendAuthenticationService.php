<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OrderBackendAuthenticationService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(AuthenticationService $authenticationService, OrderRepositoryInterface $orderRepository)
    {
        $this->authenticationService = $authenticationService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param int $orderContextId
     * @return Identity
     */
    public function getIdentityByOrderContextId(int $orderContextId): Identity
    {
        $authId = $this->orderRepository->fetchAuthIdFromOrderById($orderContextId);
        $identity = $this->authenticationService->getIdentityByAuthId($authId);

        return $identity;
    }
}
