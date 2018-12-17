<?php declare(strict_types = 1);

namespace Shopware\B2B\FastOrder\Framework;

use Shopware\B2B\Common\File\CsvContext;

class FastOrderContext extends CsvContext
{
    /**
     * @var int
     */
    public $orderNumberColumn;

    /**
     * @var int
     */
    public $quantityColumn;

    /**
     * @param int $orderNumberColumn
     * @param int $quantityColumn
     * @param string $csvDelimiter
     * @param string|null $csvEnclosure
     * @param bool $headline
     */
    public function __construct(
        $orderNumberColumn = 0,
        $quantityColumn = 1,
        $csvDelimiter = ',',
        $csvEnclosure = null,
        $headline = false
    ) {
        parent::__construct($csvDelimiter, $csvEnclosure, $headline);
        $this->orderNumberColumn = $orderNumberColumn;
        $this->quantityColumn = $quantityColumn;
    }
}
