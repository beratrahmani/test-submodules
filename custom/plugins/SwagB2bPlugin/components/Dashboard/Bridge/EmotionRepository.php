<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Bridge;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Dashboard\Framework\EmotionEntity;
use Shopware\B2B\Dashboard\Framework\EmotionRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\StoreFrontAuthenticationRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\CustomerGroup;
use Shopware\Models\Emotion\Emotion;

class EmotionRepository implements EmotionRepositoryInterface
{
    /**
     * @var ModelManager
     */
    private $manager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ModelManager $manager
     * @param Connection $connection
     */
    public function __construct(ModelManager $manager, Connection $connection)
    {
        $this->manager = $manager;
        $this->connection = $connection;
    }

    /**
     * @internal
     * @param Identity $identity
     * @throws NotFoundException
     * @return int
     */
    protected function getEmotionId(Identity $identity): int
    {
        $emotionData = $this->connection->createQueryBuilder()
            ->select(StoreFrontAuthenticationRepository::TABLE_ALIAS . '.emotion_id as emotionId')
            ->addSelect(StoreFrontAuthenticationRepository::TABLE_ALIAS . '_debtor.emotion_id as debtorEmotionId')
            ->addSelect(StoreFrontAuthenticationRepository::TABLE_ALIAS . '_debtor.provider_context as userId')
            ->from(StoreFrontAuthenticationRepository::TABLE_NAME, StoreFrontAuthenticationRepository::TABLE_ALIAS)
            ->innerJoin(
                StoreFrontAuthenticationRepository::TABLE_ALIAS,
                StoreFrontAuthenticationRepository::TABLE_NAME,
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '_debtor',
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '.context_owner_id = ' .
                StoreFrontAuthenticationRepository::TABLE_ALIAS . '_debtor.id'
            )
            ->where(StoreFrontAuthenticationRepository::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $identity->getAuthId())
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (isset($emotionData['emotionId'])) {
            return (int) $emotionData['emotionId'];
        }

        if (isset($emotionData['debtorEmotionId'])) {
            return (int) $emotionData['debtorEmotionId'];
        }

        return $this->getEmotionFromCustomerGroup((int) $emotionData['userId']);
    }

    /**
     * @internal
     * @param int $userId
     * @return int
     */
    protected function getEmotionFromCustomerGroup(int $userId): int
    {
        $user = $this->manager->getRepository('Shopware\Models\Customer\Customer')
            ->find($userId);

        if (!$user) {
            throw new NotFoundException(sprintf('user not found for id %d', $userId));
        }

        /** @var CustomerGroup $customerGroupAttr */
        $customerGroupAttr = $user->getGroup()->getAttribute();

        if (!$customerGroupAttr) {
            throw new NotFoundException('attribute not found');
        }

        $emotionId = $customerGroupAttr->getB2bLandingpage();

        if (!$emotionId) {
            throw new NotFoundException(sprintf('no emotion set to customerGroup %s', $customerGroupAttr->getCustomerGroup()->getKey()));
        }

        return (int) $emotionId;
    }

    /**
     * @param Identity $identity
     * @throws NotFoundException
     * @return EmotionEntity
     */
    public function fetchEmotion(Identity $identity): EmotionEntity
    {
        $emotionId = $this->getEmotionId($identity);

        $emotion = $this->manager->getRepository('Shopware\Models\Emotion\Emotion')->find($emotionId);

        if (!$emotion) {
            throw new NotFoundException('no emotion found for user');
        }

        return $this->convertToEntity($emotion);
    }

    /**
     * @param int $authId
     * @throws NotFoundException
     * @return int
     */
    public function getDirectEmotionIdByAuthId(int $authId): int
    {
        $emotionId = $this->connection->createQueryBuilder()
            ->select('emotion_id')
            ->from(StoreFrontAuthenticationRepository::TABLE_NAME, StoreFrontAuthenticationRepository::TABLE_ALIAS)
            ->where('id = :id')
            ->setParameter('id', $authId)
            ->execute()
            ->fetchColumn();

        if (!$emotionId) {
            throw new NotFoundException('no emotion found for user');
        }

        return (int) $emotionId;
    }

    /**
     * @param int $authId
     * @param int $emotionId
     */
    public function updateEmotion(int $authId, int $emotionId)
    {
        if ($emotionId === 0) {
            $emotionId = null;
        }

        $this->connection->update(
            StoreFrontAuthenticationRepository::TABLE_NAME,
            ['emotion_id' => $emotionId],
            ['id' => $authId]
        );
    }

    /**
     * @return EmotionEntity[]
     */
    public function getAllEmotions(): array
    {
        $emotions = $this->manager->getRepository('Shopware\Models\Emotion\Emotion')
            ->findAll();

        return $this->convertToEntities($emotions);
    }

    /**
     * @internal
     * @param array $emotions
     * @return array
     */
    protected function convertToEntities(array $emotions): array
    {
        return array_map([$this, 'convertToEntity'], $emotions);
    }

    /**
     * @internal
     * @param Emotion $emotion
     * @return EmotionEntity
     */
    protected function convertToEntity(Emotion $emotion): EmotionEntity
    {
        $entity = new EmotionEntity();
        $entity->id = (int) $emotion->getId();
        $entity->device = (int) $emotion->getDevice();
        $entity->name = $emotion->getName();

        return $entity;
    }
}
