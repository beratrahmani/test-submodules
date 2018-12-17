<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Bridge;

use Shopware\B2B\AuditLog\Framework\AuditLogAuthorEntity;
use Shopware\B2B\AuditLog\Framework\BackendProviderInterface;

class BackendProvider implements BackendProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBackendUser(): AuditLogAuthorEntity
    {
        /** @var $auth \Shopware_Components_Auth */
        $auth = Shopware()->Container()->get('Auth')->getIdentity();
        $name = explode(' ', $auth->name);

        $authorEntity = new AuditLogAuthorEntity();
        $authorEntity->firstName = array_shift($name);

        if ($name !== null) {
            $lastName = implode(' ', $name);
            $authorEntity->lastName = $lastName;
        }

        $authorEntity->email = $auth->email;
        $authorEntity->isApi = false;
        $authorEntity->isBackend = true;

        return $authorEntity;
    }
}
