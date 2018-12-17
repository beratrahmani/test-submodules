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

namespace SwagLiveShopping\Tests\Functional\Mocks;

class LiveShoppingViewMock
{
    /**
     * @var array
     */
    public $templates = [];

    /**
     * @var array
     */
    public $viewAssign = [];

    /**
     * @param string $templatePath
     */
    public function extendsTemplate($templatePath)
    {
        $this->templates[] = $templatePath;
    }

    /**
     * @param string $key
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function getAssign($key)
    {
        if (isset($this->viewAssign[$key])) {
            return $this->viewAssign[$key];
        }

        throw new \RuntimeException(
            sprintf('The requested view variable %s is not set', $key)
        );
    }

    /**
     * @param string|array $key
     * @param mixed        $value
     */
    public function assign($key, $value = null)
    {
        if (!$value && is_array($key)) {
            foreach ($key as $i => $v) {
                $this->viewAssign[$i] = $v;
            }

            return;
        }

        $this->viewAssign[$key] = $value;
    }

    public function resetAssign()
    {
        $this->viewAssign = [];
    }
}
