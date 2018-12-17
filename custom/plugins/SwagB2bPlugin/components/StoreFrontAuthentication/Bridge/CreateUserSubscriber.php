<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\CredentialsBuilder;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginService;
use Shopware\Components\Model\ModelManager;

class CreateUserSubscriber implements SubscriberInterface
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
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param LoginService $loginService
     * @param UserRepository $userRepository
     * @param CredentialsBuilder $credentialsBuilder
     * @param ModelManager $modelManager
     */
    public function __construct(
        LoginService $loginService,
        UserRepository $userRepository,
        CredentialsBuilder $credentialsBuilder,
        ModelManager $modelManager
    ) {
        $this->loginService = $loginService;
        $this->userRepository = $userRepository;
        $this->credentialsBuilder = $credentialsBuilder;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Account::passwordAction::before' => 'syncUserData',
        ];
    }

    public function syncUserData(Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Account $subject */
        $subject = $args->getSubject();

        if (!$subject->Request()->isPost()) {
            return;
        }

        $credentials = $this->credentialsBuilder->createCredentials($subject->Request()->getParams());

        try {
            $userData = $this->loginService
                ->getUserDataBeforeLogin($credentials);
        } catch (NotFoundException $e) {
            return;
        }

        $this->userRepository->syncUser($userData);

        $this->modelManager->flush();
    }
}
