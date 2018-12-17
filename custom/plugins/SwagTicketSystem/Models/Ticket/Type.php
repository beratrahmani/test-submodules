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
 * History Model represent the s_ticket_support_types table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_types")
 */
class Type extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection An array of \SwagTicketSystem\Models\Ticket\Support Objects
     *
     * @ORM\OneToMany(targetEntity="SwagTicketSystem\Models\Ticket\Support", mappedBy="type")     *
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
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gridColor", type="string", nullable=false)
     */
    private $gridColor;

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
     * @param string $gridColor
     */
    public function setGridColor($gridColor)
    {
        $this->gridColor = $gridColor;
    }

    /**
     * @return string
     */
    public function getGridColor()
    {
        return $this->gridColor;
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
        return $this->setOneToMany($tickets, Support::class, 'tickets', 'type');
    }
}
