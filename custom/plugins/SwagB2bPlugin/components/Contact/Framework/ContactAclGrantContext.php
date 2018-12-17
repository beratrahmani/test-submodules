<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Acl\Framework\AclGrantContext;

class ContactAclGrantContext implements AclGrantContext
{
    /**
     * @var ContactEntity
     */
    private $contactEntity;

    /**
     * @param ContactEntity $contactEntity
     */
    public function __construct(ContactEntity $contactEntity)
    {
        $this->contactEntity = $contactEntity;
    }

    /**
     * @return ContactEntity
     */
    public function getEntity()
    {
        return $this->contactEntity;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return ContactEntity::class . '::' . $this->contactEntity->id;
    }
}
