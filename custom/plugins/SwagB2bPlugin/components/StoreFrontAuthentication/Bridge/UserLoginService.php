<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\IdentityChainIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginService;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginServiceInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UserLoginService implements UserLoginServiceInterface
{
    /**
     * @var IdentityChainIdentityLoader
     */
    private $chainIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $repository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param IdentityChainIdentityLoader $chainIdentityLoader
     * @param LoginContextService $loginContextService
     * @param StoreFrontAuthenticationRepository $repository
     * @param UserRepository $userRepository
     * @param LoginService $loginService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        IdentityChainIdentityLoader $chainIdentityLoader,
        LoginContextService $loginContextService,
        StoreFrontAuthenticationRepository $repository,
        UserRepository $userRepository,
        LoginService $loginService,
        AuthenticationService $authenticationService
    ) {
        $this->chainIdentityLoader = $chainIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->loginService = $loginService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public function loginByMail(string $email)
    {
        $currentUserCredentials = $this->authenticationService->getIdentity()->getLoginCredentials();

        $identity = $this->chainIdentityLoader
            ->fetchIdentityByEmail($email, $this->loginContextService);

        $this->userRepository
            ->syncUser(
                $this->loginService->transformIdentityToUserData($identity)
            );

        $loginCredentials = $identity->getLoginCredentials();

        $admin = Shopware()->Modules()->Admin();
        $admin->logout();

        Shopware()->Front()->Request()->setPost([
            'email' => $loginCredentials->email,
            'passwordMD5' => $loginCredentials->password,
        ]);

        $status = $admin->sLogin(true);

        if (!isset($status['sErrorMessages'])) {
            return;
        }

        Shopware()->Front()->Request()->setPost([
            'email' => $currentUserCredentials->email,
            'passwordMD5' => $currentUserCredentials->password,
        ]);

        $admin->sLogin(true);

        if (isset($status['sErrorFlag'])) {
            $field = array_search(true, $status['sErrorFlag'], true);
        } else {
            $field = 'Email';
        }

        $violation = new ConstraintViolation(
            $status['sErrorMessages'][0],
            $status['sErrorMessages'][0],
            [],
            '',
            $field,
            $field,
            '',
            null
        );

        $violationList = new ConstraintViolationList([$violation]);

        throw new ValidationException($identity->getEntity(), $violationList, 'Validation violations detected, can not proceed:', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function loginByAuthId(int $authId)
    {
        $authentication = $this->repository
            ->fetchAuthenticationById($authId);

        $identity = $this->chainIdentityLoader
            ->fetchIdentityByAuthentication($authentication, $this->loginContextService);

        $this->loginByMail($identity->getLoginCredentials()->email);
    }
}
