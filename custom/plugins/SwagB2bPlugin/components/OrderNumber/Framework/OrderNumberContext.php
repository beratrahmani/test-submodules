<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\File\CsvContext;

class OrderNumberContext extends CsvContext
{
    /**
     * @var int
     */
    public $orderNumberColumn;

    /**
     * @var int
     */
    public $customOrderNumberColumn;

    /**
     * @param int $orderNumberColumn
     * @param int $customOrderNumberColumn
     * @param string $csvDelimiter
     * @param string|null $csvEnclosure
     * @param bool $headline
     */
    public function __construct(
        $orderNumberColumn = 0,
        $customOrderNumberColumn = 1,
        $csvDelimiter = ',',
        $csvEnclosure = null,
        $headline = false
    ) {
        parent::__construct($csvDelimiter, $csvEnclosure, $headline);
        $this->orderNumberColumn = $orderNumberColumn;
        $this->customOrderNumberColumn = $customOrderNumberColumn;
    }
}
