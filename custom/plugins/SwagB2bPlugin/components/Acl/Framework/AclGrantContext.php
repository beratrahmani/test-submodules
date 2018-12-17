<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

interface AclGrantContext
{
    public function getEntity();

    /**
     * @return string
     */
    public function getIdentifier(): string;
}
