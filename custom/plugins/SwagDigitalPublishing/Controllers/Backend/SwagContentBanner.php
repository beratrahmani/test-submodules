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

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Shop\Shop;
use SwagDigitalPublishing\Models\ContentBanner;
use SwagDigitalPublishing\Models\Element;
use SwagDigitalPublishing\Models\Layer;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_SwagContentBanner extends Shopware_Controllers_Backend_Application
{
    protected $model = ContentBanner::class;
    protected $alias = 'contentBanner';

    /**
     * Gets the data for a single banner element.
     * Including the media data which is set for the preview background.
     */
    public function detailAction()
    {
        $data = $this->getDetail(
            $this->Request()->getParam('id')
        );

        if (!empty($data['data']['mediaId'])) {
            $defaultShopId = Shopware()->Models()->getRepository(Shop::class)->getActiveDefault()->getId();
            $context = $this->container->get('shopware_storefront.context_service')->createShopContext($defaultShopId);
            $converter = $this->container->get('legacy_struct_converter');
            $media = $this->container->get('shopware_storefront.media_service')->get($data['data']['mediaId'], $context);

            if (!empty($media)) {
                $data['data']['media'] = $converter->convertMediaStruct($media);
            }
        }

        $this->View()->assign($data);
    }

    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        if ($data['mediaId']) {
            $mediaExists = $this->get('dbal_connection')->fetchColumn(
                'SELECT 1 FROM s_media WHERE id = :id',
                ['id' => $data['mediaId']]
            );

            if (!$mediaExists) {
                $data['mediaId'] = null;
            }
        }

        $data = parent::save($data);

        $this->view->assign('data', ['id' => $data['data']['id']]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        if (empty($id)) {
            return ['success' => false, 'error' => 'The id parameter contains no value.'];
        }

        $model = $this->getManager()->find($this->model, $id);

        if (!($model instanceof $this->model)) {
            return ['success' => false, 'error' => 'The passed id parameter exists no more.'];
        }

        $this->deleteTranslations($model);

        $this->getManager()->remove($model);
        $this->getManager()->flush();

        return ['success' => true];
    }

    /**
     * Duplicates a banner including all associated layers and elements.
     */
    public function duplicateAction()
    {
        $bannerId = $this->Request()->getParam('bannerId');

        if ($bannerId === null) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $modelManager = $this->get('models');
        $banner = $modelManager->find($this->model, $bannerId);

        if (!$banner) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $newBanner = clone $banner;

        $modelManager->persist($newBanner);
        $modelManager->flush();

        $this->View()->assign(['success' => true, 'data' => [$newBanner]]);
    }

    /**
     * @param $id
     *
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    protected function getDetailQuery($id)
    {
        $builder = parent::getDetailQuery($id);

        $builder->addSelect(['layers', 'elements'])
            ->leftJoin('contentBanner.layers', 'layers')
            ->addOrderBy('layers.position')
            ->leftJoin('layers.elements', 'elements')
            ->addOrderBy('elements.position');

        return $builder;
    }

    /**
     * Deletes all corresponding translations for the given banner.
     *
     * @param ContentBanner $banner
     */
    private function deleteTranslations($banner)
    {
        $elementIds = [];
        $layerIds = [];

        /** @var Layer $layer */
        foreach ($banner->getLayers()->getIterator() as $layer) {
            $layerIds[] = $layer->getId();
            /** @var Element $element */
            foreach ($layer->getElements()->getIterator() as $element) {
                $elementIds[] = $element->getId();
            }
        }

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $query->delete('s_core_translations')
            ->where('objecttype = "contentBannerElement" AND objectkey IN (:elementIds)')
            ->orWhere('objecttype = "digipubLink" AND objectkey IN (:layerIds)')
            ->setParameter('elementIds', $elementIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('layerIds', $layerIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }
}
