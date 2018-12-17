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

/**
 * History Model represent the s_ticket_support_history table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_history")
 */
class History extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Support
     *
     * @ORM\ManyToOne(targetEntity="SwagTicketSystem\Models\Ticket\Support", inversedBy="history")
     * @ORM\JoinColumn(name="ticketID", referencedColumnName="id")
     */
    protected $ticket;

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="ticketID", type="integer", nullable=false)
     */
    private $ticketId;

    /**
     * @var string
     *
     * @ORM\Column(name="swUser", type="string", nullable=false)
     */
    private $swUser = '';

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", nullable=false)
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
     * @var string
     *
     * @ORM\Column(name="support_type", type="string", nullable=false)
     */
    private $supportType;

    /**
     * @var string
     *
     * @ORM\Column(name="receiver", type="string", nullable=false)
     */
    private $receiver = '';

    /**
     * @var string
     *
     * @ORM\Column(name="direction", type="string", nullable=false)
     */
    private $direction;

    /**
     * @var int
     *
     * @ORM\Column(name="statusId", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param string $supportType
     */
    public function setSupportType($supportType)
    {
        $this->supportType = $supportType;
    }

    /**
     * @return string
     */
    public function getSupportType()
    {
        return $this->supportType;
    }

    /**
     * @param string $swUser
     */
    public function setSwUser($swUser)
    {
        $this->swUser = $swUser;
    }

    /**
     * @return string
     */
    public function getSwUser()
    {
        return $this->swUser;
    }

    /**
     * @param Support $ticket
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @return Support
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @param int $statusId
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;
    }
}
