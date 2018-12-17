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

namespace SwagDigitalPublishing\Bootstrap\Components;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Models\Shop\Shop;
use SwagDigitalPublishing\Services\ContentBanner;
use SwagDigitalPublishing\Services\PopulateElementHandlerFactory;
use SwagDigitalPublishing\Services\TranslationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElementCompleter
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

    public function completeBannerElements()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');

        $componentId = $this->getComponentId();
        $bannerIdFieldId = $this->getFieldId();
        $dataFieldId = $this->getDataFieldId();
        $incompleteElements = $this->getIncompleteElements($componentId);

        if (empty($incompleteElements)) {
            return;
        }

        /** @var ContentBanner $contentBannerService */
        $contentBannerService = $this->getBannerService();
        $defaultShopId = $this->container->get('models')->getRepository(Shop::class)->getActiveDefault()->getId();
        $context = $this->container->get('shopware_storefront.context_service')->createShopContext($defaultShopId);

        foreach ($incompleteElements as $elementId => $elementValues) {
            $needsUpdate = true;
            $bannerContentId = null;
            $emotionId = null;

            foreach ($elementValues as $valueField) {
                $emotionId = $valueField['emotionID'];

                switch ($valueField['fieldID']) {
                    case $dataFieldId:
                        $needsUpdate = false;
                        break;
                    case $bannerIdFieldId:
                        $bannerContentId = $valueField['value'];
                        break;
                }
            }

            if (!$needsUpdate || $bannerContentId === null || $emotionId === null) {
                continue;
            }

            $data = $contentBannerService->get($bannerContentId, $context);

            $insertData = [
                'emotionID' => $emotionId,
                'componentID' => $componentId,
                'fieldID' => $dataFieldId,
                'elementID' => $elementId,
                'value' => json_encode(json_encode($data)),
            ];

            $connection->insert('s_emotion_element_value', $insertData);
        }
    }

    /**
     * @param int $componentId
     *
     * @return array
     */
    private function getIncompleteElements($componentId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select([
            'element.elementID',
            'element.emotionID',
            'element.componentID',
            'element.fieldID',
            'element.value',
        ])
            ->from('s_emotion_element_value', 'element')
            ->where('element.componentID = :componentId')
            ->setParameter('componentId', $componentId)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return bool|string
     */
    private function getDataFieldId()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select('field.id')
            ->from('s_library_component_field', 'field')
            ->where('field.name = "digital_publishing_banner_data"')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return bool|string
     */
    private function getFieldId()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select('field.id')
            ->from('s_library_component_field', 'field')
            ->where('field.name = "digital_publishing_banner_id"')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return bool|string
     */
    private function getComponentId()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $builder->select('component.id')
            ->from('s_library_component', 'component')
            ->where('component.x_type = "emotion-digital-publishing"')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return ContentBanner
     */
    private function getBannerService()
    {
        return new ContentBanner(
            $this->container->get('models'),
            $this->getTranslationService(),
            $this->container->get('shopware_storefront.list_product_service'),
            $this->container->get('shopware_storefront.media_service'),
            $this->getHandlerFactory(),
            $this->container->get('events'),
            $this->container->get('legacy_struct_converter')
        );
    }

    /**
     * @return PopulateElementHandlerFactory
     */
    private function getHandlerFactory()
    {
        return new PopulateElementHandlerFactory(
            $this->container->get('events'),
            $this->container->get('shopware_storefront.list_product_service'),
            $this->container->get('shopware_storefront.media_service'),
            $this->container->get('legacy_struct_converter')
        );
    }

    /**
     * @return TranslationService
     */
    private function getTranslationService()
    {
        return new TranslationService($this->container->get('translation'));
    }
}
