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

namespace SwagPromotion\Components\Promotion\DiscountCommand;

use SwagPromotion\Components\Promotion\DiscountCommand\Handler\CommandHandler;

/**
 * Class DiscountCommandRegistry maps Commands to CommandHandler
 */
class DiscountCommandRegistry
{
    /**
     * @var CommandHandler[]
     */
    protected $handler = [];

    /**
     * @param \IteratorAggregate $handler
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return CommandHandler
     */
    public function get($name)
    {
        foreach ($this->handler as $handler) {
            if ($handler->supports($name)) {
                return $handler;
            }
        }

        throw new CommandHandlerNotFoundException(
            sprintf(
                'Command handler %s not defined',
                $name
            )
        );
    }

    /**
     * @param CommandHandler $instance
     */
    public function add($instance)
    {
        $this->handler[] = $instance;
    }
}
