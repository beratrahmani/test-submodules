<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class OwnershipContext
{
    /**
     * @var string
     */
    public $shopOwnerEmail;

    /**
     * @var int
     */
    public $shopOwnerUserId;

    /**
     * @var int
     */
    public $identityId;

    /**
     * @var string
     */
    public $identityClassName;

    /**
     * @var int
     */
    public $authId;

    /**
     * @var int
     */
    public $contextOwnerId;

    /**
     * @param string $shopOwnerEmail
     * @param int $shopOwnerUserId
     * @param int $identityId
     * @param string $identityClassName
     * @param int $authId
     * @param int $contextOwnerId
     */
    public function __construct(
        int $authId,
        int $contextOwnerId,
        string $shopOwnerEmail,
        int $shopOwnerUserId,
        int $identityId,
        string $identityClassName
    ) {
        $this->authId = $authId;
        $this->contextOwnerId = $contextOwnerId;
        $this->shopOwnerEmail = $shopOwnerEmail;
        $this->shopOwnerUserId = $shopOwnerUserId;
        $this->identityId = $identityId;
        $this->identityClassName = $identityClassName;
    }
}
