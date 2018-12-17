<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

class InformationMessage
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $params;

    /**
     * @var string
     */
    public $sender;

    /**
     * @param string $sender
     * @param string $type
     * @param array $params
     */
    public function __construct(string $sender, string $type, array $params)
    {
        $this->type = $type;
        $this->params = $params;
        $this->sender = $sender;
    }
}
