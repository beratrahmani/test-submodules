<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Frontend;

use Shopware\B2B\Account\Framework\AccountImageServiceInterface;
use Shopware\B2B\Account\Framework\AccountServiceInterface;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;

class AccountController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AccountServiceInterface
     */
    private $accountService;

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var AccountImageServiceInterface
     */
    private $accountImageService;

    /**
     * @var AuthStorageAdapterInterface
     */
    private $authStorageAdapter;

    /**
     * @param AuthenticationService $authenticationService
     * @param AccountServiceInterface $accountService
     * @param SessionStorageInterface $sessionStorage
     * @param AccountImageServiceInterface $accountImageService
     * @param AuthStorageAdapterInterface $authStorageAdapter
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AccountServiceInterface $accountService,
        SessionStorageInterface $sessionStorage,
        AccountImageServiceInterface $accountImageService,
        AuthStorageAdapterInterface $authStorageAdapter
    ) {
        $this->authenticationService = $authenticationService;
        $this->accountService = $accountService;
        $this->sessionStorage = $sessionStorage;
        $this->accountImageService = $accountImageService;
        $this->authStorageAdapter = $authStorageAdapter;
    }

    /**
     * @return array
     */
    public function indexAction(): array
    {
        $identity = $this->authenticationService
            ->getIdentity();

        $message = $this->sessionStorage->get('ChangeMessage');
        $this->sessionStorage->set('ChangeMessage', null);

        return [
            'identity' => $identity->getEntity(),
            'avatar' => $identity->getAvatar(),
            'changeMessage' => $message,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function savePasswordAction(Request $request)
    {
        $request->checkPost();

        try {
            $currentPassword = $request->requireParam('currentPassword');
            $password = $request->requireParam('password');
            $passwordConfirm = $request->requireParam('passwordConfirmation');
        } catch (\InvalidArgumentException $e) {
            $this->sessionStorage->set('ChangeMessage', ['error', 'RequiredFieldsEmpty']);
            throw new B2bControllerRedirectException('index', 'b2baccount');
        }

        if ($password !== $passwordConfirm) {
            $this->sessionStorage->set('ChangeMessage', ['error', 'NoPasswordConfirm']);
            throw new B2bControllerRedirectException('index', 'b2baccount');
        }

        try {
            $this->accountService->savePassword($currentPassword, $password);
        } catch (\InvalidArgumentException $e) {
            $this->sessionStorage->set('ChangeMessage', ['error', 'InvalidCurrentPassword']);
            throw new B2bControllerRedirectException('index', 'b2baccount');
        }

        $this->sessionStorage->set('ChangeMessage', ['success', 'PasswordChange']);
        throw new B2bControllerRedirectException('index', 'b2baccount');
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function processUploadAction(Request $request)
    {
        $imageProfile = $request->requireFileParam('uploadedFile');
        $identity = $this->authenticationService->getIdentity();
        $imgData = $this->accountImageService->uploadImage($identity->getAuthId(), $imageProfile);

        if ($imgData['success']) {
            $identity->setAvatar($imgData['path']);
            $this->authStorageAdapter->setIdentity($identity);

            $this->sessionStorage->set('ChangeMessage', ['success', 'ProfilePictureChanged']);
        } else {
            $this->sessionStorage->set('ChangeMessage', ['error', 'ProfilePictureNotChanged']);
        }

        throw new B2bControllerRedirectException('index', 'b2baccount');
    }
}
