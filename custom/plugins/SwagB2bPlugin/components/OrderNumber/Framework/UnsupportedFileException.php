<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\B2BException;
use Throwable;

class UnsupportedFileException extends \InvalidArgumentException implements B2BException
{
    /**
     * @var string
     */
    private $fileExtension;

    /**
     * @param string $fileExtension
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $fileExtension,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }
}
