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

namespace SwagAdvancedCart\Tests\Functional\Services;

use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CartHandlerTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_createWishList()
    {
        $this->loginCustomer();
        $expectedResult = [
            'user_id' => '1',
            'shop_id' => '1',
            'name' => 'FooBar',
        ];

        $this->getCartHandler()->createWishList('FooBar');
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select(['user_id', 'shop_id', 'name'])
            ->from('s_order_basket_saved')
            ->where('name LIKE "%FooBar%"')
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($expectedResult, $result);
    }

    public function test_createWishList_with_no_user_id()
    {
        $expectedResult = 0;
        $result = $this->getCartHandler()->createWishList('NoUserId');
        $this->assertSame($expectedResult, $result);
    }

    public function test_createWishList_unknown_user_id()
    {
        $this->loginCustomer();
        $session = Shopware()->Container()->get('session');
        $session->offsetSet('sUserId', 999);

        $this->expectException(\RuntimeException::class);
        $this->getCartHandler()->createWishList('ExpectException01');
    }

    public function test_createWishList_create_duplicated_wishlist()
    {
        $this->loginCustomer();
        $this->getCartHandler()->createWishList('DuplicatedName');
        $this->expectException(\RuntimeException::class);
        $this->getCartHandler()->createWishList('DuplicatedName');
    }

    public function test_saveCart_without_user_id()
    {
        $result = $this->getCartHandler()->saveCart('TestName', false);

        $this->assertEmpty($result);
    }

    public function test_saveCart_with_false_user_id()
    {
        Shopware()->Session()->offsetSet('sUserId', 1999);

        $result = $this->getCartHandler()->saveCart('TestName', false);

        $expectedResult = ['success' => false];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_addToList_without_user_id()
    {
        $postData = [];
        $postData['ordernumber'] = 'aaa';
        $postData['quantity'] = 1;
        $postData['lists'] = '123';
        $postData['newlist'] = '456';
        Shopware()->Session()->offsetSet('sUserId', null);

        $result = $this->getCartHandler()->addToList($postData);
        $this->assertEmpty($result);
    }

    public function test_addToList_without_order_number()
    {
        $postData = [];
        $postData['ordernumber'] = null;
        $postData['quantity'] = null;
        $postData['lists'] = null;
        $postData['newlist'] = null;

        Shopware()->Session()->offsetSet('sUserId', 1);
        $this->expectException(\RuntimeException::class);
        $this->getCartHandler()->addToList($postData);
    }

    public function test_addToList_without_lists()
    {
        $postData = [];
        $postData['ordernumber'] = 'SW10239';
        $postData['quantity'] = null;
        $postData['lists'] = null;
        $postData['newlist'] = null;

        Shopware()->Session()->offsetSet('sUserId', 1);
        $result = $this->getCartHandler()->addToList($postData);

        $this->assertEquals(['success' => true], $result);
    }

    public function test_addToList_with_false_user_id()
    {
        $this->createCart();

        $postData = [];
        $postData['ordernumber'] = 'SW10239';
        $postData['quantity'] = null;
        $postData['lists'] = [169111];
        $postData['newlist'] = null;

        Shopware()->Session()->offsetSet('sUserId', 99999);

        $this->expectException(\RuntimeException::class);
        $this->getCartHandler()->addToList($postData);
    }

    public function test_addToList_with_false_ordernumber()
    {
        $this->createCart();

        $postData = [];
        $postData['ordernumber'] = 'aFalseNumber';
        $postData['quantity'] = null;
        $postData['lists'] = [169111];
        $postData['newlist'] = null;

        Shopware()->Session()->offsetSet('sUserId', 99999);

        $this->expectException(\RuntimeException::class);
        $this->getCartHandler()->addToList($postData);
    }

    public function test_addToList_with_new_list()
    {
        $this->createCart();

        $postData = [];
        $postData['ordernumber'] = 'SW10156.4';
        $postData['quantity'] = null;
        $postData['lists'] = [];
        $postData['newlist'] = 'NewListName';

        Shopware()->Session()->offsetSet('sUserId', 1);

        $result = $this->getCartHandler()->addToList($postData);

        $this->assertArraySubset(['success' => true], $result);
    }

    public function test_addToList()
    {
        Shopware()->Session()->offsetSet('sUserId', 1);

        $postData = [];
        $postData['ordernumber'] = 'SW10156.4';
        $postData['quantity'] = 1;
        $postData['lists'] = [169111];
        $postData['newlist'] = '';

        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql'));
        $this->getCartHandler()->addToList($postData);

        $result = $this->getConnection()->fetchAll(
            'SELECT article_ordernumber FROM s_order_basket_saved_items WHERE basket_id = 169111'
        );

        $expectedResult = [
            [
                'article_ordernumber' => 'SW10239',
            ],
            [
                'article_ordernumber' => 'SW10038',
            ],
            [
                'article_ordernumber' => 'SW10036',
            ],
            [
                'article_ordernumber' => 'SW10083',
            ],
            [
                'article_ordernumber' => 'SW10156.4',
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_prepareCartForModal()
    {
        $this->createCart();
        $cart = [
            [
                'id' => '169111',
                'cookie_value' => 'customCookieValue',
                'user_id' => '1',
                'shop_id' => '1',
                'expire' => '2018-03-07',
                'modified' => '2017-03-07 08:21:40',
                'name' => 'TestBasket',
                'published' => '0',
                'orderNumbers' => 'SW10239,SW10038,SW10036,SW10083',
            ],
        ];

        $result = $this->getCartHandler()->prepareCartForModal($cart);
        $expectedResult = [
            [
                'id' => '169111',
                'expire' => '2018-03-07',
                'modified' => '2017-03-07 08:21:40',
                'name' => 'TestBasket',
                'published' => '0',
                'hash' => 'customCookieValue',
                'shopId' => '1',
                'cartItems' => [
                    [
                        'id' => 37,
                        'articleOrderNumber' => 'SW10036',
                        'basket_id' => '169111',
                    ],
                    [
                        'id' => 39,
                        'articleOrderNumber' => 'SW10038',
                        'basket_id' => '169111',
                    ],
                    [
                        'id' => 82,
                        'articleOrderNumber' => 'SW10083',
                        'basket_id' => '169111',
                    ],
                    [
                        'id' => 272,
                        'articleOrderNumber' => 'SW10239',
                        'basket_id' => '169111',
                    ],
                ],
            ],
        ];

        $this->assertEquals(array_multisort($expectedResult), array_multisort($result));
    }

    public function test_getCartItemsByOrderNumbers_without_basket_id()
    {
        $this->createCart();
        $orderNumbers = ['SW10239', 'SW10038', 'SW10036', 'SW10083'];

        $result = $this->getCartHandler()->getCartItemsByOrderNumbers($orderNumbers);

        $expectedResult = [
            [
                'id' => 37,
                'articleOrderNumber' => 'SW10036',
            ],
            [
                'id' => 39,
                'articleOrderNumber' => 'SW10038',
            ],
            [
                'id' => 82,
                'articleOrderNumber' => 'SW10083',
            ],
            [
                'id' => 272,
                'articleOrderNumber' => 'SW10239',
            ],
        ];

        $this->assertEquals(array_multisort($expectedResult), array_multisort($result));
    }

    public function test_getCartItemsByOrderNumbers_with_basket_id()
    {
        $this->createCart();
        $orderNumbers = ['SW10239', 'SW10038', 'SW10036', 'SW10083'];

        $result = $this->getCartHandler()->getCartItemsByOrderNumbers($orderNumbers, 169111);

        $expectedResult = [
            [
                'id' => 37,
                'articleOrderNumber' => 'SW10036',
                'basket_id' => 169111,
            ],
            [
                'id' => 39,
                'articleOrderNumber' => 'SW10038',
                'basket_id' => 169111,
            ],
            [
                'id' => 82,
                'articleOrderNumber' => 'SW10083',
                'basket_id' => 169111,
            ],
            [
                'id' => 272,
                'articleOrderNumber' => 'SW10239',
                'basket_id' => 169111,
            ],
        ];

        $this->assertEquals(array_multisort($expectedResult), array_multisort($result));
    }

    private function getCartHandler()
    {
        return self::getKernel()->getContainer()->get('swag_advanced_cart.cart_handler');
    }

    private function getConnection()
    {
        return self::getKernel()->getContainer()->get('dbal_connection');
    }

    private function createCart()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql'));
    }
}
