<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Address\Framework\AddressSearchStruct;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactAddressController
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
     * @var AclRepository
     */
    private $addressAclRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    public function __construct(
        AuthenticationService $authenticationService,
        AddressRepositoryInterface $addressRepository,
        AclRepository $addressAclRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService
    ) {
        $this->authenticationService = $authenticationService;
        $this->addressRepository = $addressRepository;
        $this->addressAclRepository = $addressAclRepository;
        $this->contactRepository = $contactRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
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

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->addressAclRepository, $contact, $addresses);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->addressAclRepository, $ownershipContext, $addresses);

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
    public function assignAction(Request $request)
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
            'b2bcontactaddress'
        );

        $post = $request->getPost();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->addressAclRepository->allow(
                $contact,
                (int) $post['addressId'],
                (bool) $request->getParam('grantable', false)
            );
        } else {
            $this->addressAclRepository->deny($contact, (int) $post['addressId']);
        }

        throw new B2bControllerForwardException(
            'grid',
            null,
            null,
            [
                'contactId' => $contactId,
                'type' => $type,
            ]
        );
    }
}
