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

class Updater
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Migration300
     */
    private $migrationService;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection, Migration300 $migration)
    {
        $this->connection = $connection;
        $this->migrationService = $migration;
    }

    /**
     * @param string $oldVersion
     */
    public function update($oldVersion)
    {
        if (version_compare($oldVersion, '1.0.8', '<=')) {
            $this->update108();
        }

        if (version_compare($oldVersion, '1.2.6', '<=')) {
            $this->update126();
        }

        if (version_compare($oldVersion, '1.4.1', '<=')) {
            $this->update141();
        }

        if (version_compare($oldVersion, '2.2.1', '<')) {
            $this->updateTo221();
        }

        if (version_compare($oldVersion, '2.3.0', '<')) {
            $this->migrationService->updateTo230();
        }

        $customFacet = new CustomFacet($this->connection);
        $customFacet->install();
    }

    /**
     * updates the version 1.4.1 and before
     */
    private function update141()
    {
        $sql = 'ALTER TABLE `s_plugin_swag_abo_commerce_orders`
                ADD `delivered` INT(11) UNSIGNED;';

        $this->connection->exec($sql);

        $this->connection->createQueryBuilder()
            ->update('s_core_menu')
            ->set('name', 'AboCommerce')
            ->where('name LIKE "Abonnements"')
            ->execute();
    }

    /**
     * updates the version 1.2.6 and before
     */
    private function update126()
    {
        $sql = 'ALTER TABLE `s_plugin_swag_abo_commerce_settings`
                ADD `use_actual_product_price` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
                UPDATE `s_plugin_swag_abo_commerce_settings`
                SET use_actual_product_price = 1;';

        $this->connection->exec($sql);
    }

    /**
     * updates the version 1.0.8 and before
     */
    private function update108()
    {
        $sql = 'ALTER TABLE `s_plugin_swag_abo_commerce_settings`
                ADD `allow_voucher_usage` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
                UPDATE `s_plugin_swag_abo_commerce_settings`
                SET allow_voucher_usage = 1;';

        $this->connection->exec($sql);
    }

    /**
     * Updates older db schemas to version 2.2.1
     */
    private function updateTo221()
    {
        $this->updateAboProductTable();
        $this->updateAboOrderTable();
        $this->updateAboMail();
        $this->addAboTerminationMail();
    }

    private function updateAboProductTable()
    {
        if ($this->columnExists('s_plugin_swag_abo_commerce_articles', 'endless_subscription')) {
            return;
        }

        $sql = <<<SQL
ALTER TABLE `s_plugin_swag_abo_commerce_articles`
CHANGE `min_duration` `min_duration` INT(11) UNSIGNED NULL,
CHANGE `max_duration` `max_duration` INT(11) UNSIGNED NULL,
CHANGE `duration_unit` `duration_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
CHANGE `delivery_interval_unit` `delivery_interval_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
ADD `endless_subscription` TINYINT(1) UNSIGNED NOT NULL,
ADD `period_of_notice_interval` INT(11) UNSIGNED NULL,
ADD `period_of_notice_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
ADD `direct_termination` TINYINT(1) UNSIGNED NULL
SQL;

        $this->connection->exec($sql);
    }

    private function updateAboOrderTable()
    {
        if ($this->columnExists('s_plugin_swag_abo_commerce_orders', 'endless_subscription')) {
            return;
        }

        $sql = <<<SQL
ALTER TABLE `s_plugin_swag_abo_commerce_orders`
CHANGE `duration` `duration` INT(11) NULL,
CHANGE `duration_unit` `duration_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
ADD `endless_subscription` TINYINT(1) UNSIGNED NOT NULL,
ADD `period_of_notice_interval` INT(11) UNSIGNED NULL,
ADD `period_of_notice_unit` VARCHAR(255) COLLATE utf8_unicode_ci NULL,
ADD `direct_termination` TINYINT(1) UNSIGNED NULL,
ADD `termination_date` DATETIME DEFAULT NULL
SQL;

        $this->connection->exec($sql);
    }

    private function updateAboMail()
    {
        $sql = 'UPDATE `s_core_config_mails` 
                SET `frommail` = \'{config name=mail}\', `fromname` = \'{config name=shopName}\', `content` = \'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$billingaddress.firstname} {$billingaddress.lastname},\n \nanbei finden Sie Informationen zu Ihrer Abo-Lieferung:\n\nLaufzeit: {if $aboCommerce.endlessSubscription}unbegrenzt{else}{$aboCommerce.duration} {if $aboCommerce.durationUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if}{/if} \n{if $aboCommerce.endlessSubscription}Kündigungsfrist: {if $aboCommerce.directTermination}jederzeit kündbar{else}{$aboCommerce.periodOfNoticeInterval} {if $aboCommerce.periodOfNoticeUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if}{/if}{/if} \nLieferintervall: {$aboCommerce.deliveryInterval} {if $aboCommerce.deliveryIntervalUnit eq \'\'months\'\'}Monat(e){else}Woche(n){/if} \nLetzte Lieferung: {if $aboCommerce.endlessSubscription && !$aboCommerce.lastRun}-{else}{$aboCommerce.lastRun|date_format}{/if} \n\nPos. Art.Nr.              Menge         Preis        Summe\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8|number_format:2:\'\',\'\':\'\' . \'\'} EUR {$details.amount|padding:8|number_format:2:\'\',\'\':\'\' . \'\'} EUR\n{$details.articlename|wordwrap:49|indent:5}\n{/foreach}\n \nVersandkosten: {$sShippingCosts|number_format:2:\'\',\'\':\'\' . \'\'}\nGesamtkosten Netto: {$sAmountNet|number_format:2:\'\',\'\':\'\' . \'\'}\n{if !$sNet}\nGesamtkosten Brutto: {$sAmount|number_format:2:\'\',\'\':\'\' . \'\'}\n{/if}\n \nGewählte Zahlungsart: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == \"debit\"}\nIhre Bankverbindung:\nKontonr: {$sPaymentTable.account}\nBLZ:{$sPaymentTable.bankcode}\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\n{/if}\n{if $additional.payment.name == \"prepayment\"}\n \nUnsere Bankverbindung:\n{config name=bankAccount}\n{/if}\n \n{if $sComment}\nIhr Kommentar:\n{$sComment}\n{/if}\n \nRechnungsadresse:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street} {$billingaddress.streetnumber}\n{$billingaddress.zipcode} {$billingaddress.city}\n{$billingaddress.phone}\n{$additional.country.countryname}\n \nLieferadresse:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street} {$shippingaddress.streetnumber}\n{$shippingaddress.zipcode} {$shippingaddress.city}\n{$additional.country.countryname}\n \n{if $billingaddress.ustid}\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\n{/if}\n \n \nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \n\nWir wünschen Ihnen noch einen schönen Tag.\n \n{include file=\"string:{config name=emailfooterplain}\"}\'
                WHERE name = \'sABOCOMMERCE\' AND (dirty = 0 OR dirty IS NULL);';

        $this->connection->exec($sql);
    }

    private function addAboTerminationMail()
    {
        $sql = 'INSERT IGNORE INTO `s_core_config_mails` (`stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`)
                VALUES (NULL, \'sABOCOMMERCETERMINATION\', \'{config name=mail}\', \'{config name=shopName}\', \'Ihre Abo-Kündigung\', \'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$aboData.customer.firstname} {$aboData.customer.lastname},\n\nhiermit bestätigen wir Ihnen die Kündigung Ihres Abonnements zum {$dueDate|date_format:\"%d.%m.%Y\"}.\n\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \n\nWir wünschen Ihnen noch einen schönen Tag.\n \n{include file=\"string:{config name=emailfooterplain}\"}\', \'\', 0, \'\', 1, NULL);';

        $this->connection->exec($sql);
    }

    /**
     * Helper function to check if a column exists which is needed during update
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    private function columnExists($tableName, $columnName)
    {
        $sql = 'SHOW COLUMNS FROM ' . $tableName;

        $columns = $this->connection->fetchAll($sql);

        foreach ($columns as $column) {
            if ($column['Field'] === $columnName) {
                return true;
            }
        }

        return false;
    }
}
