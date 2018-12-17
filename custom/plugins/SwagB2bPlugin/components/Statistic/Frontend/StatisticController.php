<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Frontend;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\File\CsvWriter;
use Shopware\B2B\Common\File\XlsWriter;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Statistic\Framework\Statistic;
use Shopware\B2B\Statistic\Framework\StatisticRepository;
use Shopware\B2B\Statistic\Framework\StatisticSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StatisticController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var StatisticRepository
     */
    private $statisticRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var CsvWriter
     */
    private $csvWriter;

    /**
     * @var XlsWriter
     */
    private $xlsWriter;

    /**
     * @param AuthenticationService $authenticationService
     * @param StatisticRepository $statisticRepository
     * @param GridHelper $gridHelper
     * @param CsvWriter $csvWriter
     * @param XlsWriter $xlsWriter
     */
    public function __construct(
        AuthenticationService $authenticationService,
        StatisticRepository $statisticRepository,
        GridHelper $gridHelper,
        CsvWriter $csvWriter,
        XlsWriter $xlsWriter
    ) {
        $this->authenticationService = $authenticationService;
        $this->statisticRepository = $statisticRepository;
        $this->gridHelper = $gridHelper;
        $this->csvWriter = $csvWriter;
        $this->xlsWriter = $xlsWriter;
    }

    public function indexAction()
    {
        //nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $searchStruct = $this->createSearchStruct($request);

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $currentPage = $this->gridHelper
            ->getCurrentPage($request);

        $totalCount = $this->statisticRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        if ($currentPage > $maxPage) {
            $currentPage = 1;
            $searchStruct->offset = 0;
        }

        $statistics = $this->statisticRepository
            ->fetchGroupedList($ownershipContext, $searchStruct);

        $contacts = $this->statisticRepository
            ->fetchStatisticContactList($ownershipContext);

        $roles = $this->statisticRepository
            ->fetchStatisticRoleList($ownershipContext);

        $states = $this->statisticRepository
            ->fetchStatisticStatesList($ownershipContext);

        $rows = $this->statisticRepository
            ->fetchList($ownershipContext, $searchStruct);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $rows, $maxPage, $currentPage);

        return [
            'dateFormat' => MysqlRepository::MYSQL_DATE_FORMAT,
            'from' => $searchStruct->from,
            'to' => $searchStruct->to,
            'groupBy' => $searchStruct->groupBy,
            'selects' => $request->getParam('selects'),
            'authId' => $request->getParam('authId'),
            'roleId' => $request->getParam('roleId'),
            'stateId' => (string) $request->getParam('stateId', 'all'),
            'contacts' => $contacts,
            'roles' => $roles,
            'states' => $states,
            'statistics' => $statistics,
            'gridState' => $gridState,
            'postParams' => http_build_query($request->getPost()),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function chartAction(Request $request): array
    {
        return $this->gridAction($request);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function chartDataAction(Request $request): array
    {
        $searchStruct = $this->createSearchStruct($request);

        $searchStruct->limit = null;
        $searchStruct->offset =null;

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $statistics = $this->statisticRepository->fetchGroupedList($ownershipContext, $searchStruct);

        $selects = $request->getParam('selects');

        switch ($searchStruct->groupBy) {
            case 'week':
                $dataFill = $this->fillData($searchStruct->from, $searchStruct->to, 'Y-W', '+1 week');
                break;
            case 'month':
                $dataFill = $this->fillData($searchStruct->from, $searchStruct->to, 'Y-n', '+1 month');
                break;
            case 'year':
            default:
                $dataFill = $this->fillData($searchStruct->from, $searchStruct->to, 'Y-Y', '+1 year');
        }

        $dataSets = [];
        foreach ($selects as $filter) {
            $dataSets[$filter] = $dataFill;
            foreach ($statistics as $statistic) {
                if ($filter === 'orderAmount' || $filter === 'orderAmountNet') {
                    $statistic->$filter = round($statistic->$filter, 2);
                }
                $dataSets[$filter][$statistic->createdAtYear . '-' . $statistic->createdAtGrouping] = $statistic->$filter;
            }
        }

        $labels = $this->getLabels(reset($dataSets), $searchStruct->groupBy);
        foreach ($dataSets as $key => $dataSet) {
            $dataSets[$key] = array_values($dataSet);
        }

        return [
            'labels' => $labels,
            'data' => $dataSets,
        ];
    }

    /**
     * @internal
     * @param array $dataSet
     * @param string $groupBy
     * @return array
     */
    protected function getLabels(array $dataSet, string $groupBy): array
    {
        $labels = [];
        foreach ($dataSet as $date => $value) {
            switch ($groupBy) {
                case 'month':
                    $dateObject = \DateTime::createFromFormat('Y-n', $date);

                    $labels[] = [
                        'month' => $dateObject->format('M'),
                        'year' => $dateObject->format('Y'),
                    ];
                    break;
                case 'week':
                    $dateArray = explode('-', $date);

                    if ($dateArray[1] <= 9) {
                        $dateArray[1] = '0' . $dateArray[1];
                    }

                    $labels[] = $dateArray[1] . ', ' . $dateArray[0];
                    break;
                case 'year':
                    $dateArray = explode('-', $date);
                    $labels[] = $dateArray[0];
            }
        }

        return $labels;
    }

    /**
     * @internal
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $format
     * @param string $modify
     * @return array
     */
    protected function fillData(\DateTime $from, \DateTime $to, string $format, string $modify): array
    {
        $return = [];
        while ($from->format('Ymd') < $to->format('Ymd')) {
            // replace for calender weeks, because datetime has no option to prevent leading zeros
            $return[str_replace('-0', '-', $from->format($format))] = 0;
            $from->modify($modify);
        }

        $return[str_replace('-0', '-', $from->format($format))] = 0;

        return $return;
    }

    /**
     * @internal
     * @param Request $request
     * @return StatisticSearchStruct
     */
    protected function createSearchStruct(Request $request): StatisticSearchStruct
    {
        $searchStruct = new StatisticSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        // date stuff
        $fromString = $request->getParam('from', (new \DateTime())->sub(new \DateInterval('P1Y'))->format(MysqlRepository::MYSQL_DATE_FORMAT));
        $toString = $request->getParam('to', (new \DateTime())->format(MysqlRepository::MYSQL_DATE_FORMAT));

        $from = \DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $fromString . ' 00:00:00');
        $to = \DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $toString . ' 23:59:59');

        if ($to->getTimestamp() > time()) {
            $to->setTimestamp(time());
        }

        if ($from->getTimestamp() > $to->getTimestamp()) {
            $from = \DateTime::createFromFormat(
                MysqlRepository::MYSQL_DATETIME_FORMAT,
                $to->format(MysqlRepository::MYSQL_DATE_FORMAT) . ' 00:00:00'
            );
        }

        $searchStruct->filters[] = $this->statisticRepository
            ->createDateRangeFilter($from, $to);

        $authId = (int) $request->getParam('authId');
        if ($authId) {
            $searchStruct->filters[] = $this->statisticRepository
                ->createEqualsAuthorFilter($authId);
        }

        $roleId = (int) $request->getParam('roleId');
        if ($roleId) {
            $searchStruct->filters[] = $this->statisticRepository
                ->createEqualsRoleFilter($roleId);
        }

        $stateId = (string) $request->getParam('stateId', 'all');
        if ($stateId !== 'all') {
            $searchStruct->filters[] = $this->statisticRepository
                ->createEqualsStatesFilter((int) $stateId);
        }

        $searchStruct->groupBy = $request->getParam('groupBy', 'week');
        $searchStruct->from = $from;
        $searchStruct->to = $to;

        return $searchStruct;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function exportCsvAction(Request $request): array
    {
        $searchStruct = $this->createSearchStruct($request);

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $rows = $this->statisticRepository
            ->fetchList($ownershipContext, $searchStruct);

        $csvData = $this->getStatisticExportData(...$rows);

        $name = tempnam('/tmp', 'csv');

        $this->csvWriter->write($csvData, $name);

        $response = new Response();

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'order-export.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        $csv = file_get_contents($name);

        unlink($name);

        $response->sendHeaders();

        return ['csvData' => $csv];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function exportXlsAction(Request $request): array
    {
        $searchStruct = $this->createSearchStruct($request);

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $rows = $this->statisticRepository
            ->fetchList($ownershipContext, $searchStruct);

        $xlsData = $this->getStatisticExportData(...$rows);

        $name = tempnam('/tmp', 'xls');

        $this->xlsWriter->write($xlsData, $name);

        $response = new Response();

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'order-export.xls'
        );

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'max-age=0');

        $xls = file_get_contents($name);

        unlink($name);

        $response->sendHeaders();

        return ['xlsData' => $xls];
    }

    /**
     * @internal
     * @param Statistic[] $statistics
     * @return array
     */
    protected function getStatisticExportData(Statistic ... $statistics): array
    {
        $return = [];
        /** @var Statistic $statistic */
        foreach ($statistics as $statistic) {
            $statisticArray = $statistic->toArray();
            unset($statisticArray['listId']);
            unset($statisticArray['orderContextId']);

            if ($statisticArray['contact']) {
                $statisticArray['contact'] = $statisticArray['contact']->firstName . ' ' . $statisticArray['contact']->lastName;
            }

            if (count($return) === 0) {
                $return[] = array_keys($statisticArray);
            }

            if ($statistic->clearedAt) {
                $statisticArray['clearedAt'] = $statistic->clearedAt->format(MysqlRepository::MYSQL_DATE_FORMAT);
            }
            $statisticArray['createdAt'] = $statistic->createdAt->format(MysqlRepository::MYSQL_DATE_FORMAT);

            $return[] = $statisticArray;
        }

        return $return;
    }
}
