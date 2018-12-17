<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsBuilder;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginService;

class LoginSubscriber implements SubscriberInterface
{
    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var CredentialsBuilder
     */
    private $credentialsBuilder;

    /**
     * @param LoginService $loginService
     * @param UserRepository $userRepository
     * @param CredentialsBuilder $credentialsBuilder
     */
    public function __construct(
        LoginService $loginService,
        UserRepository $userRepository,
        CredentialsBuilder $credentialsBuilder
    ) {
        $this->loginService = $loginService;
        $this->userRepository = $userRepository;
        $this->credentialsBuilder = $credentialsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_Start' => ['syncUserData', 1],
            'Shopware_Modules_Admin_Login_Successful' => 'storeIdentity',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function syncUserData(Enlight_Event_EventArgs $args)
    {
        $credentials = $this->credentialsBuilder->createCredentials($args->get('post'));

        try {
            $userData = $this->loginService
                ->getUserDataBeforeLogin($credentials);
        } catch (NotFoundException $e) {
            return;
        }

        $this->userRepository->syncUser($userData);
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function storeIdentity(Enlight_Event_EventArgs $args)
    {
        $email = $args->get('email');

        try {
            $identity = $this->loginService->getIdentityByEmail($email);
        } catch (NotFoundException $e) {
            return;
        }

        $this->userRepository->checkAddress(
            $identity->getMainBillingAddress()->id,
            'billing',
            $identity->getOwnershipContext()
        );

        $this->userRepository->checkAddress(
            $identity->getMainShippingAddress()->id,
            'shipping',
            $identity->getOwnershipContext()
        );

        $this->loginService
            ->setIdentityFor($email);
    }
}
