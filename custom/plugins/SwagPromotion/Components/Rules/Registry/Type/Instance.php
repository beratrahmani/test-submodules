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

namespace SwagPromotion\Components\Rules\Registry\Type;

use SwagPromotion\Components\Rules\Registry\Type;
use SwagPromotion\Components\Rules\Rule;

/**
 * The instance registry items allows creating rules by cloning the reference instance
 */
class Instance implements Type
{
    /** @var Rule $rule */
    private $rule;

    /** @var bool $isContainer */
    private $isContainer;

    /**
     * @param Rule $rule
     * @param bool $isContainer
     */
    public function __construct(Rule $rule, $isContainer = false)
    {
        $this->rule = $rule;
        $this->isContainer = $isContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $config)
    {
        return clone $this->rule;
    }

    /**
     * {@inheritdoc}
     */
    public function isContainer()
    {
        return $this->isContainer;
    }
}
