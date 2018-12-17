<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Framework;

class EmotionEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $device;

    /**
     * @var string
     */
    public $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDevice(): int
    {
        return $this->device;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
