<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriterInterface;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ContactCrudService extends AbstractCrudService
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var ContactValidationService
     */
    private $validationService;

    /**
     * @var AclRepository
     */
    private $aclAddressRepository;

    /**
     * @var ContactPasswordProviderInterface
     */
    private $passwordProvider;

    /**
     * @var ContactPasswordActivationServiceInterface
     */
    private $contactPasswordActivationService;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var AclAccessWriterInterface[]
     */
    private $aclAccessWriters;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param ContactRepository $contactRepository
     * @param ContactValidationService $validationService
     * @param AclRepository $aclAddressRepository
     * @param ContactPasswordProviderInterface $passwordProvider
     * @param ContactPasswordActivationServiceInterface $contactPasswordActivationService
     * @param LoginContextService $loginContextService
     * @param AclAccessWriterInterface[] $aclAccessWriters
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        ContactRepository $contactRepository,
        ContactValidationService $validationService,
        AclRepository $aclAddressRepository,
        ContactPasswordProviderInterface $passwordProvider,
        ContactPasswordActivationServiceInterface $contactPasswordActivationService,
        LoginContextService $loginContextService,
        array $aclAccessWriters,
        UserRepositoryInterface $userRepository
    ) {
        $this->contactRepository = $contactRepository;
        $this->validationService = $validationService;
        $this->aclAddressRepository = $aclAddressRepository;
        $this->passwordProvider = $passwordProvider;
        $this->contactPasswordActivationService = $contactPasswordActivationService;
        $this->loginContextService = $loginContextService;
        $this->aclAccessWriters = $aclAccessWriters;
        $this->userRepository = $userRepository;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'passwordNew',
                'passwordRepeat',
                'passwordActivation',
                'password',
                'encoder',
                'email',
                'active',
                'language',
                'title',
                'salutation',
                'firstName',
                'lastName',
                'contextOwnerId',
                'department',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'id',
                'passwordNew',
                'passwordRepeat',
                'passwordActivation',
                'password',
                'encoder',
                'email',
                'active',
                'language',
                'title',
                'salutation',
                'firstName',
                'lastName',
                'contextOwnerId',
                'department',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param Identity $identity
     * @param AclGrantContext $grantContext
     * @throws \Shopware\B2B\Common\Validator\ValidationException
     * @return ContactEntity
     */
    public function create(
        CrudServiceRequest $request,
        Identity $identity,
        AclGrantContext $grantContext
    ): ContactEntity {
        $data = $request->getFilteredData();

        $contact = new ContactEntity();

        $contact->setData($data);

        $this->checkPassword($contact, $request, true);
        $this->passwordProvider->setPassword($contact, $request->requireParam('passwordNew'));

        $validation = $this->validationService
            ->createInsertValidation($contact);

        $this->testValidation($contact, $validation);

        $this->contactRepository
            ->addContact($contact, $identity->getOwnershipContext());

        $contact = $this->contactRepository
            ->fetchOneByEmail($contact->email, $identity->getOwnershipContext());

        if ($request->hasValueForParam('passwordActivation')) {
            $this->contactPasswordActivationService->sendPasswordActivationEmail($contact);
        }

        $contact->authId = $this->loginContextService
            ->getAuthId(ContactRepository::class, $contact->id, $contact->contextOwnerId);

        $this->contactRepository
            ->setAuthId($contact->id, $contact->authId, $identity->getOwnershipContext());

        $this->aclAddressRepository->allowAll(
            $contact,
            [
                $identity->getMainShippingAddress()->id,
                $identity->getMainBillingAddress()->id,
            ]
        );

        foreach ($this->aclAccessWriters as $aclAccessWriter) {
            $aclAccessWriter->addNewSubject(
                $identity->getOwnershipContext(),
                $grantContext,
                $contact->id,
                true
            );
        }

        return $contact;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Validator\ValidationException
     * @return ContactEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext): ContactEntity
    {
        $data = $request->getFilteredData();
        $contact = new ContactEntity();
        $contact->setData($data);

        $this->checkPassword($contact, $request, false);

        if ($request->hasValueForParam('passwordNew')) {
            $this->passwordProvider->setPassword($contact, $request->requireParam('passwordNew'));
        }

        $validation = $this->validationService
            ->createUpdateValidation($contact, $ownershipContext);

        $this->testValidation($contact, $validation);

        if ($request->hasValueForParam('passwordActivation')) {
            $this->contactPasswordActivationService->sendPasswordActivationEmail($contact);
        }

        $originalContact = $this->contactRepository->fetchOneById($contact->id, $ownershipContext);

        $this->contactRepository
            ->updateContact($contact, $ownershipContext);

        if ($originalContact->email !== $contact->email) {
            $this->userRepository->updateEmail($originalContact->email, $contact->email);
        }

        return $contact;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @return ContactEntity
     */
    public function remove(int $id, OwnershipContext $ownershipContext): ContactEntity
    {
        $contact = $this->contactRepository->fetchOneById($id, $ownershipContext);

        $this->contactRepository
            ->removeContact($contact, $ownershipContext);

        return $contact;
    }

    /**
     * @internal
     * @param ContactEntity $contact
     * @param CrudServiceRequest $request
     * @param bool $required
     */
    protected function checkPassword(ContactEntity $contact, CrudServiceRequest $request, bool $required)
    {
        if ($this->hasAnyPasswordSet($request)) {
            if ($required) {
                $this->throwPasswordRequiredException($contact);
            }

            return;
        }

        if ($this->hasMatchingPasswords($request)) {
            $this->throwPasswordNotMatchingException($contact);
        }
    }

    /**
     * @internal
     * @param ContactEntity $contact
     */
    protected function throwPasswordNotMatchingException(ContactEntity $contact)
    {
        $violation = new ConstraintViolation(
            'The password is not equal to the repeated password.',
            'The password is not equal to the repeated password.',
            [],
            '',
            'Confirm',
            '',
            null
        );

        $violationList = new ConstraintViolationList([$violation]);

        throw new ValidationException(
            $contact,
            $violationList,
            'Validation violations detected, can not proceed:',
            400
        );
    }

    /**
     * @internal
     * @param ContactEntity $contact
     */
    protected function throwPasswordRequiredException(ContactEntity $contact)
    {
        $violation = new ConstraintViolation(
            'A password is required.',
            'A password is required.',
            [],
            '',
            'Password',
            '',
            null
        );

        $violationList = new ConstraintViolationList([$violation]);

        throw new ValidationException(
            $contact,
            $violationList,
            'Validation violations detected, can not proceed:',
            400
        );
    }

    /**
     * @internal
     * @param CrudServiceRequest $request
     * @return bool
     */
    protected function hasAnyPasswordSet(CrudServiceRequest $request): bool
    {
        return !$request->hasValueForParam('passwordNew') && !$request->hasValueForParam('passwordRepeat');
    }

    /**
     * @internal
     * @param CrudServiceRequest $request
     * @return bool
     */
    protected function hasMatchingPasswords(CrudServiceRequest $request): bool
    {
        return !$request->hasValueForParam('passwordNew') ||
            !$request->hasValueForParam('passwordRepeat') ||
            $request->requireParam('passwordNew') !== $request->requireParam('passwordRepeat');
    }
}
