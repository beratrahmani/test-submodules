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

namespace SwagEmotionAdvanced\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;

class MediaPathMigration
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection            $connection
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(
        Connection $connection,
        MediaServiceInterface $mediaService
    ) {
        $this->mediaService = $mediaService;
        $this->connection = $connection;
    }

    public function migrate()
    {
        $values = $this->getAffectedRows();
        $parameter = [];
        $sql = '';
        foreach ($values as $id => $value) {
            $valueKey = 'value_' . $id;
            $valueId = 'id_' . $id;
            $parameter[$valueKey] = $this->mediaService->normalize($value);
            $parameter[$valueId] = $id;
            $sql .= $this->createQuery($valueId, $valueKey);
        }

        if ($sql === '') {
            return;
        }

        $this->connection->executeQuery($sql, $parameter);
    }

    /**
     * @param int    $valueId
     * @param string $valueKey
     *
     * @return string
     */
    private function createQuery($valueId, $valueKey)
    {
        return $this->connection->createQueryBuilder()
                ->update('s_emotion_element_value')
                ->set('value', ':' . $valueKey)
                ->where('id = :' . $valueId)
                ->getSQL() . ';';
    }

    /**
     * @return array
     */
    private function getAffectedRows()
    {
        return $this->connection->createQueryBuilder()
            ->select(['value.id', 'value.value'])
            ->from('s_emotion_element_value', 'value')
            ->join('value', 's_library_component_field', 'field', 'field.id = value.fieldID')
            ->where('field.name = "sideview_banner"')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
