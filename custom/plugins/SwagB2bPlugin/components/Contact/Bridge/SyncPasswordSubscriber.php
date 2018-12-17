<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Hook_HookArgs;
use sAdmin;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class SyncPasswordSubscriber implements SubscriberInterface
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var ContactPasswordProvider
     */
    private $passwordProvider;

    /**
     * @var sAdmin
     */
    private $admin;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param ContactRepository $contactRepository
     * @param ContactPasswordProvider $passwordProvider
     * @param sAdmin $admin
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ContactRepository $contactRepository,
        ContactPasswordProvider $passwordProvider,
        sAdmin $admin,
        AuthenticationService $authenticationService
    ) {
        $this->contactRepository = $contactRepository;
        $this->passwordProvider = $passwordProvider;
        $this->admin = $admin;
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Account::resetPasswordAction::after' => 'syncContactDataAfterReset',
        ];
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     */
    public function syncContactDataAfterReset(Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Account $subject */
        $subject = $args->getSubject();

        if (!$this->isValid($args)) {
            return;
        }

        $email = $subject->Request()->getParam('email');

        if (!$this->contactRepository->hasContactForEmail($email)) {
            return;
        };

        $password = $subject->Request()->getParam('password');

        $contact = $this->contactRepository->insecureFetchOneByEmail($email);

        $this->passwordProvider->setPassword($contact, $password);

        $ownershipContext = $this->authenticationService->getIdentityByAuthId($contact->authId)->getOwnershipContext();
        $this->contactRepository->updateContact($contact, $ownershipContext);

        $this->admin->sLogin();
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     * @return bool
     */
    protected function isValid(Enlight_Hook_HookArgs $args): bool
    {
        /** @var \Shopware_Controllers_Frontend_Account $subject */
        $subject = $args->getSubject();
        $view = $subject->View();
        $request = $subject->Request();

        $invalidToken = $view->getAssign('invalidToken');
        $errorMessage = $view->getAssign('sErrorMessages');
        $errorFlag = $view->getAssign('sErrorFlag');

        return $request->isPost()
            && !$invalidToken
            && !$errorMessage
            && !$errorFlag;
    }
}
