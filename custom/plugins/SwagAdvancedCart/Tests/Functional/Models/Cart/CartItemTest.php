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

namespace Shopware\SwagAdvancedCart\Tests\Functional\Models\Cart;

use Shopware\Models\Article\Article;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Models\Cart\CartItem;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CartItemTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_create_cart_item_all_getter_should_be_null()
    {
        $cartItem = new CartItem();

        $this->assertNull($cartItem->getId());
        $this->assertNull($cartItem->getProductOrderNumber());
        $this->assertNull($cartItem->getCart());
        $this->assertNull($cartItem->getDetail());
        $this->assertNull($cartItem->getQuantity());
    }

    public function test_get_set_ArticleOrdernumber()
    {
        $cartItem = new CartItem();
        $cartItem->setProductOrderNumber('212');

        $this->assertEquals('212', $cartItem->getProductOrderNumber());
    }

    public function test_get_set_Cart()
    {
        $cart = new Cart();
        $cartItem = new CartItem();
        $cartItem->setCart($cart);

        $this->assertEquals($cart, $cartItem->getCart());
    }

    public function test_get_set_Detail()
    {
        /** @var Article $product */
        $product = $this->getContainer()->get('models')->getRepository(Article::class)->find(178);
        $cartItem = new CartItem();
        $cartItem->setDetail($product->getDetails());

        $this->assertEquals($product->getDetails(), $cartItem->getDetail());
    }

    public function test_get_set_Quantity()
    {
        $cartItem = new CartItem();
        $cartItem->setQuantity(12);

        $this->assertEquals(12, $cartItem->getQuantity());
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }
}
