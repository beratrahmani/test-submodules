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

namespace SwagAdvancedCart\Models\Cart;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_order_basket_saved_items")
 */
class CartItem extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="basket_id")
     */
    private $basket_id;

    /**
     * @ORM\ManyToOne(targetEntity="SwagAdvancedCart\Models\Cart\Cart", inversedBy="cartItems")
     * @ORM\JoinColumn(name="basket_id", referencedColumnName="id")
     */
    private $cart;

    /**
     * @var string
     *
     * @ORM\Column(name="article_ordernumber")
     */
    private $productOrderNumber;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="article_ordernumber", referencedColumnName="ordernumber")
     */
    private $details;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity")
     */
    private $quantity;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return \SwagAdvancedCart\Models\Cart\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param string $productOrderNumber
     */
    public function setProductOrderNumber($productOrderNumber)
    {
        $this->productOrderNumber = $productOrderNumber;
    }

    /**
     * @return string
     */
    public function getProductOrderNumber()
    {
        return $this->productOrderNumber;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->details = $detail;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getDetail()
    {
        return $this->details;
    }
}
