<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\Common\Repository\SearchStruct;

class AclGrantContextSearchStruct extends SearchStruct
{
    /**
     * @var AclGrantContext
     */
    public $aclGrantContext;
}
