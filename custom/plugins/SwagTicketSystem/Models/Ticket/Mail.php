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

namespace SwagTicketSystem\Models\Ticket;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\Shop;

/**
 * History Model represent the s_ticket_support_mails table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_mails")
 */
class Mail extends ModelEntity
{
    /**
     * OWNING SIDE - UNI DIRECTIONAL
     *
     * @var Shop
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="frommail", type="string", nullable=false)
     */
    private $fromMail;

    /**
     * @var string
     *
     * @ORM\Column(name="fromname", type="string", nullable=false)
     */
    private $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", nullable=false)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="contentHTML", type="string", nullable=false)
     */
    private $contentHTML;

    /**
     * @var bool
     *
     * @ORM\Column(name="ishtml", type="boolean", nullable=false)
     */
    private $isHTML = false;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment", type="string", nullable=false)
     */
    private $attachment;

    /**
     * @var bool
     *
     * @ORM\Column(name="sys_dependent", type="boolean", nullable=false)
     */
    private $systemDependent = false;

    /**
     * @var string
     *
     * @ORM\Column(name="isocode", type="string", nullable=false)
     */
    private $isoCode;

    /**
     * @var int
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $contentHTML
     */
    public function setContentHTML($contentHTML)
    {
        $this->contentHTML = $contentHTML;
    }

    /**
     * @return string
     */
    public function getContentHTML()
    {
        return $this->contentHTML;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $fromMail
     */
    public function setFromMail($fromMail)
    {
        $this->fromMail = $fromMail;
    }

    /**
     * @return string
     */
    public function getFromMail()
    {
        return $this->fromMail;
    }

    /**
     * @param string $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param bool $isHTML
     */
    public function setIsHTML($isHTML)
    {
        $this->isHTML = $isHTML;
    }

    /**
     * @return bool
     */
    public function getIsHTML()
    {
        return $this->isHTML;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param bool $systemDependent
     */
    public function setSystemDependent($systemDependent)
    {
        $this->systemDependent = $systemDependent;
    }

    /**
     * @return bool
     */
    public function getSystemDependent()
    {
        return $this->systemDependent;
    }

    /**
     * @param Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }
}
