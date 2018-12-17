<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Frontend;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Dashboard\Framework\EmotionRepositoryInterface;
use Shopware\B2B\Dashboard\Framework\InformationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class DashboardController
{
    /**
     * @var EmotionRepositoryInterface
     */
    private $emotionRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var InformationService
     */
    private $informationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param EmotionRepositoryInterface $emotionRepository
     * @param AuthenticationService $authenticationService
     * @param InformationService $informationService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        EmotionRepositoryInterface $emotionRepository,
        AuthenticationService $authenticationService,
        InformationService $informationService,
        CurrencyService $currencyService
    ) {
        $this->emotionRepository = $emotionRepository;
        $this->authenticationService = $authenticationService;
        $this->informationService = $informationService;
        $this->currencyService = $currencyService;
    }

    /**
     * @return array
     */
    public function indexAction(): array
    {
        $emotion = [];
        try {
            $emotion = $this->emotionRepository
                ->fetchEmotion($this->authenticationService->getIdentity());


            $emotion = [
                'emotion' => $emotion,
                'hasEmotion' => true,
            ];
        } catch (NotFoundException $e) {
            // nth
        }

        $orderInformationMessages = $this->informationService->getInformation(
            $this->authenticationService->getIdentity(),
            $this->currencyService->createCurrencyContext()
        );

        return array_merge(
            ['orderInformationMessages' => $orderInformationMessages],
            $emotion
        );
    }
}
