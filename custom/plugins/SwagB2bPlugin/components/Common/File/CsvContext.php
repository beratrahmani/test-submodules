<?php declare(strict_types=1);

namespace Shopware\B2B\Common\File;

class CsvContext
{
    /**
     * @var string
     */
    public $csvDelimiter;

    /**
     * @var string|null
     */
    public $csvEnclosure;

    /**
     * @var bool
     */
    public $headline;

    /**
     * @param string $csvDelimiter
     * @param string|null $csvEnclosure
     * @param bool $headline
     */
    public function __construct(
        $csvDelimiter = ',',
        $csvEnclosure = null,
        $headline = false
    ) {
        $this->csvDelimiter = $csvDelimiter;
        $this->csvEnclosure = $csvEnclosure;
        $this->headline = $headline;
    }
}
