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

namespace SwagLiveShopping\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use SwagLiveShopping\Bootstrap\EmotionElementSetup;
use SwagLiveShopping\Bundle\SearchBundle\Condition\LiveShoppingCondition;

class LiveShoppingSliderHandler implements ComponentHandlerInterface
{
    const COMPONENT_TYPE = 'emotion-components-live-shopping-slider';

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @var LegacyStructConverter
     */
    private $structConverter;

    /**
     * @param StoreFrontCriteriaFactoryInterface $criteriaFactory
     * @param LegacyStructConverter              $structConverter
     */
    public function __construct(
        StoreFrontCriteriaFactoryInterface $criteriaFactory,
        LegacyStructConverter $structConverter
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->structConverter = $structConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $limit = (int) $element->getConfig()->get('number_products');

        if ($limit === 0) {
            $limit = EmotionElementSetup::DEFAULT_NUMBER_OF_PRODUCTS;
        }

        $criteria = $this->createCriteria($context, $limit);

        $key = 'emotion-element--' . $element->getId();
        $collection->getBatchRequest()->setCriteria($key, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $key = 'emotion-element--' . $element->getId();

        $requestedProducts = $collection->getBatchResult()->get($key);
        $requestedProductsArray = $this->structConverter->convertListProductStructList($requestedProducts);

        $element->getData()->set('values', $requestedProductsArray);
        $element->getData()->set('pages', 3);
        $element->getData()->set('ajaxFeed', '');
        $element->getData()->set('article_slider_type', 'selected_article');
        $element->getData()->set('article_slider_arrows', $element->getConfig()->get('show_arrows'));
        $element->getData()->set('article_slider_rotation', $element->getConfig()->get('rotate_automatically'));
        $element->getData()->set('article_slider_scrollspeed', $element->getConfig()->get('scroll_speed'));
        $element->getData()->set('article_slider_rotatespeed', $element->getConfig()->get('rotation_speed'));
    }

    /**
     * @param ShopContextInterface $context
     * @param int                  $limit
     *
     * @return Criteria
     */
    private function createCriteria(ShopContextInterface $context, $limit)
    {
        $criteria = $this->criteriaFactory->createBaseCriteria(
            [$context->getShop()->getCategory()->getId()],
            $context
        );

        $criteria->addBaseCondition(new LiveShoppingCondition());
        $criteria->limit($limit);

        return $criteria;
    }
}
