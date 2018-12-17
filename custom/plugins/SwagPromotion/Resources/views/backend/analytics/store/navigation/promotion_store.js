/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Analytics Searches Without Results Store
 *
 * @category   Shopware
 * @package    Analytics
 * @copyright  Copyright (c) shopware AG (http://www.shopware.com)
 *
 */

//{namespace name=backend/analytics/view/promotion}
//{block name="backend/analytics/store/navigation/promotionStore"}
Ext.define('Shopware.apps.Analytics.store.navigation.promotionStore', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-promotion-store',
    fields: [
        'id',
        'name',
        'orders',
        'turnover'
    ],
    remoteSort: true,
    proxy: {
        type: 'ajax',
        url: '{url module=backend controller=SwagPromotionAnalytics action=getAnalytics}',
        reader: {
            type: 'json',
            root: 'data',
            currencySign: 'currencySign'
        }
    }
});
//{/block}
