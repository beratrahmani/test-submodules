<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Account\Framework\AccountServiceInterface;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginService;
use Shopware\Components\Password\Manager;

class AccountService implements AccountServiceInterface
{
    /**
     * @var Manager
     */
    private $passwordEncoder;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var LoginService
     */
    private $loginService;

    /**
     * @param Manager $passwordEncoder
     * @param AuthenticationService $authenticationService
     * @param Connection $connection
     * @param SessionStorageInterface $sessionStorage
     * @param LoginService $loginService
     */
    public function __construct(
        Manager $passwordEncoder,
        AuthenticationService $authenticationService,
        Connection $connection,
        SessionStorageInterface $sessionStorage,
        LoginService $loginService
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->authenticationService = $authenticationService;
        $this->connection = $connection;
        $this->sessionStorage = $sessionStorage;
        $this->loginService = $loginService;
    }

    /**
     * {@inheritdoc}
     */
    public function savePassword(string $currentPassword, string $newPassword)
    {
        $identity = $this->authenticationService->getIdentity();

        $loginCredentials = $identity->getLoginCredentials();

        if (!$this->passwordEncoder->isPasswordValid($currentPassword, $loginCredentials->password, $loginCredentials->encoder)) {
            throw new \InvalidArgumentException('wrong current password');
        }

        $encodedPassword = $this->passwordEncoder->encodePassword($newPassword, $loginCredentials->encoder);

        $this->connection->update(
            $identity->getTableName(),
            ['password' => $encodedPassword],
            ['id' => $identity->getId()]
        );

        $this->loginService->setIdentityFor($identity->getLoginCredentials()->email);

        if ($identity->getTableName() === 's_user') {
            $this->sessionStorage->set('sUserPassword', $encodedPassword);
        }
    }
}
