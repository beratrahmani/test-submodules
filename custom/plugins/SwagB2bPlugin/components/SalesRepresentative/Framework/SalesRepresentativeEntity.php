<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Common\Entity;
use Shopware\B2B\Debtor\Framework\DebtorEntity;

class SalesRepresentativeEntity extends DebtorEntity
{
    /**
     * @var SalesRepresentativeClientEntity[]
     */
    public $clients;

    /**
     * @param array $data
     * @return Entity
     */
    public function fromDatabaseArray(array $data): Entity
    {
        parent::fromDatabaseArray($data);

        return $this;
    }
}
