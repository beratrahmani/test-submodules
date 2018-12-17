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

use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Shop;
use SwagAdvancedCart\Models\Cart\Cart;
use SwagAdvancedCart\Models\Cart\CartItem;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CartTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_cart_all_getter_should_be_null()
    {
        $cart = new Cart();

        $this->assertNull($cart->getCartItems());
        $this->assertNull($cart->getCustomer());
        $this->assertNull($cart->getExpire());
        $this->assertNull($cart->getHash());
        $this->assertNull($cart->getId());
        $this->assertNull($cart->getModified());
        $this->assertNull($cart->getName());
        $this->assertNull($cart->getPublished());
        $this->assertNull($cart->getShopId());
        $this->assertNull($cart->getShop());
    }

    public function test_set_and_get_CartItems()
    {
        $cart = new Cart();
        $cart->setCartItems([
            new CartItem(),
            new CartItem(),
            new CartItem(),
            new CartItem(),
        ]);

        $this->assertCount(4, $cart->getCartItems());
    }

    public function test_get_and_set_Customer()
    {
        $customer = $this->getContainer()->get('models')->getRepository(Customer::class)->find(1);
        $cart = new Cart();
        $cart->setCustomer($customer);

        $this->assertEquals(1, $cart->getCustomer()->getId());
    }

    public function test_get_and_set_Expire()
    {
        $dateTime = new \DateTime('2010-10-29');
        $cart = new Cart();
        $cart->setExpire($dateTime->getTimestamp());

        $this->assertEquals(1288303200, $cart->getExpire());
    }

    public function test_get_and_set_Hash()
    {
        $cart = new Cart();
        $cart->setHash('notRandomHash');

        $this->assertEquals('notRandomHash', $cart->getHash());
    }

    public function test_get_and_set_Modified()
    {
        $dateTime = new \DateTime('2010-10-29');
        $cart = new Cart();
        $cart->setModified($dateTime->getTimestamp());

        $this->assertEquals(1288303200, $cart->getModified());
    }

    public function test_get_and_set_Name()
    {
        $cart = new Cart();
        $cart->setName('CartName');

        $this->assertEquals('CartName', $cart->getName());
    }

    public function test_get_and_set_Published()
    {
        $cart = new Cart();
        $cart->setPublished(true);

        $this->assertTrue($cart->getPublished());
    }

    public function test_get_and_set_Shop()
    {
        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->find(1);
        $cart = new Cart();
        $cart->setShop($shop);

        $this->assertEquals(1, $cart->getShop()->getId());
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }
}
