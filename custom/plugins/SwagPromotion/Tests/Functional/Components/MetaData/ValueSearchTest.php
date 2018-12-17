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

namespace SwagPromotion\Tests\Functional;

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\MetaData\ValueSearch;
use SwagPromotion\Tests\DatabaseTestCaseTrait;

/**
 * @small
 */
class ValueSearchTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var array
     */
    protected static $ensureLoadedPlugins = [
        'SwagPromotion' => [],
    ];

    public function test_ValueSearch_product()
    {
        $search = $this->getValueSearch();

        $result = $search->get('product::id', 0, 20, '');
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('product::name', 0, 20, '');
        $this->assertArrayHasKey('name', $result['data'][0]);
        $this->assertArrayHasKey('shortDescription', $result['data'][0]);

        $result = $search->get('product::pricegroupID', 0, 20, '');
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('product::taxID', 0, 20, '');
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('product::supplierID', 0, 20, '');
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);
        $this->assertTrue($result['total'] > 0);

        $result = $search->get('product::description', 0, 20, null);
        $this->assertArrayHasKey('articleName', $result['data'][0]);
        $this->assertArrayHasKey('shortDescription', $result['data'][0]);

        $result = $search->get('product::description_long', 0, 20, null);
        $this->assertArrayHasKey('articleName', $result['data'][0]);
        $this->assertArrayHasKey('shortDescription', $result['data'][0]);

        $result = $search->get('product::keywords', 0, 20, '');
        $this->assertArrayHasKey('name', $result['data'][0]);
        $this->assertArrayHasKey('keywords', $result['data'][0]);
    }

    public function test_ValueSearch_user()
    {
        $search = $this->getValueSearch();

        $result = $search->get('user::id', 0, 20, null);
        $this->assertArrayHasKey('email', $result['data'][0]);
        $this->assertArrayHasKey('firstname', $result['data'][0]);

        $result = $search->get('user::firstname', 0, 20, 'Max');
        $this->assertArrayHasKey('firstname', $result['data'][0]);
        $this->assertEquals('Max', $result['data'][0]['firstname']);
        $this->assertArrayHasKey('email', $result['data'][0]);

        $result = $search->get('user::lastname', 0, 20, 'Mustermann');
        $this->assertArrayHasKey('lastname', $result['data'][0]);
        $this->assertEquals('Mustermann', $result['data'][0]['lastname']);
        $this->assertArrayHasKey('email', $result['data'][0]);

        $result = $search->get('user::paymentID', 0, 20, null);
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('paymentDescription', $result['data'][0]);

        $result = $search->get('user::email', 0, 20, null);
        $this->assertArrayHasKey('email', $result['data'][0]);
        $this->assertArrayHasKey('firstname', $result['data'][0]);

        $result = $search->get('user::accountmode', 0, 20, null);
        $this->assertArrayHasKey('accountmode', $result['data'][0]);
        $this->assertArrayHasKey('description', $result['data'][0]);

        $result = $search->get('user::validation', 0, 20, null);
        $this->assertArrayHasKey('validateGroupKey', $result['data'][0]);

        $result = $search->get('user::paymentpreset', 0, 20, null);
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('paymentDescription', $result['data'][0]);

        $result = $search->get('user::internalcomment', 0, 20, null);
        $this->assertArrayHasKey('internalcomment', $result['data'][0]);
        $this->assertArrayHasKey('firstname', $result['data'][0]);

        $result = $search->get('user::language', 0, 20, null);
        $this->assertArrayHasKey('shopName', $result['data'][0]);

        if (Shopware()->Container()->has('shopware.customer_stream.repository')) {
            $this->execSql("INSERT INTO s_customer_streams (name) VALUES ('test')");
            $result = $search->get('customer_stream::id', 0, 20, null);
            $this->assertArrayHasKey('id', $result['data'][0]);
            $this->assertArrayHasKey('name', $result['data'][0]);
        }
    }

    public function test_ValueSearch_address()
    {
        $search = $this->getValueSearch();

        $result = $search->get('address::country_id', 0, 20, null);

        $this->assertArrayHasKey('countryname', $result['data'][0]);
        $this->assertArrayHasKey('countryen', $result['data'][0]);

        $result = $search->get('address::state_id', 0, 20, null);
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('stateName', $result['data'][0]);
    }

    public function test_ValueSearch_variant()
    {
        $search = $this->getValueSearch();

        $result = $search->get('detail::ordernumber', 0, 20, null);

        $result = $search->get('detail::kind', 0, 20, null);
        $this->assertArrayHasKey('kind', $result['data'][0]);
        $this->assertArrayHasKey('description', $result['data'][0]);

        $result = $search->get('detail::id', 0, 20, null);
        $this->assertArrayHasKey('name', $result['data'][0]);
        $this->assertArrayHasKey('ordernumber', $result['data'][0]);
    }

    public function test_ValueSearch_product_price()
    {
        $search = $this->getValueSearch();

        $result = $search->get('price::id', 0, 20, null);
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('articleName', $result['data'][0]);

        $result = $search->get('price::price', 0, 20, null);
        $this->assertArrayHasKey('netPrice', $result['data'][0]);
        $this->assertArrayHasKey('articleName', $result['data'][0]);

        $result = $search->get('price::to', 0, 20, null);
        $this->assertArrayHasKey('to', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('price::pseudoprice', 0, 20, null);
        $this->assertArrayHasKey('netPseudoprice', $result['data'][0]);
        $this->assertArrayHasKey('articleName', $result['data'][0]);

        $result = $search->get('price::baseprice', 0, 20, null);
        $this->assertArrayHasKey('baseprice', $result['data'][0]);
        $this->assertArrayHasKey('articleName', $result['data'][0]);
    }

    public function test_ValueSearch_category()
    {
        $search = $this->getValueSearch();

        $result = $search->get('categories.id', 0, 20, null);
        $this->assertArrayHasKey('id', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('categories.description', 0, 20, null);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('categories.cmstext', 0, 20, null);
        $this->assertArrayHasKey('description', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);

        $result = $search->get('categories.cmsheadline', 0, 20, null);
        $this->assertArrayHasKey('cmsheadline', $result['data'][0]);
        $this->assertArrayHasKey('name', $result['data'][0]);
    }

    public function test_ValueSearch_invalid_type()
    {
        $search = $this->getValueSearch();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Type foo not defined');
        $search->get('foo::bar', 0, 20, null);
    }

    /**
     * @return ValueSearch
     */
    private function getValueSearch()
    {
        return new ValueSearch(
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('shopware_storefront.additional_text_service'),
            Shopware()->Container()->get('snippets')
        );
    }
}
