<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Framework;

use Shopware\B2B\Common\Entity;

class AclRouteEntity implements Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $resource_name;

    /**
     * @var string
     */
    public $privilege_type;

    /**
     * @param array $data
     * @return AclRouteEntity
     */
    public function fromDatabaseArray(array $data): self
    {
        $this->id = (int) $data['id'];
        $this->resource_name = (string) $data['resource_name'];
        $this->privilege_type = (string) $data['privilege_type'];

        return $this;
    }
}
