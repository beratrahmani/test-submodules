<?php declare(strict_types = 1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Shopware\B2B\Common\Entity;

class StoreFrontAuthenticationEntity implements Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $contextOwnerId;

    /**
     * @var string
     */
    public $providerKey;

    /**
     * @var int
     */
    public $providerContext;

    /**
     * @var int
     */
    public $mediaId;

    /**
     * @param array $data
     * @return Entity
     */
    public function fromDatabaseArray(array $data): Entity
    {
        $this->id = (int) $data['id'];
        $this->contextOwnerId = (int) $data['context_owner_id'];
        $this->providerKey = $data['provider_key'];
        $this->providerContext = (int) $data['provider_context'];
        $this->mediaId = (int) $data['media_id'];

        return $this;
    }
}
