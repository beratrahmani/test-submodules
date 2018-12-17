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

namespace SwagProductAdvisor\Tests\Functional\Components;

use SwagProductAdvisor\Bundle\AdvisorBundle\AdvisorService;
use SwagProductAdvisor\Components\Helper\RewriteUrlGeneratorInterface;
use SwagProductAdvisor\Tests\TestCase;

/**
 * Class RewriteUrlGeneratorTest
 */
class RewriteUrlGeneratorTest extends TestCase
{
    /** @var AdvisorService $advisorService */
    public $advisorService;

    /** @var \Doctrine\DBAL\Connection $db */
    public $dbalConnection;
    /** @var RewriteUrlGeneratorInterface $rewriteUrlGenerator */
    private $rewriteUrlGenerator;

    /**
     * Setup the necessary data.
     */
    public function setUp()
    {
        parent::setUp();
        $this->rewriteUrlGenerator = Shopware()->Container()->get('swag_product_advisor.rewrite_url_generator');
        $this->dbalConnection = Shopware()->Container()->get('dbal_connection');
    }

    /**
     * Overwritten to also clean-up the SEO-URLs being generated in this test.
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->cleanUpSeoUrls();
    }

    /**
     * Tests if the seo-URL's are generated properly.
     */
    public function testCreateRewriteTableAdvisor()
    {
        $count = 10;
        for ($i = 0; $i < $count; ++$i) {
            $this::$helper->createAdvisor(['name' => "AdvisorNumber{$i}"]);
        }

        $this->rewriteUrlGenerator->createRewriteTableAdvisor();

        $builder = $this->dbalConnection->createQueryBuilder();
        $rewriteUrls = $builder->select('*')
            ->from('s_core_rewrite_urls', 'rewrite')
            ->where('rewrite.org_path LIKE \'%advisor%\'')
            ->andWhere('path LIKE \'AdvisorNumber%\'')
            ->execute()
            ->fetchAll();

        $shopCountBuilder = $this->dbalConnection->createQueryBuilder();
        $shopCount = $shopCountBuilder->select('COUNT(shop.id)')
            ->from('s_core_shops', 'shop')
            ->where('shop.active = 1')
            ->execute()
            ->fetchColumn();

        $this->assertTrue(count($rewriteUrls) == ($count * $shopCount));
    }

    /**
     * Cleans up all SEO-URLs being generated in this test.
     */
    private function cleanUpSeoUrls()
    {
        $builder = $this->dbalConnection->createQueryBuilder();
        $builder->delete('s_core_rewrite_urls')
            ->where('org_path LIKE \'%advisor%\'')
            ->execute();
    }
}
