<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Frontend;

use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactAddressDefaultController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    public function __construct(
        AuthenticationService $authenticationService,
        AddressRepositoryInterface $addressRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper
    ) {
        $this->authenticationService = $authenticationService;
        $this->addressRepository = $addressRepository;
        $this->contactRepository = $contactRepository;
        $this->gridHelper = $gridHelper;
    }

    public function gridAction(Request $request): array
    {
        $contactId = (int) $request->requireParam('contactId');
        $addressType = $request->requireParam('type');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        $searchStruct = new AddressSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $addresses = $this->addressRepository
            ->fetchList($addressType, $ownershipContext, $searchStruct);

        $count = $this->addressRepository
            ->fetchTotalCount($addressType, $ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper->getGridState($request, $searchStruct, $addresses, $maxPage, $currentPage),
            'type' => $addressType,
            'addresses' => $addresses,
            'contact' => $contact,
        ];
    }

    /**
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     * @throws \InvalidArgumentException
     */
    public function defaultAction(Request $request)
    {
        $contactId = (int) $request->requireParam('contactId');
        $type = $request->requireParam('type');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        if ($type !== 'billing' && $type !== 'shipping') {
            throw new \InvalidArgumentException('The argument type must be billing or shipping');
        }

        $request->checkPost(
            'grid',
            [
                'contactId' => $contactId,
                'type' => $type,
            ],
            'b2bcontactaddressdefault'
        );

        $post = $request->getPost();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        if ($type === 'billing') {
            $contact->defaultBillingAddressId = (int) $post['addressId'];
        } else {
            $contact->defaultShippingAddressId = (int) $post['addressId'];
        }

        $this->contactRepository->updateDefaultAddresses($contact, $ownershipContext);

        throw new B2bControllerForwardException($type, null, null, ['contactId' => $post['contactId'], 'type' => $post['type']]);
    }
}
