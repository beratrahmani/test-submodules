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

namespace SwagPromotion\Components\DataProvider;

use Enlight_Components_Db_Adapter_Pdo_Mysql as PdoConnection;

/**
 * Returns the current customer's context
 */
class CustomerDataProvider implements DataProvider
{
    /**
     * @var PdoConnection
     */
    private $db;

    /**
     * CustomerDataProvider constructor.
     *
     * @param PdoConnection $db
     */
    public function __construct(PdoConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Return customer's context
     *
     * {@inheritdoc}
     */
    public function get($context = null)
    {
        $userId = $context;

        return $this->getCustomerData($userId);
    }

    /**
     * Read base data, address data for customerId
     *
     * @param string|null $customerId
     *
     * @return array
     */
    private function getCustomerData($customerId)
    {
        $sql = '
        SELECT
          u.id AS "user::id",
          u.email AS "user::email",
          u.active AS "user::active",
          u.accountmode AS "user::accountmode",
          u.paymentID AS "user::paymentID",
          u.firstlogin AS "user::firstlogin",
          u.lastlogin  AS "user::lastlogin",
          u.customergroup AS "user::customergroup",
          u.paymentpreset AS "user::paymentpreset",
          u.language AS "user::language",
          u.subshopID AS "user::subshopID",
          u.referer AS "user::referer",
          u.internalcomment AS "user::internalcomment",
          u.failedlogins AS "user::failedlogins",
          u.customernumber AS "user::customernumber",
          u.birthday AS "user::birthday"
          
        FROM s_user u

        WHERE u.id = ?
        ';

        $user = $this->db->fetchRow($sql, [$customerId]);

        $user['customer_stream::id'] = $this->db->fetchCol(
            'SELECT stream_id FROM s_customer_streams_mapping WHERE customer_id = ?',
            [$customerId]
        );

        //Query all addresses that belong to the requested user
        $sql = '
        SELECT 

          ua.id AS "address::id",
          ua.company AS "address::company",
          ua.department AS "address::department",
          ua.salutation AS "address::salutation",
          ua.firstname AS "address::firstname",
          ua.lastname AS "address::lastname",
          ua.street AS "address::street",
          ua.zipcode AS "address::zipcode",
          ua.city AS "address::city",
          ua.phone AS "address::phone",
          ua.country_id AS "address::country_id",
          ua.state_id AS "address::state_id",
          ua.ustid AS "address::ustid",
          ua.additional_address_line1 AS "address::additional_address_line_1",
          ua.additional_address_line2 AS "address::additional_address_line_2"

        FROM s_user_addresses ua 
        WHERE ua.user_id = ?
        ';

        $addresses = $this->db->fetchAll($sql, [$customerId]);
        $addressesWithAttributes = [];

        //Query the address attributes for each address
        foreach ($addresses as $address) {
            $id = $address['address::id'];

            $sql = 'SELECT * FROM s_user_addresses_attributes WHERE address_id = ?';
            $attributes = $this->db->fetchRow($sql, [$id]);

            foreach ($attributes as $attribute => $value) {
                $address['address::attribute_' . $attribute] = $value;
            }

            $addressesWithAttributes[] = $address;
        }

        //Assign the addresses including the attributes to the user data
        $user['user::addresses'] = $addressesWithAttributes;

        /**
         * Enrich the customer's data with attributes
         */
        $sql = 'SELECT * FROM s_user_attributes WHERE userID = ?';
        $attributes = $this->db->fetchRow($sql, [$customerId]);
        $attributes = $attributes ?: [];
        foreach ($attributes as $attribute => $value) {
            $user['user::attribute_' . $attribute] = $value;
        }

        return $user ?: [];
    }
}
