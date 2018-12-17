<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class ErrorMessage
{
    /**
     * @var string
     */
    public $sender;

    /**
     * @var string
     */
    public $error;

    /**
     * @var array
     */
    public $params;

    /**
     * @param string $sender
     * @param string $error
     * @param array $params
     */
    public function __construct(string $sender, string $error, array $params = [])
    {
        $this->sender = $sender;
        $this->error = $error;
        $this->params = $params;
    }
}
