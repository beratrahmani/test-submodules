<?php

/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagProductAdvisor\Subscriber;

use Enlight\Event\SubscriberInterface;
use SwagProductAdvisor\Components\Helper\RewriteUrlGeneratorInterface;

/**
 * Class Seo
 */
class Seo implements SubscriberInterface
{
    /**
     * @var RewriteUrlGeneratorInterface
     */
    private $rewriteUrlGenerator;

    /**
     * @param RewriteUrlGeneratorInterface $rewriteUrlGenerator
     */
    public function __construct(RewriteUrlGeneratorInterface $rewriteUrlGenerator)
    {
        $this->rewriteUrlGenerator = $rewriteUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Performance' => 'onLoadBackendModule',
            'Shopware_Components_RewriteGenerator_FilterQuery' => 'onFilterRewriteQuery',
            'sRewriteTable::sCreateRewriteTable::after' => 'onCreateRewriteTable',
            'Shopware_CronJob_RefreshSeoIndex_CreateRewriteTable' => 'onCreateRewriteTable',
            'Shopware_Controllers_Seo_filterCounts' => 'addSeoCount',
        ];
    }

    /**
     * Adds the backend-templates here to extend the performance-module.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onLoadBackendModule(\Enlight_Controller_ActionEventArgs $args)
    {
        $subject = $args->getSubject();
        $request = $subject->Request();

        if ($request->getActionName() !== 'load') {
            return;
        }

        $subject->View()->extendsTemplate('backend/performance/view/advisor.js');
    }

    /**
     * Adds our own advisor-queries to the rewrite-query.
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return string
     */
    public function onFilterRewriteQuery(\Enlight_Event_EventArgs $args)
    {
        $orgQuery = $args->getReturn();
        $query = $args->getQuery();

        if ($query['controller'] === 'advisor' && isset($query['advisorId'])) {
            $orgQuery['advisorId'] = $query['advisorId'];
            unset($orgQuery['sAction']);
        }

        return $orgQuery;
    }

    /**
     * Generates the seo-urls for the advisor for the current shop.
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return string
     */
    public function onCreateRewriteTable(\Enlight_Event_EventArgs $args)
    {
        /** @var RewriteUrlGeneratorInterface $rewriteUrlGenerator */
        $rewriteUrlGenerator = $this->rewriteUrlGenerator;
        $rewriteUrlGenerator->createRewriteTableAdvisor();

        return $args->getReturn();
    }

    /**
     * Add count for the advisor SEO URLs
     *
     * @param \Enlight_Event_EventArgs $args
     *
     * @return array
     */
    public function addSeoCount(\Enlight_Event_EventArgs $args)
    {
        $counts = $args->getReturn();
        $counts['advisor'] = $this->rewriteUrlGenerator->countAdvisorUrls();

        return $counts;
    }
}
