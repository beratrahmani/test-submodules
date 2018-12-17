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

namespace SwagTicketSystem\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;

class MediaPathMigration
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param Connection            $connection
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(Connection $connection, MediaServiceInterface $mediaService)
    {
        $this->connection = $connection;
        $this->mediaService = $mediaService;
    }

    /**
     * Migrates the old media path to the new required media path
     */
    public function migrate()
    {
        $banner = $this->getTicketFiles();
        $sql = '';
        $queryParameter = [];

        if (empty($banner)) {
            return;
        }

        foreach ($banner as $settingId => $bannerPath) {
            $idString = ':id_' . $settingId;
            $valueString = ':value_' . $settingId;
            $sql .= $this->getUpdateQuery($idString, $valueString);
            $queryParameter[$idString] = $settingId;
            $queryParameter[$valueString] = $this->mediaService->normalize($bannerPath);
        }

        if ($sql === '') {
            return;
        }

        $this->connection->executeQuery($sql, $queryParameter);
    }

    /**
     * @return array
     */
    private function getTicketFiles()
    {
        return $this->connection->createQueryBuilder()
            ->select(['id', 'name'])
            ->from('s_ticket_support_files')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param string $idString
     * @param string $valueString
     *
     * @return string
     */
    private function getUpdateQuery($idString, $valueString)
    {
        return $this->connection->createQueryBuilder()
                ->update('s_ticket_support_files')
                ->where('id = ' . $idString)
                ->set('name', $valueString)
                ->getSQL() . ';';
    }
}
