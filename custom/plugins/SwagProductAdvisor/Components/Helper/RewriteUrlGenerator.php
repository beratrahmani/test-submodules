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

namespace SwagProductAdvisor\Components\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Modules as Modules;
use sRewriteTable;
use SwagProductAdvisor\Components\DependencyProvider\DependencyProviderInterface;

class RewriteUrlGenerator implements RewriteUrlGeneratorInterface
{
    /**
     * @var Modules
     */
    private $modules;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param ModelManager                $modelManager
     * @param Connection                  $connection
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->modules = $dependencyProvider->getModules();
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createRewriteUrls($advisorId, $advisorName, $shopId = null)
    {
        /** @var sRewriteTable $rewriteCmp */
        $rewriteCmp = $this->modules->RewriteTable();
        $orgPath = "sViewport=advisor&advisorId={$advisorId}";

        $shops = $this->modelManager->getRepository(Shop::class)->findBy([
            'active' => 1,
        ]);

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            if ($shopId && $shop->getId() != $shopId) {
                continue;
            }
            $shop->registerResources();
            $rewriteCmp->sInsertUrl($orgPath, $rewriteCmp->sCleanupPath($advisorName) . '/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createRewriteTableAdvisor($offset = null, $limit = null, $shopId = null)
    {
        $advisorList = $this->getAdvisorList();

        if (isset($offset) && isset($limit)) {
            $advisorList = array_slice($advisorList, $offset, $limit);
        }

        foreach ($advisorList as $advisor) {
            $this->createRewriteUrls($advisor['id'], $advisor['name'], $shopId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countAdvisorUrls()
    {
        return count($this->getAdvisorList());
    }

    /**
     * Returns all advisor-list and its translations for the given shop-id.
     *
     * @return array
     */
    private function getAdvisorList()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();

        $advisorList = $builder->select('advisor.id, advisor.name')
            ->from('s_plugin_product_advisor_advisor', 'advisor')
            ->execute()
            ->fetchAll();

        $advisorList = array_merge($this->getAdvisorTranslations(), $advisorList);

        return $advisorList;
    }

    /**
     * Returns all advisor translations.
     *
     * @param int|null $shopId
     *
     * @return array
     */
    private function getAdvisorTranslations($shopId = null)
    {
        if ($shopId === null) {
            $shop = $this->dependencyProvider->getShop();

            if ($shop !== null) {
                $shopId = $shop->getId();
            }
        }

        /** @var QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();

        $translations = $builder->select('translation.objectdata, translation.objectkey as advisorId')
            ->from('s_core_translations', 'translation')
            ->where("translation.objecttype = 'productAdvisor'")
            ->andWhere('translation.objectlanguage = :shopId')
            ->setParameter('shopId', $shopId)
            ->execute()
            ->fetchAll();

        $translationArray = [];
        foreach ($translations as $translationData) {
            $translation = unserialize($translationData['objectdata']);

            $translationArray[] = [
                'id' => $translationData['advisorId'],
                'name' => $translation['name'],
            ];
        }

        return $translationArray;
    }
}
