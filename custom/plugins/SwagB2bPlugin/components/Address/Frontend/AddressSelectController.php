<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Frontend;

use Shopware\B2B\Address\Framework\AddressCheckoutServiceInterface;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class AddressSelectController
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AddressCheckoutServiceInterface
     */
    private $addressCheckoutService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     * @param AddressRepositoryInterface $addressRepository
     * @param GridHelper $gridHelper
     * @param AddressCheckoutServiceInterface $addressCheckoutService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AddressRepositoryInterface $addressRepository,
        GridHelper $gridHelper,
        AddressCheckoutServiceInterface $addressCheckoutService
    ) {
        $this->authenticationService = $authenticationService;
        $this->addressRepository = $addressRepository;
        $this->gridHelper = $gridHelper;
        $this->addressCheckoutService = $addressCheckoutService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $addressType = $request->requireParam('type');
        $selectedAddressId = $request->requireParam('selectedId');

        $searchStruct = new AddressSearchStruct();
        $ownership = $this->authenticationService->getIdentity()->getOwnershipContext();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $addresses = $this->addressRepository
            ->fetchList($addressType, $ownership, $searchStruct);

        $totalCount = $this->addressRepository
            ->fetchTotalCount($addressType, $ownership, $searchStruct);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $addresses, $maxPage, $currentPage);

        return [
            'gridState' => $gridState,
            'addressType' => $addressType,
            'selectedAddressId' => $selectedAddressId,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function selectAction(Request $request): array
    {
        $request->checkPost();

        $type = $request->requireParam('type');
        $addressId = (int) $request->requireParam('addressId');
        $identity = $this->authenticationService->getIdentity();

        $address = $this->addressRepository
            ->fetchOneById($addressId, $identity, $type);

        $this->addressCheckoutService
            ->updateCheckoutAddress($type, $address);

        return [];
    }
}
