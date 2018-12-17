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

namespace SwagProductAdvisor\Bootstrap;

use Doctrine\DBAL\Connection;

class DatabaseHandler
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function installDatabase()
    {
        $this->createAdvisorTable();
        $this->createQuestionTable();
        $this->createAnswerTable();
        $this->createSessionTable();
    }

    public function uninstallDatabase()
    {
        $sql = '
            SET foreign_key_checks = 0;
            DROP TABLE IF EXISTS s_plugin_product_advisor_sessions;
            DROP TABLE IF EXISTS s_plugin_product_advisor_answer;
            DROP TABLE IF EXISTS s_plugin_product_advisor_question;
            DROP TABLE IF EXISTS s_plugin_product_advisor_advisor;
            SET foreign_key_checks = 1;
        ';

        $this->connection->exec($sql);
    }

    private function createAdvisorTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_product_advisor_advisor` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `stream_id` int(11) NOT NULL,
                  `teaser_banner_id` int(11) DEFAULT NULL,
                  `active` tinyint(1) NOT NULL,
                  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `description` longtext COLLATE utf8_unicode_ci,
                  `info_link_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `button_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `remaining_posts_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `listing_title_filtered` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `highlight_top_hit` tinyint(1) NOT NULL,
                  `top_hit_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `min_matching_attributes` int(11) DEFAULT NULL,
                  `listing_layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `mode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `last_listing_sort` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_SPPAA_teaser_banner_id` (`teaser_banner_id`),
                  KEY `IDX__SPPAA_stream_id` (`stream_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    private function createQuestionTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_product_advisor_question` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `advisor_id` int(11) DEFAULT NULL,
                  `order` int(11) DEFAULT NULL,
                  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `exclude` tinyint(1) NOT NULL,
                  `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `template` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `info_text` longtext COLLATE utf8_unicode_ci,
                  `configuration` longtext COLLATE utf8_unicode_ci,
                  `number_of_rows` int(11) DEFAULT NULL,
                  `number_of_columns` int(11) DEFAULT NULL,
                  `needs_to_be_answered` tinyint(1) NOT NULL,
                  `expand_question` tinyint(1) NOT NULL,
                  `column_height` int(11) DEFAULT NULL,
                  `boost` int(11) NOT NULL,
                  `multiple_answers` tinyint(1) NOT NULL,
                  `hide_text` tinyint(1) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_SPPAQ_advisor_id` (`advisor_id`),
                  CONSTRAINT `FK_SPPAQ_to_SPPAA_advisor_id` FOREIGN KEY (`advisor_id`) REFERENCES `s_plugin_product_advisor_advisor` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    private function createAnswerTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_product_advisor_answer` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `question_id` int(11) DEFAULT NULL,
                  `order` int(11) DEFAULT NULL,
                  `answer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `row_id` int(11) DEFAULT NULL,
                  `column_id` int(11) DEFAULT NULL,
                  `target_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `media_id` int(11) DEFAULT NULL,
                  `css_class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_SPPAAns_media_id` (`media_id`),
                  KEY `IDX_SPPAAns_question_id` (`question_id`),
                  CONSTRAINT `FK_SPPAAns_to_SPPAQ_question_id` FOREIGN KEY (`question_id`) REFERENCES `s_plugin_product_advisor_question` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    private function createSessionTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_product_advisor_sessions` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `advisor_id` int(11) DEFAULT NULL,
                  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
                  `date` date NOT NULL,
                  `user_id` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_SPPAS_advisor_id` (`advisor_id`),
                  KEY `IDX_SPPAS_user_id` (`user_id`),
                  CONSTRAINT `FK_SPPAS_to_SPPAA_advisor_id` FOREIGN KEY (`advisor_id`) REFERENCES `s_plugin_product_advisor_advisor` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }
}
