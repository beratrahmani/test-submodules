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

namespace SwagProductAdvisor\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\SortingInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;

/**
 * Class AdvisorSorting
 */
class AdvisorSorting implements SortingInterface
{
    /**
     * @var Advisor
     */
    private $advisor;

    /**
     * AdvisorSorting constructor.
     *
     * @param Advisor $advisor
     */
    public function __construct(Advisor $advisor)
    {
        $this->advisor = $advisor;
    }

    /**
     * @return Advisor
     */
    public function getAdvisor()
    {
        return $this->advisor;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'advisor';
    }
}
