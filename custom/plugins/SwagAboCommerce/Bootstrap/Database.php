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

namespace SwagAboCommerce\Bootstrap;

use Doctrine\DBAL\Connection;

class Database
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

    /**
     * installs all required database tables and default data
     */
    public function install()
    {
        $this->createCommerceTable();
        $this->createPricesTable();
        $this->createOrdersTable();
        $this->createSettingsTable();
        $this->createPaymentMeansTable();

        $this->addAboMail();
        $this->addTerminationMail();
        $this->installDefaultSettings();
    }

    /**
     * removes all plugin database tables and default data
     */
    public function uninstall()
    {
        $sql = 'DROP TABLE IF EXISTS s_plugin_swag_abo_commerce_settings_paymentmeans;
                DROP TABLE IF EXISTS s_plugin_swag_abo_commerce_settings;
                DROP TABLE IF EXISTS s_plugin_swag_abo_commerce_orders;
                DROP TABLE IF EXISTS s_plugin_swag_abo_commerce_prices;
                DROP TABLE IF EXISTS s_plugin_swag_abo_commerce_articles;';

        $this->connection->exec($sql);

        $this->removeAboMails();
    }

    /**
     * creates the "s_plugin_swag_abo_commerce_settings_paymentmeans" table
     */
    private function createPaymentMeansTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_swag_abo_commerce_settings_paymentmeans` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `settings_id` INT(11) UNSIGNED NOT NULL,
          `payment_id` INT(11) UNSIGNED NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `settings_id` (`settings_id`,`payment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates the "s_plugin_swag_abo_commerce_settings" table
     */
    private function createSettingsTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_swag_abo_commerce_settings` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `shop_id` INT(11) UNSIGNED DEFAULT NULL,
          `sidebar_headline` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          `sidebar_text` TEXT COLLATE utf8_unicode_ci NOT NULL,
          `banner_headline` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          `banner_subheadline` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          `sharing_twitter` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
          `sharing_facebook` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
          `sharing_google` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
          `sharing_mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
          `allow_voucher_usage` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
          `use_actual_product_price` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `shop_id` (`shop_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates the "s_plugin_swag_abo_commerce_orders" table
     */
    private function createOrdersTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_swag_abo_commerce_orders` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `customer_id` INT(11) UNSIGNED NOT NULL,
          `order_id` INT(11) UNSIGNED NOT NULL,
          `article_order_detail_id` INT(11) UNSIGNED NOT NULL,
          `discount_order_detail_id` INT(11) UNSIGNED DEFAULT NULL,
          `last_order_id` INT(11) UNSIGNED NOT NULL,
          `endless_subscription` TINYINT(1) UNSIGNED NOT NULL,
          `period_of_notice_interval` INT(11) UNSIGNED NULL,
          `period_of_notice_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
          `direct_termination` TINYINT(1) UNSIGNED NULL,
          `termination_date` DATETIME DEFAULT NULL,
          `duration` INT(11) NULL,
          `duration_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
          `delivery_interval` INT(11) NOT NULL,
          `delivery_interval_unit` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          `due_date` DATE DEFAULT NULL,
          `recent_run` DATETIME DEFAULT NULL,
          `last_run` DATE DEFAULT NULL,
          `created` DATETIME NOT NULL,
          `delivered` INT(11) UNSIGNED,
          `payment_id` INT(11) NULL,
          `billing_address_id` INT(11) NULL,
          `shipping_address_id` INT(11) NULL,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          KEY `article_order_detail_id` (`article_order_detail_id`),
          KEY `discount_order_detail_id` (`discount_order_detail_id`),
          KEY `last_order_id` (`last_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates the "s_plugin_swag_abo_commerce_prices" table
     */
    private function createPricesTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `s_plugin_swag_abo_commerce_prices` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `customer_group_id` INT(11) UNSIGNED NOT NULL,
          `abo_article_id` INT(11) UNSIGNED NOT NULL,
          `duration_from` INT(11) NOT NULL,
          `discount_absolute` DOUBLE DEFAULT NULL,
          `discount_percent` DOUBLE DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `abo_article_id` (`abo_article_id`),
          KEY `customer_group_id` (`customer_group_id`),
          CONSTRAINT `s_plugin_swag_abo_commerce_prices_ibfk_1`
          FOREIGN KEY (`abo_article_id`)
          REFERENCES `s_plugin_swag_abo_commerce_articles` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * creates the "s_plugin_swag_abo_commerce_articles" table
     */
    private function createCommerceTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `s_plugin_swag_abo_commerce_articles` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `article_id` INT(11) UNSIGNED NOT NULL,
          `active` TINYINT(1) UNSIGNED NOT NULL,
          `exclusive` TINYINT(1) UNSIGNED NOT NULL,
          `ordernumber` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          `min_duration` INT(11) UNSIGNED NULL,
          `max_duration` INT(11) UNSIGNED NULL,
          `duration_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
          `min_delivery_interval` INT(11) UNSIGNED NOT NULL,
          `max_delivery_interval` INT(11) UNSIGNED NOT NULL,
          `delivery_interval_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
          `endless_subscription` TINYINT(1) UNSIGNED NOT NULL,
          `period_of_notice_interval` INT(11) UNSIGNED NULL,
          `period_of_notice_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
          `direct_termination` TINYINT(1) UNSIGNED NULL,
          `limited` TINYINT(1) UNSIGNED NOT NULL,
          `max_units_per_week` INT(11) UNSIGNED DEFAULT NULL,
          `description` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `article_id` (`article_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $this->connection->exec($sql);
    }

    /**
     * adds a default aboMail
     */
    private function addAboMail()
    {
        $sql = 'INSERT IGNORE INTO `s_core_config_mails` (`stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`)
VALUES (NULL, \'sABOCOMMERCE\', \'{config name=mail}\', \'{config name=shopName}\', \'Abo-Lieferung\', \'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$billingaddress.firstname} {$billingaddress.lastname},\n \nanbei finden Sie Informationen zu Ihrer Abo-Lieferung:\n\nLaufzeit: {if $aboCommerce.endlessSubscription}unbegrenzt{else}{$aboCommerce.duration} {if $aboCommerce.durationUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if}{/if} \n{if $aboCommerce.endlessSubscription}Kündigungsfrist: {if $aboCommerce.directTermination}jederzeit kündbar{else}{$aboCommerce.periodOfNoticeInterval} {if $aboCommerce.periodOfNoticeUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if}{/if}{/if} \nLieferintervall: {$aboCommerce.deliveryInterval} {if $aboCommerce.deliveryIntervalUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if} \nLetzte Lieferung: {if $aboCommerce.endlessSubscription && !$aboCommerce.lastRun}-{else}{$aboCommerce.lastRun|date_format}{/if} \n\nPos. Art.Nr.              Menge         Preis        Summe\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8|number_format:2:\'\',\'\':\'\' . \'\'} EUR {$details.amount|padding:8|number_format:2:\'\',\'\':\'\' . \'\'} EUR\n{$details.articlename|wordwrap:49|indent:5}\n{/foreach}\n \nVersandkosten: {$sShippingCosts|number_format:2:\'\',\'\':\'\' . \'\'}\nGesamtkosten Netto: {$sAmountNet|number_format:2:\'\',\'\':\'\' . \'\'}\n{if !$sNet}\nGesamtkosten Brutto: {$sAmount|number_format:2:\'\',\'\':\'\' . \'\'}\n{/if}\n \nGewählte Zahlungsart: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == \"debit\"}\nIhre Bankverbindung:\nKontonr: {$sPaymentTable.account}\nBLZ:{$sPaymentTable.bankcode}\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\n{/if}\n{if $additional.payment.name == \"prepayment\"}\n \nUnsere Bankverbindung:\n{config name=bankAccount}\n{/if}\n \n{if $sComment}\nIhr Kommentar:\n{$sComment}\n{/if}\n \nRechnungsadresse:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street} {$billingaddress.streetnumber}\n{$billingaddress.zipcode} {$billingaddress.city}\n{$billingaddress.phone}\n{$additional.country.countryname}\n \nLieferadresse:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street} {$shippingaddress.streetnumber}\n{$shippingaddress.zipcode} {$shippingaddress.city}\n{$additional.country.countryname}\n \n{if $billingaddress.ustid}\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\n{/if}\n \n \nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \n\nWir wünschen Ihnen noch einen schönen Tag.\n \n{include file=\"string:{config name=emailfooterplain}\"}\', \'\', 0, \'\', 1, \'N;\');';

        $this->connection->exec($sql);
    }

    private function addTerminationMail()
    {
        $sql = 'INSERT IGNORE INTO `s_core_config_mails` (`stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`)
                VALUES (NULL, \'sABOCOMMERCETERMINATION\', \'{config name=mail}\', \'{config name=shopName}\', \'Ihre Abo-Kündigung\', \'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$aboData.customer.firstname} {$aboData.customer.lastname},\n\nhiermit bestätigen wir Ihnen die Kündigung Ihres Abonnements zum {$dueDate|date_format:\"%d.%m.%Y\"}.\n\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \n\nWir wünschen Ihnen noch einen schönen Tag.\n \n{include file=\"string:{config name=emailfooterplain}\"}\', \'\', 0, \'\', 1, NULL);';

        $this->connection->exec($sql);
    }

    /**
     * deletes the aboMail
     */
    private function removeAboMails()
    {
        $this->connection->createQueryBuilder()
            ->delete('s_core_config_mails')
            ->where('name = "sABOCOMMERCE" OR name = "sABOCOMMERCETERMINATION"')
            ->execute();
    }

    /**
     * creates the default settings
     */
    private function installDefaultSettings()
    {
        $sql = 'INSERT IGNORE INTO  `s_plugin_swag_abo_commerce_settings` (
          `id` ,
          `shop_id` ,
          `sidebar_headline` ,
          `sidebar_text` ,
          `banner_headline` ,
          `banner_subheadline` ,
          `sharing_twitter` ,
          `sharing_facebook` ,
          `sharing_google` ,
          `sharing_mail`,
          `allow_voucher_usage`
        )
        VALUES (
          1 ,
          NULL ,
          \'Lorem ipsum dolor sit amet\',
          \'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.\',
          \'AboCommerce\',
          \'Lorem ipsum dolor sit amet\',
          \'1\',
          \'1\',
          \'1\',
          \'1\',
          \'0\'
        );';

        $this->connection->exec($sql);
    }
}
