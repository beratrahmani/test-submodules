<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Debtor\Framework\DebtorEntity;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;

class SalesRepresentativeDebtorIdentity extends DebtorIdentity implements SalesRepresentativeIdentityInterface
{
    /**
     * @var int
     */
    private $salesRepresentativeId;

    /**
     * @var int
     */
    private $mediaId;

    /**
     * @param int $salesRepresentativeId
     * @param int $authId
     * @param int $id
     * @param string $tableName
     * @param DebtorEntity $entity
     * @param string $avatar
     * @param int $mediaId
     * @param bool $isApi
     */
    public function __construct(
        int $salesRepresentativeId,
        int $authId,
        int $id,
        string $tableName,
        DebtorEntity $entity,
        string $avatar = '',
        int $mediaId = null,
        bool $isApi = false
    ) {
        parent::__construct($authId, $id, $tableName, $entity, $avatar, $isApi);
        $this->salesRepresentativeId = $salesRepresentativeId;
        $this->mediaId = $mediaId;
    }

    /**
     * @return int
     */
    public function getSalesRepresentativeId(): int
    {
        return $this->salesRepresentativeId;
    }

    /**
     * @return null|int
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }
}
