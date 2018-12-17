<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Frontend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Contact\Framework\ContactCrudService;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationEntity;
use Shopware\B2B\Contact\Framework\ContactPasswordActivationServiceInterface;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactPasswordActivationController
{
    /**
     * @var ContactPasswordActivationServiceInterface
     */
    private $activationService;

    /**
     * @var ContactCrudService
     */
    private $contactCrudService;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param ContactPasswordActivationServiceInterface $activationService
     * @param ContactCrudService $contactCrudService
     * @param ContactRepository $contactRepository
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ContactPasswordActivationServiceInterface $activationService,
        ContactCrudService $contactCrudService,
        ContactRepository $contactRepository,
        AuthenticationService $authenticationService
    ) {
        $this->activationService = $activationService;
        $this->contactCrudService = $contactCrudService;
        $this->contactRepository = $contactRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $b2bErrors = [];
        $hash = $request->requireParam('hash');

        $activation = $this->activationService->getValidActivationByHash($hash);

        if ($activation && $request->isPost()) {
            try {
                $this->activatePassword($activation, $request);
            } catch (NotFoundException $e) {
                $b2bErrors[] = 'PasswordActivationEmailNotFound';
            } catch (ValidationException $e) {
                $b2bErrors[] = 'PasswordActivationPasswordsNotEqual';
            } catch (\InvalidArgumentException $e) {
                $b2bErrors[] = 'PasswordActivationPasswordsBlank';
            }
        }

        if (!$activation) {
            $b2bErrors[] = 'PasswordActivationHashIsInvalid';
        }

        return ['activation' => $activation, 'hash' => $hash, 'b2bErrors' => $b2bErrors];
    }

    /**
     * @internal
     * @param ContactPasswordActivationEntity $activation
     * @param Request $request
     * @return \Shopware\B2B\Contact\Framework\ContactPasswordActivationEntity
     */
    protected function activatePassword(
        ContactPasswordActivationEntity $activation,
        Request $request
    ): ContactPasswordActivationEntity {
        $contact = $this->contactRepository->insecurefetchOneByEmail($activation->data->email);
        $requestArray = $contact->toArray();
        $ownershipContext = $this->authenticationService->getIdentityByAuthId($contact->authId)->getOwnershipContext();

        $requestArray['passwordNew'] = $request->requireParam('passwordNew');
        $requestArray['passwordRepeat'] = $request->requireParam('passwordRepeat');

        $crudRequest = $this->contactCrudService
            ->createExistingRecordRequest($requestArray);

        $this->contactCrudService->update($crudRequest, $ownershipContext);

        return $this->activationService->removeActivation($activation);
    }
}
