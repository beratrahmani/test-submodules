<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

class CategoryNode
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $hasChildren;

    /**
     * @param int $id
     * @param string $title
     * @param bool $hasChildren
     */
    public function __construct(int $id, string $title, bool $hasChildren)
    {
        $this->id = $id;
        $this->name = $title;
        $this->hasChildren = $hasChildren;
    }
}
