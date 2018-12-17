<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;

class BackendTemplateExtender implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $template_Manager;

    /**
     * @param Enlight_Template_Manager $container
     * @param Enlight_Template_Manager $template_Manager
     */
    public function __construct(Enlight_Template_Manager $template_Manager)
    {
        $this->template_Manager = $template_Manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend' => 'addViewDirectories',
            'Enlight_Controller_Action_PreDispatch' => 'addBackendSmartyHelpers',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addViewDirectories(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->addTemplateDir(__DIR__ . '/../Resources/views');
        $args->getSubject()->View()->addTemplateDir(__DIR__ . '/../Resources/extendedViews');
    }

    /**
     * Register the b2b widget plugin
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addBackendSmartyHelpers(\Enlight_Controller_ActionEventArgs $args)
    {
        $this->template_Manager->addPluginsDir(__DIR__ . '/../Resources/views/_private/smarty/');
    }
}
