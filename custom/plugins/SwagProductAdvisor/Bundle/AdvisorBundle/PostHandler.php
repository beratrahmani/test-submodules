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

namespace SwagProductAdvisor\Bundle\AdvisorBundle;

/**
 * Class PostHandler
 */
class PostHandler
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function handle(array $data)
    {
        $questions = [];
        foreach ($data as $key => $value) {
            if (!$this->match($key)) {
                continue;
            }

            $parts = explode('_', $key);
            if (count($parts) <= 1) {
                continue;
            }

            $question = $parts[0];
            $parameter = $parts[1];

            if ($parts[2] === 'min' || $parts[2] === 'max') {
                $questions[$question][$parts[2]] = $value;
                continue;
            }

            $questions[$question][$parameter] = $value;
        }

        return $questions;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function match($key)
    {
        $matches = [];
        preg_match('#q(\d)+_#', $key, $matches);

        return count($matches) > 1;
    }
}
