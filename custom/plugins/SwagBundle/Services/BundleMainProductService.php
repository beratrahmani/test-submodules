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

namespace SwagBundle\Services;

use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail;
use SwagBundle\Models\Article as BundleProduct;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Dependencies\ProviderInterface;

class BundleMainProductService implements BundleMainProductServiceInterface
{
    /**
     * @var ProviderInterface
     */
    private $dependenciesProvider;

    /**
     * @param ProviderInterface $dependenciesProvider
     */
    public function __construct(ProviderInterface $dependenciesProvider)
    {
        $this->dependenciesProvider = $dependenciesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleMainProduct(Bundle $bundle, $productNumber = '')
    {
        $mainProduct = new BundleProduct();
        $mainProduct->setArticleDetail($bundle->getArticle()->getMainDetail());
        $mainProduct->setConfigurable($bundle->getArticle()->getConfiguratorSet() instanceof Set);
        $mainProduct->setBundle($bundle);
        $mainProduct->setId(0);

        if ($productNumber !== '') {
            $variants = $bundle->getArticle()->getDetails();

            $selectedVariants = $variants->filter(function ($variant) use ($productNumber) {
                return $variant->getNumber() === $productNumber;
            });

            // if count is 0 the request is not from the main product detail page, but from another product of the bundle
            if ($selectedVariants->count() !== 0) {
                /** @var Detail $selectedVariant */
                $selectedVariant = $selectedVariants->first();
                $mainProduct->setArticleDetail($selectedVariant);

                // set selected detail to session for the further plugin logic
                $session = $this->dependenciesProvider->getSession();
                $bundleConfiguration = $session->get('bundleConfiguration', []);
                $selectedConfiguration = [];

                $configuratorOptions = $selectedVariant->getConfiguratorOptions();
                /** @var Option $configuratorOption */
                foreach ($configuratorOptions as $configuratorOption) {
                    $selectedConfiguration[$configuratorOption->getGroup()->getId()] = $configuratorOption->getId();
                }

                $bundleConfiguration[$bundle->getId()][0] = $selectedConfiguration;
                $session->offsetSet('bundleConfiguration', $bundleConfiguration);
            }
        }

        // $productNumber is not a number from bundle main product
        // check for limited variants, otherwise it is possible that a variant is shown, which is not allowed
        $limitedVariants = $bundle->getLimitedDetails();
        if ($limitedVariants->count() !== 0) {
            $mainProduct->setArticleDetail($limitedVariants->first());
        }

        return $mainProduct;
    }
}
