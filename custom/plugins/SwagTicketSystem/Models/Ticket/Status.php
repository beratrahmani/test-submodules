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

/**
 * History Model represent the s_ticket_support_status table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_status")
 */
class Status extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="SwagTicketSystem\Models\Ticket\Support", mappedBy="status")
     *
     * @var ArrayCollection An array of \SwagTicketSystem\Models\Ticket\Support Objects
     */
    protected $tickets;

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
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="responsible", type="integer", nullable=false)
     */
    private $responsible;

    /**
     * @var int
     *
     * @ORM\Column(name="closed", type="integer", nullable=false)
     */
    private $closed;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", nullable=false)
     */
    private $color = 0;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return int
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
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
     * @param int $responsible
     */
    public function setResponsible($responsible)
    {
        $this->responsible = $responsible;
    }

    /**
     * @return int
     */
    public function getResponsible()
    {
        return $this->responsible;
    }

    /**
     * @return ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param ArrayCollection|array|null $tickets
     *
     * @return ModelEntity
     */
    public function setTickets($tickets)
    {
        return $this->setOneToMany($tickets, Support::class, 'tickets', 'status');
    }
}
