<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Api;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AssignmentContactController extends ApiAssignmentController
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @param ContactRepository $contactRepository
     * @param AclRepository $aclRepository
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        ContactRepository $contactRepository,
        AclRepository $aclRepository,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService
    ) {
        parent::__construct($aclRepository, $contextIdentityLoader, $loginContextService);
        $this->contactRepository = $contactRepository;
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @return array
     */
    public function getAllGrantAction(string $debtorEmail, string $contactMail): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->getAllGrant($contactEntity);
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @return array
     */
    public function getAllAllowedAction(string $debtorEmail, string $contactMail): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->getAllAllowed($contactEntity);
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param int $subjectId
     * @return array
     */
    public function getAllowedAction(string $debtorEmail, string $contactMail, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->getAllowed($contactEntity, $subjectId);
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param int $subjectId
     * @return array
     */
    public function allowAction(string $debtorEmail, string $contactMail, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->allow($contactEntity, $subjectId, false);
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param int $subjectId
     * @return array
     */
    public function allowGrantAction(string $debtorEmail, string $contactMail, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->allow($contactEntity, $subjectId, true);
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param Request $request
     * @return array
     */
    public function multipleAllowAction(string $debtorEmail, string $contactMail, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->multipleAllow($contactEntity, $request->getPost());
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param Request $request
     * @return array
     */
    public function multipleDenyAction(string $debtorEmail, string $contactMail, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->multipleDeny($contactEntity, $request->getPost());
    }

    /**
     * @param string $debtorEmail
     * @param string $contactMail
     * @param int $subjectId
     * @return array
     */
    public function denyAction(string $debtorEmail, string $contactMail, int $subjectId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $contactEntity = $this->getContactIdentityByEmail($contactMail, $ownershipContext);

        return $this->deny($contactEntity, $subjectId);
    }

    /**
     * @internal
     * @param string $contactMail
     * @param OwnershipContext $ownershipContext
     * @return ContactEntity
     */
    protected function getContactIdentityByEmail(string $contactMail, OwnershipContext $ownershipContext): ContactEntity
    {
        return $this->contactRepository->fetchOneByEmail($contactMail, $ownershipContext);
    }
}
