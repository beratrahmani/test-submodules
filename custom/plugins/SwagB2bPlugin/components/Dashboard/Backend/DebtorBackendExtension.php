<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Backend;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Contact\Framework\ContactEntity;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\Dashboard\Bridge\EmotionRepository;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\Debtor\Framework\DebtorRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class DebtorBackendExtension
{
    /**
     * @var DebtorRepository
     */
    private $debtorRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var LoginContextService
     */
    private $contextService;

    /**
     * @var EmotionRepository
     */
    private $emotionRepository;

    /**
     * @param DebtorRepository $debtorRepository
     * @param ContactRepository $contactRepository
     * @param LoginContextService $contextService
     * @param EmotionRepository $emotionRepository
     */
    public function __construct(
        DebtorRepository $debtorRepository,
        ContactRepository $contactRepository,
        LoginContextService $contextService,
        EmotionRepository $emotionRepository
    ) {
        $this->debtorRepository = $debtorRepository;
        $this->contactRepository = $contactRepository;
        $this->contextService = $contextService;
        $this->emotionRepository = $emotionRepository;
    }

    /**
     * @param int $debtorId
     * @return array
     */
    public function getUserList(int $debtorId): array
    {
        /** @var DebtorIdentity $debtor */
        $debtor = $this->debtorRepository->fetchIdentityById($debtorId, $this->contextService);

        $contactSearchStruct = new ContactSearchStruct();
        $contactSearchStruct->orderBy = $this->contactRepository::TABLE_ALIAS . '.id';
        $contactSearchStruct->orderDirection = 'ASC';

        $contacts = $this->contactRepository->fetchList($debtor->getOwnershipContext(), $contactSearchStruct);

        $users = $this->getContactUserData($contacts);
        array_unshift($users, $this->getDebtorUserData($debtor));

        return $users;
    }

    /**
     * @internal
     * @param DebtorIdentity $debtor
     * @throws NotFoundException
     * @return array
     */
    protected function getDebtorUserData(DebtorIdentity $debtor): array
    {
        try {
            $emotion = $this->emotionRepository->getDirectEmotionIdByAuthId($debtor->getAuthId());
        } catch (NotFoundException $e) {
            $emotion = null;
        }

        $postalSetting = $debtor->getPostalSettings();

        return [
            'id' => $debtor->getAuthId(),
            'name' => $postalSetting->firstName . ' ' . $postalSetting->lastName,
            'email' => $postalSetting->email,
            'type' => 'debtor',
            'emotion' => $emotion,
        ];
    }

    /**
     * @internal
     * @param ContactEntity[] $contacts
     * @throws NotFoundException
     * @return array
     */
    protected function getContactUserData(array $contacts): array
    {
        $userData = [];
        foreach ($contacts as $contact) {
            if (!$contact->authId) {
                continue;
            }

            try {
                $emotion = $this->emotionRepository->getDirectEmotionIdByAuthId($contact->authId);
            } catch (NotFoundException $e) {
                $emotion = null;
            }

            $userData[] = [
                'id' => $contact->authId,
                'name' => $contact->firstName . ' ' . $contact->lastName,
                'email' => $contact->email,
                'type' => 'contact',
                'emotion' => $emotion,
            ];
        }

        return $userData;
    }

    /**
     * @param int $userId
     * @param int $emotionId
     */
    public function updateUser(int $userId, int $emotionId)
    {
        $this->emotionRepository->updateEmotion($userId, $emotionId);
    }

    /**
     * @return array
     */
    public function getAllEmotions()
    {
        $emotions = $this->emotionRepository->getAllEmotions();

        $emotionArray = [];
        foreach ($emotions as $emotion) {
            $emotionArray[] = [
                'id' => $emotion->getId(),
                'name' => $emotion->getName(),
            ];
        }

        return $emotionArray;
    }
}
