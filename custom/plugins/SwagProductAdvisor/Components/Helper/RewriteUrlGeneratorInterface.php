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

namespace SwagProductAdvisor\Components\Helper;

/**
 * Class RewriteUrlGenerator
 */
interface RewriteUrlGeneratorInterface
{
    /**
     * Generates the rewrite-urls for all active shops from the advisorId and the name.
     *
     * @param int      $advisorId
     * @param string   $advisorName
     * @param int|null $shopId
     */
    public function createRewriteUrls($advisorId, $advisorName, $shopId = null);

    /**
     * Fills the s_core_rewrite_urls with all the urls from the advisors and its translations.
     *
     * @param string|null $offset
     * @param string|null $limit
     * @param int|null    $shopId
     */
    public function createRewriteTableAdvisor($offset = null, $limit = null, $shopId = null);

    /**
     * Returns the count for the URLs to be generated.
     *
     * @return int
     */
    public function countAdvisorUrls();
}
