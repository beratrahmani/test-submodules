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

namespace SwagPromotion\Struct;

class Promotion extends BaseStruct
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $number;

    /**
     * @var array[]
     */
    public $rules;

    /**
     * @var array[]
     */
    public $applyRules;

    /**
     * @var string
     */
    public $validFrom;

    /**
     * @var string
     */
    public $validTo;

    /**
     * @var string
     */
    public $stackMode;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var int
     */
    public $step;

    /**
     * @var int
     */
    public $maxQuantity;

    /**
     * @var bool
     */
    public $stopProcessing;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $shippingFree;

    /**
     * @var int
     */
    public $maxUsage;

    /**
     * @var int
     */
    public $voucher;

    /**
     * @var bool
     */
    public $disallowVouchers;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $detailDescription;

    /**
     * @var bool
     */
    public $exclusive;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var array
     */
    public $freeGoods;

    /**
     * @var array
     */
    public $shops;

    /**
     * @var array
     */
    public $customerGroups;

    /**
     * @var array
     */
    public $doNotAllowLater;

    /**
     * @var array
     */
    public $doNotRunAfter;

    /**
     * @var string
     */
    public $chunkMode;

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var bool
     */
    public $showBadge;

    /**
     * @var string
     */
    public $badgeText;

    /**
     * @var bool
     */
    public $applyRulesFirst;

    /**
     * @var bool
     */
    public $showHintInBasket;
}
