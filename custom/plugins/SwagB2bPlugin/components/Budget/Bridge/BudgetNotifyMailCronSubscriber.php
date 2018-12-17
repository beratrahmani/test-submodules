<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Budget\Framework\BudgetEntity;
use Shopware\B2B\Budget\Framework\BudgetNotificationRepository;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\Components\Model\ModelManager;
use Shopware_Components_TemplateMail;

class BudgetNotifyMailCronSubscriber implements SubscriberInterface
{
    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetNotificationRepository
     */
    private $notificationRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var Shopware_Components_TemplateMail
     */
    private $templateMail;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param BudgetService $budgetService
     * @param BudgetRepository $budgetRepository
     * @param BudgetNotificationRepository $notificationRepository
     * @param AuthenticationService $authenticationService
     * @param Shopware_Components_TemplateMail $templateMail
     * @param ModelManager $modelManager
     * @param CurrencyService $currencyService
     */
    public function __construct(
        BudgetService $budgetService,
        BudgetRepository $budgetRepository,
        BudgetNotificationRepository $notificationRepository,
        AuthenticationService $authenticationService,
        Shopware_Components_TemplateMail $templateMail,
        ModelManager $modelManager,
        CurrencyService $currencyService
    ) {
        $this->budgetService = $budgetService;
        $this->budgetRepository = $budgetRepository;
        $this->authenticationService = $authenticationService;
        $this->templateMail = $templateMail;
        $this->modelManager = $modelManager;
        $this->notificationRepository = $notificationRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_B2bBudgetNotifyAuthor' => 'onRunBudgetCronJob',
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $args
     */
    public function onRunBudgetCronJob(\Shopware_Components_Cron_CronJob $args)
    {
        $currencyContext = $this->currencyService->createCurrencyContext();

        $budgets = $this->budgetRepository->fetchAllBudgets($currencyContext);

        foreach ($budgets as $budget) {
            if (!$budget->ownerId) {
                continue;
            }

            $this->sendNotifyMail($budget, $currencyContext);
        }
    }

    /**
     * @internal
     * @param BudgetEntity $budget
     * @param CurrencyContext $currencyContext
     */
    protected function sendNotifyMail(BudgetEntity $budget, CurrencyContext $currencyContext)
    {
        $identity = $this->authenticationService->getIdentityByAuthId($budget->ownerId);
        $context = $this->budgetService->prepareMail($budget, $currencyContext, $identity->getOwnershipContext());

        if (count($context) === 0) {
            return;
        }

        $postalSettings = $identity->getPostalSettings();

        $shop = null;
        if ($postalSettings->language) {
            $shop = $this->modelManager->getRepository('Shopware\Models\Shop\Shop')->findOneBy([
                'id' => $postalSettings->language,
            ]);
        }

        $mail = $this->templateMail->createMail('b2bBudgetNotify', $context, $shop);

        $mail->addTo($postalSettings->email);
        $mail->send();

        $this->notificationRepository
            ->addNotify($budget->id, $context['refreshGroup'], new \DateTime());
    }
}
