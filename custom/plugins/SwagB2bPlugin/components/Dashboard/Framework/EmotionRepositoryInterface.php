<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

interface EmotionRepositoryInterface
{
    /**
     * @param Identity $identity
     * @throws NotFoundException
     * @return EmotionEntity
     */
    public function fetchEmotion(Identity $identity): EmotionEntity;

    /**
     * @param int $authId
     * @throws NotFoundException
     * @return int
     */
    public function getDirectEmotionIdByAuthId(int $authId): int;

    /**
     * @param int $authId
     * @param int $emotionId
     */
    public function updateEmotion(int $authId, int $emotionId);

    /**
     * @return EmotionEntity[]
     */
    public function getAllEmotions(): array;
}
