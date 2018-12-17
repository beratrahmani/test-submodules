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

namespace SwagAboCommerce\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Payment\Payment;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_settings")
 */
class Settings extends ModelEntity
{
    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * A list if payments means that are allowed
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\Shopware\Models\Payment\Payment")
     * @ORM\JoinTable(
     *     name="s_plugin_swag_abo_commerce_settings_paymentmeans",
     *     joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="payment_id", referencedColumnName="id")}
     * )
     */
    private $payments;

    /**
     * @var bool
     *
     * @ORM\Column(name="sharing_twitter", type="boolean", nullable=false)
     */
    private $sharingTwitter = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="sharing_facebook", type="boolean", nullable=false)
     */
    private $sharingFacebook = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="sharing_google", type="boolean", nullable=false)
     */
    private $sharingGoogle = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="sharing_mail", type="boolean", nullable=false)
     */
    private $sharingMail = true;

    /**
     * @var string
     *
     * @ORM\Column(name="sidebar_headline", type="string", nullable=false)
     */
    private $sidebarHeadline = '';

    /**
     * @var string
     *
     * @ORM\Column(name="sidebar_text", type="string", nullable=false)
     */
    private $sidebarText = '';

    /**
     * @var string
     *
     * @ORM\Column(name="banner_headline", type="string", nullable=false)
     */
    private $bannerHeadline = '';

    /**
     * @var string
     *
     * @ORM\Column(name="banner_subheadline", type="string", nullable=false)
     */
    private $bannerSubheadline = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_voucher_usage", type="boolean", nullable=false)
     */
    private $allowVoucherUsage = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_actual_product_price", type="boolean", nullable=false)
     */
    private $useActualProductPrice;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ArrayCollection $payments
     *
     * @return Settings
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Payment $payment
     *
     * @return Settings
     */
    public function addPayment(Payment $payment)
    {
        $this->payments->add($payment);

        return $this;
    }

    /**
     * @param string $bannerHeadline
     *
     * @return Settings
     */
    public function setBannerHeadline($bannerHeadline)
    {
        $this->bannerHeadline = $bannerHeadline;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerHeadline()
    {
        return $this->bannerHeadline;
    }

    /**
     * @param string $bannerSubheadline
     *
     * @return Settings
     */
    public function setBannerSubheadline($bannerSubheadline)
    {
        $this->bannerSubheadline = $bannerSubheadline;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerSubheadline()
    {
        return $this->bannerSubheadline;
    }

    /**
     * @param bool $sharingFacebook
     *
     * @return Settings
     */
    public function setSharingFacebook($sharingFacebook)
    {
        $this->sharingFacebook = $sharingFacebook;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSharingFacebook()
    {
        return $this->sharingFacebook;
    }

    /**
     * @param bool $sharingGoogle
     *
     * @return Settings
     */
    public function setSharingGoogle($sharingGoogle)
    {
        $this->sharingGoogle = $sharingGoogle;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSharingGoogle()
    {
        return $this->sharingGoogle;
    }

    /**
     * @param bool $sharingMail
     *
     * @return Settings
     */
    public function setSharingMail($sharingMail)
    {
        $this->sharingMail = $sharingMail;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSharingMail()
    {
        return $this->sharingMail;
    }

    /**
     * @param bool $sharingTwitter
     *
     * @return Settings
     */
    public function setSharingTwitter($sharingTwitter)
    {
        $this->sharingTwitter = $sharingTwitter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSharingTwitter()
    {
        return $this->sharingTwitter;
    }

    /**
     * @param string $sidebarHeadline
     *
     * @return Settings
     */
    public function setSidebarHeadline($sidebarHeadline)
    {
        $this->sidebarHeadline = $sidebarHeadline;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarHeadline()
    {
        return $this->sidebarHeadline;
    }

    /**
     * @param string $sidebarText
     *
     * @return Settings
     */
    public function setSidebarText($sidebarText)
    {
        $this->sidebarText = $sidebarText;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarText()
    {
        return $this->sidebarText;
    }

    /**
     * @return bool
     */
    public function getAllowVoucherUsage()
    {
        return $this->allowVoucherUsage;
    }

    /**
     * @param bool $allowVoucherUsage
     */
    public function setAllowVoucherUsage($allowVoucherUsage)
    {
        $this->allowVoucherUsage = $allowVoucherUsage;
    }

    /**
     * @return bool
     */
    public function isUseActualProductPrice()
    {
        return $this->useActualProductPrice;
    }

    /**
     * @param bool $useActualProductPrice
     *
     * @return $this
     */
    public function setUseActualProductPrice($useActualProductPrice)
    {
        $this->useActualProductPrice = $useActualProductPrice;

        return $this;
    }
}
