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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks
 */
class Support extends ModelEntity
{
    /**
     * OWNING SIDE - UNI DIRECTIONAL
     *
     * @var Customer
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $customer;

    /**
     * OWNING SIDE - UNI DIRECTIONAL
     *
     * @var Shop
     * @ORM\OneToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SwagTicketSystem\Models\Ticket\History", mappedBy="ticket", orphanRemoval=true)
     */
    protected $history;

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
     * @ORM\Column(name="uniqueID", type="string", nullable=false)
     */
    private $uniqueId;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="employeeID", type="integer", nullable=false)
     */
    private $employeeId = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="ticket_typeID", type="integer", nullable=false)
     */
    private $ticketTypeId;

    /**
     * @var int
     *
     * @ORM\Column(name="statusID", type="integer", nullable=false)
     */
    private $statusId = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="formId", type="integer", nullable=false)
     */
    private $formId;

    /**
     * @var int
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", nullable=false)
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="receipt", type="datetime", nullable=false)
     */
    private $receipt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_contact", type="datetime", nullable=false)
     */
    private $lastContact;

    /**
     * @var string
     *
     * @ORM\Column(name="additional", type="string", nullable=false)
     */
    private $additional;

    /**
     * @var string
     *
     * @ORM\Column(name="isocode", type="string", nullable=false)
     */
    private $isoCode;

    /**
     * Owning Side
     *
     * @var Status
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="tickets")
     * @ORM\JoinColumn(name="statusID", referencedColumnName="id")
     */
    private $status;

    /**
     * Owning Side
     *
     * @var Type
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="tickets")
     * @ORM\JoinColumn(name="ticket_typeID", referencedColumnName="id")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="isRead", type="integer", nullable=false, options={"default" = 0})
     */
    private $isRead;

    public function __construct()
    {
        $this->history = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param \DateTime $lastContact
     */
    public function setLastContact($lastContact)
    {
        if (!$lastContact instanceof \DateTime && strlen($lastContact) > 0) {
            $lastContact = new \DateTime($lastContact);
        }
        $this->lastContact = $lastContact;
    }

    /**
     * @return \DateTime
     */
    public function getLastContact()
    {
        return $this->lastContact;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \DateTime $receipt
     */
    public function setReceipt($receipt)
    {
        if (!$receipt instanceof \DateTime && strlen($receipt) > 0) {
            $receipt = new \DateTime($receipt);
        }
        $this->receipt = $receipt;
    }

    /**
     * @return \DateTime
     */
    public function getReceipt()
    {
        return $this->receipt;
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
     * @param string $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @param Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
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

    /**
     * @param ArrayCollection $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return ArrayCollection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param int $formId
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * @return int $formId
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param int $value
     */
    public function setIsRead($value)
    {
        $this->isRead = $value;
    }

    /**
     * @return int $formId
     */
    public function getIsRead()
    {
        return $this->isRead;
    }
}
