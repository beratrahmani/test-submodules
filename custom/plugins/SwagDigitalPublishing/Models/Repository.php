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

namespace SwagDigitalPublishing\Models;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelRepository;

/**
 * Class Repository
 */
class Repository extends ModelRepository
{
    /**
     * Returns complete banner data by id.
     *
     * @param $id
     *
     * @return array
     */
    public function getContentBannerById($id)
    {
        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $query = $connection->createQueryBuilder();

        $query->select(['id', 'name', 'bgType', 'bgOrientation', 'bgMode', 'bgColor', 'mediaId'])
            ->from('s_digital_publishing_content_banner', 'banner')
            ->where('banner.id = :id')
            ->setParameter(':id', $id);

        $data = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            return [];
        }

        $data['layers'] = $this->getLayersByBannerId($id, $connection);

        $layerIds = array_column($data['layers'], 'id');

        $elements = $this->getElements($layerIds, $connection);

        foreach ($elements as $element) {
            $layerId = $element['layerID'];
            $data['layers'][$layerId]['elements'][] = $element;
        }

        return $data;
    }

    /**
     * Returns all layers of a banner by bannerId.
     *
     * @param $id
     * @param Connection $connection
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getLayersByBannerId($id, $connection)
    {
        $query = $connection->createQueryBuilder();

        $query->select(
            [
                'id',
                'label',
                'position',
                'orientation',
                'width',
                'height',
                'link',
                'marginTop',
                'marginRight',
                'marginBottom',
                'marginLeft',
                'borderRadius',
                'bgColor',
            ]
        )
            ->from('s_digital_publishing_layers', 'layers')
            ->where('layers.contentBannerID = :id')
            ->addOrderBy('layers.position')
            ->setParameter(':id', $id);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $layer) {
            $result[$layer['id']] = $layer;
        }

        return $result;
    }

    /**
     * Returns all elements of a layer by layerId.
     *
     * @param $layerIds
     * @param Connection $connection
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getElements($layerIds, $connection)
    {
        $query = $connection->createQueryBuilder();

        $query->select(['id', 'name', 'label', 'position', 'payload', 'layerID'])
            ->from('s_digital_publishing_elements', 'elements')
            ->where('elements.layerID IN (:ids)')
            ->addOrderBy('elements.position')
            ->setParameter(':ids', $layerIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
}
