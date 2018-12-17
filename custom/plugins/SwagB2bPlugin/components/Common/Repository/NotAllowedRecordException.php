<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Repository;

use Shopware\B2B\Common\B2BTranslatableException;
use Throwable;

class NotAllowedRecordException extends \DomainException implements B2BTranslatableException
{
    /**
     * @var string
     */
    private $translationMessage;

    /**
     * @var array
     */
    private $translationParams;

    /**
     * @param string $message
     * @param string $translationMessage
     * @param array $translationParams
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = '',
        string $translationMessage = '',
        array $translationParams = [],
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->translationMessage = $translationMessage;
        $this->translationParams = $translationParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationMessage(): string
    {
        return $this->translationMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationParams(): array
    {
        return $this->translationParams;
    }
}
