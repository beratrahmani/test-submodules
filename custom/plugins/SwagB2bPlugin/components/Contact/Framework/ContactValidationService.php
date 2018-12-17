<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactValidationService
{
    const CAUSE_MAIL_CHANGE = 'MailChange';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param ContactRepository $contactRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        ContactRepository $contactRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ContactEntity $contact
     * @return Validator
     */
    public function createInsertValidation(ContactEntity $contact): Validator
    {
        return $this->createCrudValidation($contact)
            ->validateThat('id', $contact->id)
                ->isBlank()

            ->validateThat('email', $contact->email)
                ->isUnique(function () use ($contact) {
                    try {
                        $this->contactRepository->insecureFetchOneByEmail($contact->email);
                    } catch (NotFoundException $e) {
                        return $this->userRepository->isMailAvailable($contact->email);
                    }

                    return false;
                })
                ->isNotBlank()
                ->isEmail()

            ->getValidator($this->validator);
    }

    /**
     * @param ContactEntity $contact
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createUpdateValidation(ContactEntity $contact, OwnershipContext $ownershipContext): Validator
    {
        return $this->createCrudValidation($contact)
            ->validateThat('id', $contact->id)
                ->isNotBlank()

            ->validateThat('email', $contact->email)
                ->isNotBlank()
                ->isEmail()
                ->isUnique(
                    function () use ($contact, $ownershipContext) {
                        try {
                            $mailContact = $this->contactRepository->fetchOneById($contact->id, $ownershipContext);
                        } catch (NotFoundException $e) {
                            return false;
                        }

                        if ($mailContact->email === $contact->email) {
                            return true;
                        }

                        try {
                            $this->contactRepository->insecureFetchOneByEmail($contact->email);

                            return false;
                        } catch (NotFoundException $e) {
                        }

                        return $this->userRepository->isMailAvailable($contact->email);
                    }
                )

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param ContactEntity $contact
     * @return ValidationBuilder
     */
    protected function createCrudValidation(ContactEntity $contact): ValidationBuilder
    {
        return $this->validationBuilder

            ->validateThat('password', $contact->password)
                ->isNotBlank()
                ->isString()

            ->validateThat('encoder', $contact->encoder)
                ->isNotBlank()
                ->isString()

            ->validateThat('active', $contact->active)
                ->isBool()

            ->validateThat('language', $contact->language)
                ->isInt()

            ->validateThat('title', $contact->title)
                ->isString()

            ->validateThat('salutation', $contact->salutation)
                ->isNotBlank()
                ->isString()

            ->validateThat('firstName', $contact->firstName)
                ->isNotBlank()
                ->isString()

            ->validateThat('lastName', $contact->lastName)
                ->isNotBlank()
                ->isString()

            ->validateThat('department', $contact->department)
                ->isString()

            ->validateThat('contextOwnerId', $contact->contextOwnerId)
                ->isNotBlank();
    }
}
