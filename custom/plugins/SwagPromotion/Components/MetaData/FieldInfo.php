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

namespace SwagPromotion\Components\MetaData;

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

/**
 * Handles available fields one can filter for
 */
class FieldInfo
{
    /**
     * @var array
     */
    private $blacklist = [
        'supplier::img',
        'supplier::id',
        'supplier::link',
        'supplier::description',
        'supplier::meta_title',
        'supplier::meta_description',
        'supplier::meta_keywords',
        'supplier::changed',

        'product::configurator_set_id',
        'product::datum',
        'product::pseudosales',
        'product::changetime',
        'product::crossbundlelook',
        'product::notification',
        'product::template',
        'product::mode',
        'product::available_from',
        'product::available_to',
        'product::main_detail_id',
        'product::laststock',

        'productAttribute::id',
        'productAttribute::articleID',
        'productAttribute::articledetailsID',

        'detail::articleID',
        'detail::unitID',
        'detail::suppliernumber',
        'detail::additionaltext',
        'detail::position',
        'detail::minpurchase',
        'detail::purchasesteps',
        'detail::maxpurchase',
        'detail::referenceunit',
        'detail::packunit',
        'detail::shippingtime',
        'detail::releasedate',

        'categories.parent',
        'categories.position',
        'categories.product_box_layout',
        'categories.blog',
        'categories.path',
        'categories.showfiltergroups',
        'categories.external',
        'categories.hidefilter',
        'categories.hidetop',
        'categories.noviewselect',
        'categories.template',
        'categories.added',
        'categories.stream_id',
        'categories.changed',
        'categories.mediaID',
        'categories.sorting_ids',
        'categories.hide_sortings',
        'categories.facet_ids',

        'user::subshopID',
        'user::encoder',
        'user::password',
        'user::active',
        'user::firstlogin',
        'user::lastlogin',
        'user::confirmationkey',
        'user::sessionID',
        'user::newsletter',
        'user::affiliate',
        'user::referer',
        'user::failedlogins',
        'user::pricegroupID',
        'user::lockedUntil',
        'user::changed',
        'user::doubleOptinRegister',
        'user::doubleOptinEmailSentDate',
        'user::doubleOptinConfirmDate',

        'address::id',

        'price::articleID',
        'price::articledetailsID',
        'price::pricegroup',
    ];

    /**
     * Returns all available fields
     *
     * @return array
     */
    public function get()
    {
        return [
            'product' => $this->filterBlacklistedItems($this->getProductData()),
            'basket' => $this->filterBlacklistedItems($this->getBasketData()),
            'customer' => $this->filterBlacklistedItems($this->getCustomerData()),
        ];
    }

    /**
     * @param string $name
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    private function getClassMetaData($name)
    {
        return Shopware()->Models()->getClassMetadata($name);
    }

    /**
     * Return fields and types for a given model
     *
     * @param string $name
     * @param null   $prefix
     *
     * @return array
     */
    private function getTypesForModel($name, $prefix = null)
    {
        $result = array_column($this->getClassMetaData($name)->fieldMappings, 'type', 'columnName');

        if ($prefix) {
            $newResult = [];
            foreach ($result as $key => $value) {
                $newResult[$prefix . $key] = $value;
            }
            $result = $newResult;
        }

        return $result;
    }

    /**
     * Returns all fields belonging to products
     *
     * @return array
     */
    private function getProductData()
    {
        /**
         * intentionally ignore quantity: quantity only applies for stacks of identical ordernumbers
         * for stacks like "base article" or "global" this might be very confusing.
         * Users should use the `step` configuration of the discount instead
         */
        $productData = array_merge(
            $this->getTypesForModel(Article::class, 'product::'),
            $this->getTypesForModel(ProductAttribute::class, 'productAttribute::'),
            $this->getTypesForModel(Detail::class, 'detail::'),
            $this->getTypesForModel(Price::class, 'price::'),
            $this->getTypesForModel(Supplier::class, 'supplier::'),
            $this->getTypesForModel(Category::class, 'categories.')
        );

        $dataToFilter = [
            'price::from',
            'price::to',
            'price::price',
            'product::name',
            'detail::ordernumber',
            'supplier::name',
        ];

        $sortedData = [];

        foreach ($productData as $key => $data) {
            if (in_array($key, $dataToFilter, true)) {
                $sortedData[$key] = $productData[$key];
                unset($productData[$key]);
            }
        }

        ksort($sortedData);

        $sortedData = array_merge($sortedData, $productData);

        return $sortedData;
    }

    /**
     * Returns all fields belonging to baskets
     *
     * @return array
     */
    private function getBasketData()
    {
        return [
            'amountGross' => 'numeric',
            'amountNet' => 'numeric',
            'numberOfProducts' => 'numeric',
            'shippingFree' => 'boolean',
        ];
    }

    /**
     * Returns all fields belonging to customers
     *
     * @return array
     */
    private function getCustomerData()
    {
        $data['customer_stream::id'] = 'customer_stream';
        $data = array_merge(
            $data,
            $this->getTypesForModel(Customer::class, 'user::'),
            $this->getTypesForModel(Address::class, 'address::')
        );

        return $data;
    }

    /**
     * Filter out items which are on the blacklist
     *
     * @param array $items
     *
     * @return array
     */
    private function filterBlacklistedItems(array $items)
    {
        foreach ($items as $key => $value) {
            if (in_array($key, $this->blacklist, true)) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
