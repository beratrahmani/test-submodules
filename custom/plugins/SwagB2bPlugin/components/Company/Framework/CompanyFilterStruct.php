<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Framework;

use Shopware\B2B\Acl\Framework\AclGrantContextSearchStruct;

class CompanyFilterStruct extends AclGrantContextSearchStruct
{
    const TYPE_VISIBILITY = 'acl';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_INHERITANCE = 'inheritance';

    /**
     * @var string
     */
    public $companyFilterType;
}
