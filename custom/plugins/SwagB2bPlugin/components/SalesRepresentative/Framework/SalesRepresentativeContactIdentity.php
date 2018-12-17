<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactIdentity;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;

class SalesRepresentativeContactIdentity extends ContactIdentity implements SalesRepresentativeIdentityInterface
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
     * @param ContactEntity $entity
     * @param DebtorIdentity $identity
     * @param string $avatar
     * @param int $mediaId
     */
    public function __construct(
        int $salesRepresentativeId,
        int $authId,
        int $id,
        string $tableName,
        ContactEntity $entity,
        DebtorIdentity $identity,
        string $avatar = '',
        int $mediaId = null
    ) {
        parent::__construct($authId, $id, $tableName, $entity, $identity, $avatar);
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
