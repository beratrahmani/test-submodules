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

namespace SwagProductAdvisor\Bundle\AdvisorBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use SwagProductAdvisor\Bundle\AdvisorBundle\Question\QuestionMatch;

/**
 * Class AdvisorAttribute
 */
class AdvisorAttribute extends Attribute
{
    /**
     * @var QuestionMatch[]
     */
    private $matches = [];

    /**
     * @var QuestionMatch[]
     */
    private $misses = [];

    /**
     * @return QuestionMatch[]
     */
    public function getMatches()
    {
        return json_decode(json_encode($this->matches), true);
    }

    /**
     * @return QuestionMatch[]
     */
    public function getMisses()
    {
        return json_decode(json_encode($this->misses), true);
    }

    /**
     * @param QuestionMatch $match
     */
    public function addMatch(QuestionMatch $match)
    {
        array_push($this->matches, $match);
    }

    /**
     * @param QuestionMatch $match
     */
    public function addMiss(QuestionMatch $match)
    {
        array_push($this->misses, $match);
    }

    /**
     * Helper method to check if this product has at least one match.
     */
    public function hasMatch()
    {
        return !empty($this->matches);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $array = get_object_vars($this);
        $array['hasMatch'] = $this->hasMatch();

        return $array;
    }
}
