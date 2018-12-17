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

namespace SwagDigitalPublishing\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Components\Emotion\ComponentInstaller;
use SwagDigitalPublishing\Bootstrap\Components\Database;
use SwagDigitalPublishing\Bootstrap\Components\EmotionComponents;

class Installer
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ComponentInstaller
     */
    private $emotionComponentInstaller;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @param string             $pluginName
     * @param Connection         $connection
     * @param ComponentInstaller $emotionComponentInstaller
     */
    public function __construct($pluginName, Connection $connection, ComponentInstaller $emotionComponentInstaller)
    {
        $this->dbalConnection = $connection;
        $this->emotionComponentInstaller = $emotionComponentInstaller;
        $this->pluginName = $pluginName;
    }

    public function install()
    {
        $database = new Database($this->dbalConnection);
        $database->install();

        $components = new EmotionComponents($this->pluginName, $this->emotionComponentInstaller);
        $components->install();
    }
}
