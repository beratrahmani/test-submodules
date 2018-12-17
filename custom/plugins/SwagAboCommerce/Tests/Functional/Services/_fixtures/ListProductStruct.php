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

namespace SwagAboCommerce\Tests\Functional\Services\_fixtures;

use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;

$priceRule = new PriceRule();

$customerGroup = new Group();
$customerGroup->setId(1);
$customerGroup->setKey('EK');
$customerGroup->setName('Shopkunden');
$customerGroup->setDisplayGrossPrices(true);
$customerGroup->setInsertedGrossPrices(true);
$customerGroup->setUseDiscount(false);
$customerGroup->setPercentageDiscount(0.0);
$customerGroup->setMinimumOrderValue(10.0);
$customerGroup->setSurcharge(5.0);

$productUnit = new Unit();
$productUnit->setId('1');
$productUnit->setName('Liter');
$productUnit->setUnit('1');
$productUnit->setPurchaseUnit(0.5);
$productUnit->setReferenceUnit(1.0);
$productUnit->setPackUnit('Flasche(n)');
$productUnit->setMinPurchase(1);
$productUnit->setMaxPurchase(null);
$productUnit->setPurchaseStep(null);

$priceRule->setId(148);
$priceRule->setPrice(16.798319327731);
$priceRule->setFrom(1);
$priceRule->setTo(5);
$priceRule->setPseudoPrice(0.0);
$priceRule->setCustomerGroup($customerGroup);
$priceRule->setUnit($productUnit);

$price = new Price($priceRule);
$price->setCalculatedPrice(19.99);
$price->setCalculatedReferencePrice(39.98);
$price->setCalculatedPseudoPrice(0.0);

$struct = new \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct(2, 125, 'SW10002.3');
$struct->setCheapestPrice($price);

$tax = new Tax();
$tax->setId(1);
$tax->setName('19%');
$tax->setTax(19.0);

$struct->setTax($tax);

return $struct;
