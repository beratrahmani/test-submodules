<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Common\B2BTranslatableException;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class FrontendErrorSubscriber implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_Controllers_Frontend_Error::genericErrorAction::after' => 'afterGenericErrorAction',
        ];
    }

    public function afterGenericErrorAction(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Error $subject */
        $subject = $args->getSubject();

        if (!$this->authenticationService->isB2b() || $subject->Front()->getParam('showException')) {
            return;
        }

        $error = $subject->Request()->get('error_handler');

        /** @var B2BTranslatableException $exception */
        $exception = $error->exception;

        if (!($exception instanceof B2BTranslatableException) || empty($exception->getTranslationMessage())) {
            return;
        }

        $subject->View()->assign('b2bError', [
            'property' => 'Exception',
            'snippetKey' => preg_replace('([^a-zA-Z0-9]+)', '', ucwords($exception->getTranslationMessage())),
            'messageTemplate' => $exception->getTranslationMessage(),
            'parameters' => $exception->getTranslationParams(),
        ]);
    }
}
