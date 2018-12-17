<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class AuditLogAuthorService
{
    /**
     * @var AuditLogAuthorRepository
     */
    private $auditLogAuthorRepository;

    /**
     * @var BackendProviderInterface
     */
    private $backendProvider;

    /**
     * @param AuditLogAuthorRepository $auditLogAuthorRepository
     * @param BackendProviderInterface $backendProvider
     */
    public function __construct(
        AuditLogAuthorRepository $auditLogAuthorRepository,
        BackendProviderInterface $backendProvider
    ) {
        $this->auditLogAuthorRepository = $auditLogAuthorRepository;
        $this->backendProvider = $backendProvider;
    }

    /**
     * @param Identity $identity
     * @return AuditLogAuthorEntity
     */
    public function createAuthorEntityFromIdentity(Identity $identity): AuditLogAuthorEntity
    {
        $settings = $identity->getPostalSettings();

        $authorEntity = new AuditLogAuthorEntity();

        $authorEntity->salutation = $settings->salutation;
        $authorEntity->title = $settings->title;
        $authorEntity->firstName = $settings->firstName;
        $authorEntity->lastName = $settings->lastName;

        $authorEntity->identity = serialize($identity);
        $authorEntity->email = $identity->getLoginCredentials()->email;

        $authorEntity->isApi = $identity->isApiUser();

        $authorEntity = $this->generateAuditAuthorEntityHash($authorEntity);

        return $this->auditLogAuthorRepository
            ->createAuditLogAuthor($authorEntity);
    }

    /**
     * @return AuditLogAuthorEntity
     */
    public function createBackendAuthorEntity(): AuditLogAuthorEntity
    {
        $authorEntity = $this->backendProvider->getBackendUser();

        $authorEntity = $this->generateAuditAuthorEntityHash($authorEntity);

        return $this->auditLogAuthorRepository
            ->createAuditLogAuthor($authorEntity);
    }

    /**
     * @internal
     * @param AuditLogAuthorEntity $authorEntity
     * @return AuditLogAuthorEntity
     */
    protected function generateAuditAuthorEntityHash(AuditLogAuthorEntity $authorEntity): AuditLogAuthorEntity
    {
        $entity = $authorEntity->toArray();
        unset($entity['hash']);

        $authorEntity->hash = md5(implode('|', $entity));

        return $authorEntity;
    }
}
