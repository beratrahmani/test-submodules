<?php declare(strict_types=1);

namespace SwagB2bPlugin\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\B2B\SalesRepresentative\Framework\SalesRepresentativeIdentity;
use Shopware\Components\Theme\LessDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendTemplateExtender implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'addViewDirectories',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => [
                ['addB2bSuiteVariable', 1],
                ['addSalesRepVariable', 2],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'addViewDirectories',
            'Enlight_Controller_Action_PreDispatch_Widgets' => [
                ['addViewDirectories', 1],
                ['addB2bSuiteVariable', 2],
                ['addSalesRepVariable', 3],
            ],
            'Theme_Compiler_Collect_Plugin_Less' => 'getLessCollection',
            'Theme_Compiler_Collect_Plugin_JavaScript' => 'getJavaScriptCollection',
            'Enlight_Controller_Action_PreDispatch' => 'addFrontendSmartyHelpers',
        ];
    }

    /**
     * Register the b2b widget plugin
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addFrontendSmartyHelpers(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        /** @var \Enlight_Template_Manager $template */
        $template = $this->container->get('template');

        if (!in_array($request->getModuleName(), ['frontend', 'widgets'], true)) {
            return;
        }

        $template->addPluginsDir(__DIR__ . '/../Resources/views/frontend/_private/smarty/');
        $template->addPluginsDir(__DIR__ . '/../Resources/views/_private/smarty/');
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
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addB2bSuiteVariable(\Enlight_Controller_ActionEventArgs $args)
    {
        if ($this->container->get('b2b_front_auth.authentication_service')->isB2b()) {
            $args->getSubject()->View()->assign('b2bSuite', true);
        } else {
            $args->getSubject()->View()->assign('b2bSuite', false);
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addSalesRepVariable(\Enlight_Controller_ActionEventArgs $args)
    {
        if ($args->getSubject()->View()->getAssign('b2bSuite')) {
            $authenticationService = $this->container->get('b2b_front_auth.authentication_service');
            $identity = $authenticationService->getIdentity();

            if ($identity instanceof SalesRepresentativeIdentity) {
                $args->getSubject()->View()->assign('isSalesRep', true);
            }
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getLessCollection()
    {
        $less[] = new LessDefinition(
            [],
            [__DIR__ . '/../Resources/views/frontend/_public/src/less/all.less'],
            __DIR__ . '/..'
        );

        return new ArrayCollection($less);
    }

    /**
     * @return ArrayCollection
     */
    public function getJavaScriptCollection()
    {
        $jsFiles = [
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-acl-form.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-acl-grid.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-chart.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-default-address.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-edit-inline.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-fastorder-table.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-form-disable.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-form-select.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-loading.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-modal.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-order-number.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-plugin-loader.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-tab.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-trigger-reload.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-edit-inline.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-panel-upload.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/ajax-product-search.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/assignment-grid.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/auto-enable-form.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/auto-submit.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/Chart.min.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/confirm-box.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/contact-password-activation.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/form-input-holder.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/grid-component.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/order-list.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/sync-height.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/tab.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/tree.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/tree-select.js',
            __DIR__ . '/../Resources/views/frontend/_public/src/js/preloader-anchor.js',
        ];

        return new ArrayCollection($jsFiles);
    }
}
