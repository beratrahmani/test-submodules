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

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor;

interface ResultHelperInterface
{
    /**
     * Returns every result considering the current page and the "per-page"-value but.
     * Might not return the very first result due to it being the top-hit sometimes.
     *
     * @param Advisor $advisor
     * @param Request $request
     * @param array   $answers
     *
     * @return Advisor
     */
    public function getAdvisorResult(Advisor $advisor, Request $request, array $answers = []);

    /**
     * Returns the top-hit only.
     *
     * @param Advisor $advisor
     * @param Request $request
     *
     * @return array
     */
    public function getTopHit(Advisor $advisor, Request $request);

    /**
     * @param Advisor                 $advisor
     * @param Request                 $request
     * @param ProductContextInterface $context
     *
     * @return Criteria
     */
    public function getCriteria(Advisor $advisor, Request $request, ProductContextInterface $context);

    /**
     * Returns the total count of results
     *
     * @throws \Exception No total count given yet since the result-method was never called
     *
     * @return int
     */
    public function getTotalCount();
}
