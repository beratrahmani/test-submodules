<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class UserAddress
{
    /**
     * @var int
     */
    public $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
