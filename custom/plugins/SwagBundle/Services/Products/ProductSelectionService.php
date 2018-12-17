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

namespace SwagBundle\Services\Products;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Repository;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Dependencies\ProviderInterface;

class ProductSelectionService implements ProductSelectionServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Repository
     */
    private $productRepository;

    /**
     * @param ProviderInterface $dependenciesProvider
     * @param ModelManager      $modelManager
     */
    public function __construct(
        ProviderInterface $dependenciesProvider,
        ModelManager $modelManager
    ) {
        $this->dependenciesProvider = $dependenciesProvider;
        $this->modelManager = $modelManager;
        $this->productRepository = $modelManager->getRepository(Article::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(BundleProduct $product, array $bundleConfiguration)
    {
        $productIsConfigured = true;
        $configuration = [];
        $configurable = false;

        // a bundle product is only configurable if the assigned product has an defined configurator set
        // and the passed bundle product has an valid id.
        if ($product->getConfigurable()
            && $product->getArticleDetail()->getArticle()->getConfiguratorSet() instanceof Set
        ) {
            $configurable = true;
        }

        $product->setConfigurable($configurable);

        if ($configurable) {
            //get the configurator configuration groups and options for the
            $configuration = $this->getProductConfiguration($product, $bundleConfiguration);
            if (!$configuration['configured']) {
                $productIsConfigured = false;
            }

            $selectedVariant = $this->getSelectedVariant($product, $bundleConfiguration);
            if (!$selectedVariant['success']) {
                $productIsConfigured = false;
            }

            $selectedVariant = $selectedVariant['detail'];
        } else {
            $selectedVariant = $product->getArticleDetail();
        }

        return [
            'selectedVariant' => $selectedVariant,
            'isConfigured' => $productIsConfigured,
            'configuration' => $configuration,
        ];
    }

    /**
     * Method to get the configurator configuration for the passed bundle product.
     *
     * @param BundleProduct $bundleProduct
     * @param array         $bundleConfiguration
     *
     * @return array
     */
    private function getProductConfiguration(BundleProduct $bundleProduct, array $bundleConfiguration)
    {
        //get identification objects and ids.
        $bundleId = $bundleProduct->getBundle()->getId();
        $bundleProductId = $bundleProduct->getId();
        $product = $bundleProduct->getArticleDetail()->getArticle();

        if (!$product->getConfiguratorSet() instanceof Set) {
            return ['groups' => [], 'configured' => true];
        }

        // Build an array of options to show, if the main bundle is limited to some variants
        $showOptions = [];
        $limitedDetails = $bundleProduct->getBundle()->getLimitedDetails();
        foreach ($limitedDetails as $detail) {
            $options = $detail->getConfiguratorOptions();
            foreach ($options as $option) {
                $showOptions[] = $option->getId();
            }
        }

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
            ->from(Set::class, 'configuratorSet')
            ->innerJoin('configuratorSet.groups', 'groups', null, null, 'groups.id')
            ->innerJoin('configuratorSet.options', 'options')
            ->where('configuratorSet.id = :setId')
            ->orderBy('groups.position', 'ASC')
            ->addOrderBy('options.position', 'ASC')
            ->setParameters(['setId' => $product->getConfiguratorSet()->getId()]);

        $configuratorSet = $builder->getQuery()->getArrayResult();
        $configuratorSet = $configuratorSet[0];
        $productConfiguration = $bundleConfiguration[$bundleId][$bundleProductId];
        $sArticleCoreModule = $this->dependenciesProvider->getArticlesModule();
        $localeID = $this->dependenciesProvider->getShop()->getLocale()->getId();

        foreach ($configuratorSet['options'] as $option) {
            if (array_key_exists($option['groupId'], $configuratorSet['groups'])) {
                // Skip options of variants, not available. Only do so for the main bundle product
                // and if showOptions is not empty
                if (!empty($showOptions)
                    && !in_array($option['id'], $showOptions)
                    && (int) $product->getId() === (int) $bundleProduct->getBundle()->getArticle()->getId()
                ) {
                    continue;
                }

                $option = $sArticleCoreModule->sGetTranslation($option, $option['id'], 'configuratoroption');
                $configuratorSet['groups'][$option['groupId']]['options'][] = $option;
            }
        }

        foreach ($configuratorSet['groups'] as &$group) {
            $translatedGroup = $sArticleCoreModule->sGetTranslation($group, $group['id'], 'configuratorgroup', $localeID);

            /*
             * The already selected bundle product configuration is saved in the shopware session in the following format:
             * Session => bundleConfiguration [$bundleId]  [$bundleProductId] [$configuratorGroupId]
             */
            $group['selected'] = $productConfiguration[$translatedGroup['id']];

            /*
             * To display translations correctly we need to add the groupname to each group.
             */
            $group['groupname'] = $translatedGroup['groupname'];
        }
        unset($group);
        $filtered = array_filter($productConfiguration);

        return [
            'configured' => count($configuratorSet['groups']) === count($filtered),
            'groups' => array_values($configuratorSet['groups']),
        ];
    }

    /**
     * Returns the variant selection for the passed bundle product position.
     *
     * The selected configuration are saved in the shopware frontend session.
     * The different configuration are identified over the bundle id and bundle product id.
     *
     * @param BundleProduct $bundleProduct
     * @param array         $bundleConfiguration
     *
     * @return array with success and detail array key
     */
    private function getSelectedVariant(BundleProduct $bundleProduct, array $bundleConfiguration)
    {
        $bundleId = $bundleProduct->getBundle()->getId();
        $bundleProductId = $bundleProduct->getId();

        $productConfiguration = $bundleConfiguration[$bundleId][$bundleProductId];

        if (empty($productConfiguration)) {
            return [
                'success' => false,
                'detail' => $bundleProduct->getArticleDetail(),
            ];
        }

        $query = $this->productRepository->getDetailsForOptionIdsQuery(
            $bundleProduct->getArticleDetail()->getArticle()->getId(),
            $productConfiguration
        );

        $selected = $query->getResult();

        if (empty($selected)) {
            return [
                'success' => false,
                'detail' => $bundleProduct->getArticleDetail(),
            ];
        }

        return [
            'success' => true,
            'detail' => $selected[0],
        ];
    }
}
