<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Frontend;

use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Company\Frontend\CompanyFilterResolver;
use Shopware\B2B\Contact\Framework\ContactCrudService;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContactController
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $contactGridHelper;

    /**
     * @var ContactCrudService
     */
    private $contactCrudService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CompanyFilterResolver
     */
    private $companyFilterResolver;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    public function __construct(
        AuthenticationService $authenticationService,
        ContactRepository $contactRepository,
        ContactCrudService $contactCrudService,
        GridHelper $contactGridHelper,
        CompanyFilterResolver $companyFilterResolver,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactRepository = $contactRepository;
        $this->contactCrudService = $contactCrudService;
        $this->contactGridHelper = $contactGridHelper;
        $this->companyFilterResolver = $companyFilterResolver;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    public function indexAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $searchStruct = $this->createSearchStruct($request, $ownershipContext);

        $contacts = $this->contactRepository
            ->fetchList($ownershipContext, $searchStruct);

        $totalCount = $this->contactRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->contactGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $contactGridState = $this->contactGridHelper
            ->getGridState($request, $searchStruct, $contacts, $maxPage, $currentPage);

        return array_merge(
            [
                'gridState' => $contactGridState,
                'grantContext' => $searchStruct->aclGrantContext->getIdentifier(),
            ],
            $this->companyFilterResolver->getViewFilterVariables($searchStruct)
        );
    }

    public function detailAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        return ['contact' => $this->contactRepository->fetchOneById($id, $ownershipContext)];
    }

    public function editAction(Request $request): array
    {
        $id = (int) $request->requireParam('id');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $validationResponse = $this->contactGridHelper->getValidationResponse('contact');

        return array_merge([
            'contact' => $this->contactRepository->fetchOneById($id, $ownershipContext),
        ], $validationResponse);
    }

    /**
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $post = $request->getPost();
        $contact = $this->contactRepository->fetchOneById((int) $post['id'], $ownershipContext);

        $post['encoder'] = $contact->encoder;
        $post['contextOwnerId'] = $contact->contextOwnerId;
        $post['password'] = $contact->password;

        $post = $this->extendRequestDataWithPasswordActivation($post, $request);

        $serviceRequest = $this->contactCrudService
            ->createExistingRecordRequest($post);

        try {
            $contact = $this->contactCrudService->update($serviceRequest, $ownershipContext);
        } catch (ValidationException $e) {
            $this->contactGridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('edit', null, null, ['id' => $contact->id]);
    }

    /**
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $id = (int) $request->requireParam('id');

        $this->contactCrudService->remove($id, $ownershipContext);

        throw new EmptyForwardException();
    }

    public function newAction(Request $request): array
    {
        $validationResponse = $this->contactGridHelper->getValidationResponse('contact');

        $viewData = [
            'isNew' => true,
            'grantContext' => $request->requireParam('grantContext'),
        ];

        if ($request->getParam('createMultiple')) {
            $viewData['createMultiple'] = true;
        }

        return array_merge($viewData, $validationResponse);
    }

    /**
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $identity = $this->authenticationService->getIdentity();
        $post = $this->extendRequestDataFromIdentity($post, $identity);

        $post = $this->extendRequestDataWithPasswordActivation($post, $request);

        $serviceRequest = $this->contactCrudService->createNewRecordRequest($post);

        $grantContext = $this->grantContextProviderChain
            ->fetchOneByIdentifier($request->requireParam('grantContext'), $identity->getOwnershipContext());

        $viewData = [];
        $viewData['grantContext'] = $request->requireParam('grantContext');
        if (array_key_exists('createAdditionalContact', $post)) {
            $viewData = ['createMultiple' => true];
        }

        try {
            $contact = $this->contactCrudService->create($serviceRequest, $identity, $grantContext);

            if ($post['createAdditionalContact']) {
                throw new B2bControllerForwardException('new', null, null, $viewData);
            }
        } catch (ValidationException $e) {
            $this->contactGridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new', null, null, $viewData);
        }

        throw new B2bControllerForwardException(
            'detail',
            null,
            null,
            ['id' => $contact->id]
        );
    }

    /**
     * @internal
     */
    protected function extendRequestDataFromIdentity(array $post, Identity $identity): array
    {
        $post['contextOwnerId'] = $identity->getOwnershipContext()->contextOwnerId;
        $post['language'] = $identity->getPostalSettings()->language;

        return $post;
    }

    protected function extendRequestDataWithPasswordActivation(array $post, Request $request): array
    {
        if ($request->getParam('passwordActivation')) {
            $post['passwordNew'] = $post['passwordRepeat'] = sha1((string) random_int(0, PHP_INT_MAX));
        }

        return $post;
    }

    /**
     * @internal
     */
    protected function createSearchStruct(Request $request, OwnershipContext $ownershipContext): ContactSearchStruct
    {
        $searchStruct = new ContactSearchStruct();

        $this->companyFilterResolver
            ->extractGrantContextFromRequest($request, $searchStruct, $ownershipContext);

        $this->contactGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        return $searchStruct;
    }
}
