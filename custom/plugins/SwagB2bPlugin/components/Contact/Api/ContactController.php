<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Api;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactCrudService;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContactController
{
    /**
     * @var ContactCrudService
     */
    private $contactCrudService;

    /**
     * @var ContactRepository
     */
    private $contactSearchRepository;

    /**
     * @var GridHelper
     */
    private $contactGridHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param ContactRepository $contactSearchRepository
     * @param ContactCrudService $contactCrudService
     * @param GridHelper $contactGridHelper
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        ContactRepository $contactSearchRepository,
        ContactCrudService $contactCrudService,
        GridHelper $contactGridHelper,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->contactCrudService = $contactCrudService;
        $this->contactSearchRepository = $contactSearchRepository;
        $this->contactGridHelper = $contactGridHelper;
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return array
     */
    public function getListAction(string $debtorEmail, Request $request): array
    {
        $search = new ContactSearchStruct();
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $this->contactGridHelper
            ->extractSearchDataInRestApi($request, $search);

        $contacts = $this->contactSearchRepository
            ->fetchList($ownershipContext, $search);

        $totalCount = $this->contactSearchRepository
            ->fetchTotalCount($ownershipContext, $search);

        return ['success' => true, 'contacts' => $contacts, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $contactId
     * @return array
     */
    public function getAction(string $debtorEmail, int $contactId): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $contact = $this->contactSearchRepository
            ->fetchOneById($contactId, $ownershipContext);

        return ['success' => true, 'contact' => $contact];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $debtorIdentity = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $post = $request->getPost();
        $post['passwordNew'] = $request->getParam('password');
        $post['passwordRepeat'] = $request->getParam('password');

        $aclGrantContext = $this->extractGrantContext($request, $debtorIdentity->getOwnershipContext());

        $post['contextOwnerId'] = $debtorIdentity->getOwnershipContext()->contextOwnerId;

        $post = $this->extendRequestDataWithPasswordActivation($post, $request);

        $serviceRequest = $this->contactCrudService
            ->createNewRecordRequest($post);

        $contact = $this->contactCrudService
            ->create($serviceRequest, $debtorIdentity, $aclGrantContext);

        return ['success' => true, 'contact' => $contact];
    }

    /**
     * @param string $debtorEmail
     * @param int $contactId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $contactId, Request $request): array
    {
        $post = $request->getPost();
        $post['id'] = $contactId;

        $post = $this->extendRequestDataWithPasswordActivation($post, $request);

        if ($request->getParam('password')) {
            $post['passwordNew'] = $request->getParam('password');
            $post['passwordRepeat'] = $request->getParam('password');
        }

        $debtorIdentity = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService);
        $post['contextOwnerId'] = $debtorIdentity->getOwnershipContext()->contextOwnerId;

        $serviceRequest = $this->contactCrudService
            ->createExistingRecordRequest($post);

        $contact = $this->contactCrudService
            ->update($serviceRequest, $debtorIdentity->getOwnershipContext());

        return ['success' => true, 'contact' => $contact];
    }

    /**
     * @param string $debtorEmail
     * @param int $contactId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $contactId): array
    {
        $ownershipContext = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService)->getOwnershipContext();
        $contact = $this->contactCrudService->remove($contactId, $ownershipContext);

        return ['success' => true, 'contact' => $contact];
    }

    /**
     * @param array $post
     * @param Request $request
     * @return array
     */
    protected function extendRequestDataWithPasswordActivation(array $post, Request $request): array
    {
        if ($request->getParam('passwordActivation')) {
            $post['passwordNew'] = $post['passwordRepeat'] = sha1((string) random_int(0, PHP_INT_MAX));
        }

        return $post;
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @throws \InvalidArgumentException
     * @return AclGrantContext
     */
    protected function extractGrantContext(Request $request, OwnershipContext $ownershipContext): AclGrantContext
    {
        $grantContextIdentifier = $request->requireParam('grantContextIdentifier');

        return $this->grantContextProviderChain->fetchOneByIdentifier($grantContextIdentifier, $ownershipContext);
    }
}
