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

namespace ShopwarePlugins\SwagDigitalPublishing\tests\Functional\Controllers\Backend;

require_once __DIR__ . '/../../../../Controllers/Backend/SwagContentBanner.php';

use Enlight_Controller_Request_Request;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\QueryBuilder;
use SwagDigitalPublishing\Models\ContentBanner as ContentBannerModel;
use SwagDigitalPublishing\Services\ContentBanner;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class SwagContentBannerTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    public $container;

    public function test_getDetailQuery_there_should_be_a_queryBuilder()
    {
        $this->createBanner();
        $controller = new SwagContentBannerMock(ContentBanner::class, Shopware()->Container());

        $result = $controller->getDetailQueryMock(3500993);

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function test_detailAction_there_should_be_a_banner_in_the_view()
    {
        $this->createBanner();
        $controller = new SwagContentBannerMock(ContentBannerModel::class, Shopware()->Container());
        $controller->setToRequest('id', 3500993);

        $controller->detailAction();

        $view = $controller->getView();

        $subset = [
            'success' => true,
            'data' => [
                'id' => 3500993,
                'name' => 'Test Banner 3',
            ],
        ];

        $this->assertArraySubset($subset, $view->viewAssign);
    }

    public function test_save_with_deleted_images()
    {
        $subset = [
            'name' => 'Hintergrund',
            'bgType' => 'image',
            'bgOrientation' => 'center center',
            'bgMode' => 'cover',
            'bgColor' => '#001DF7',
            'mediaId' => null,
        ];

        $data = require __DIR__ . '/Fixtures/BannerArrayDeletedImages.php';

        $controller = new SwagContentBannerMock(ContentBannerModel::class, Shopware()->Container());
        $controller->save($data);

        $result = Shopware()->Container()->get('dbal_connection')->fetchAll(
            'SELECT * FROM `s_digital_publishing_content_banner` WHERE `name` = "Hintergrund"'
        );

        $this->assertArraySubset($subset, array_shift($result));
    }

    public function test_duplicateAction()
    {
        $this->createBanner();
        $controller = new SwagContentBannerMock(ContentBannerModel::class, Shopware()->Container());
        $controller->setToRequest('bannerId', 3500993);

        $controller->duplicateAction();
        $result = $controller->getView();

        $subset = [
            'success' => true,
        ];

        $this->assertArraySubset($subset, $result->viewAssign);
        $this->assertInstanceOf(ContentBannerModel::class, $result->viewAssign['data'][0]);
    }

    public function test_deleteAction()
    {
        $this->createBanner();
        $this->createBannerTranslation();
        $controller = new SwagContentBannerMock(ContentBannerModel::class, Shopware()->Container());
        $controller->delete(3500993);

        $controller->setToRequest('id', 3500993);
        $controller->detailAction();
        $view = $controller->getView();

        $this->assertEmpty($view->getData());

        $this->assertEquals(0, $this->countTranslations(10000000, 10000004));
    }

    private function countTranslations($start, $end)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = Shopware()->Container()->get('models')->getConnection()->createQueryBuilder();

        return $query->select('COUNT(id)')
            ->from('s_core_translations')
            ->where('id >= :start ')
            ->andWhere('id <= :end')
            ->andWhere('objecttype = "contentBannerElement" OR objecttype = "digipubLink"')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->execute()->fetchColumn();
    }

    private function createBanner()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/banners.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }

    private function createBannerTranslation()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/banner_translation.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }
}

class SwagContentBannerMock extends \Shopware_Controllers_Backend_SwagContentBanner
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @var SwagContentBannerViewMock
     */
    public $view;

    /**
     * @var string
     */
    public $model;

    /**
     * @var Enlight_Controller_Request_Request
     */
    public $request;

    /**
     * @param string    $model
     * @param Container $container
     */
    public function __construct($model, Container $container)
    {
        $this->model = $model;
        $this->container = $container;
        $this->view = new SwagContentBannerViewMock();
        $this->request = new SwagContentBannerRequestMock();
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->container->get($string);
    }

    public function getDetailQueryMock($id)
    {
        return $this->getDetailQuery($id);
    }

    public function setToRequest($key, $value)
    {
        $this->request->setParam($key, $value);
    }

    public function getView()
    {
        return $this->view;
    }
}

class SwagContentBannerRequestMock
{
    public $params = [];

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function getParam($key)
    {
        return $this->params[$key];
    }
}

class SwagContentBannerViewMock
{
    public $viewAssign;

    public function assign($value)
    {
        $this->viewAssign = $value;
    }

    public function getData()
    {
        return $this->viewAssign['data'];
    }
}
