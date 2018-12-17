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

use Shopware\Components\Plugin\Context\UpdateContext;
use SwagDigitalPublishing\Bootstrap\Components\ElementCompleter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param UpdateContext $context
     * @param ContainerInterface $container
     */
    public function update(UpdateContext $context, ContainerInterface $container)
    {
        $this->container = $container;

        if (version_compare($context->getCurrentVersion(), '1.1.3', '<=')) {
            $this->addElementPreviewField();
            $this->addSliderElementPreviewField();
        }

        if (version_compare($context->getCurrentVersion(), '2.0.0', '<')) {
            $this->completeBannerElements($container);
        }
    }

    /**
     * Adds a new hidden field on plugin update.
     * This field is used to save banner data for showing a preview of a single banner.
     */
    private function addElementPreviewField()
    {
        $sql = "SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-digital-publishing'";
        $componentId = $this->container->get('db')->fetchOne($sql, []);

        $sql = "INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
                VALUES (?, 'digital_publishing_banner_data', 'hiddenfield', 'json', '', '', '', '', '', '', '', '', 1, 10);";
        $this->container->get('db')->query($sql, [$componentId]);
    }

    /**
     * Adds a new hidden field on plugin update.
     * This field is used to save banner data for showing a preview of the slider element.
     */
    private function addSliderElementPreviewField()
    {
        $sql = "SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-digital-publishing-slider'";
        $componentId = $this->container->get('db')->fetchOne($sql, []);

        $sql = "INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
                VALUES (?, 'digital_publishing_slider_preview_data', 'hiddenfield', 'json', '', '', '', '', '', '', '', '', 1, 10);";
        $this->container->get('db')->query($sql, [$componentId]);
    }

    /**
     * Fills the hidden emotion element data fields for missing previews
     *
     * @param ContainerInterface $container
     */
    private function completeBannerElements(ContainerInterface $container)
    {
        $elementCompleter = new ElementCompleter($container);
        $elementCompleter->completeBannerElements();
    }
}
