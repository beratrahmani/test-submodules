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

namespace SwagDigitalPublishing\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as EventArgs;
use Enlight_View_Default;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\MediaService;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

class EmotionDetail implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginPath;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * EmotionDetail constructor.
     *
     * @param string                  $pluginPath
     * @param MediaServiceInterface   $mediaService
     * @param LegacyStructConverter   $legacyStructConverter
     * @param ModelManager            $modelManager
     * @param ContextServiceInterface $contextService
     * @param Connection              $connection
     */
    public function __construct(
        $pluginPath,
        MediaServiceInterface $mediaService,
        LegacyStructConverter $legacyStructConverter,
        ModelManager $modelManager,
        ContextServiceInterface $contextService,
        Connection $connection
    ) {
        $this->pluginPath = $pluginPath;
        $this->mediaService = $mediaService;
        $this->legacyStructConverter = $legacyStructConverter;
        $this->modelManager = $modelManager;
        $this->contextService = $contextService;
        $this->connection = $connection;
    }

    /**
     * @throws \Exception
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onEmotionDetail',
        ];
    }

    /**
     * @param EventArgs $args
     */
    public function onEmotionDetail(EventArgs $args)
    {
        $controller = $args->getSubject();
        $view = $controller->View();

        $view->addTemplateDir($this->pluginPath . '/Resources/views');
        $view->extendsTemplate('backend/emotion/swag_digital_publishing/view/detail/elements/digital_publishing.js');
        $view->extendsTemplate('backend/emotion/swag_digital_publishing/view/detail/elements/digital_publishing_slider.js');

        $action = $args->getRequest()->getActionName();

        if ($action !== 'detail') {
            return;
        }

        $data = $this->overworkDetails($view);

        $view->assign('data', $data);
    }

    /**
     * @param Enlight_View_Default $view
     *
     * @return array|mixed
     */
    private function overworkDetails(Enlight_View_Default $view)
    {
        $data = $view->getAssign('data');

        $defaultShopId = $this->modelManager->getRepository(Shop::class)->getActiveDefault()->getId();
        $context = $this->contextService->createShopContext($defaultShopId);

        $whiteList = [
            'emotion-digital-publishing',
            'emotion-digital-publishing-slider',
        ];

        foreach ($data['elements'] as &$element) {
            if (!in_array($element['component']['xType'], $whiteList, true)) {
                continue;
            }

            foreach ($element['data'] as &$elementData) {
                if ($elementData['valueType'] !== 'json') {
                    continue;
                }

                $previewData = json_decode($elementData['value'], true);

                if (array_key_exists('media', $previewData) && !empty($previewData['media'])) {
                    if ($this->validateMedia($previewData['media'], $context->getBaseUrl())) {
                        continue;
                    }

                    $media = $this->mediaService->getList([$previewData['media']['id']], $context);
                    $media = array_shift($media);

                    if ($media) {
                        $previewData['media'] = $this->legacyStructConverter->convertMediaStruct($media);
                    }

                    $elementData['value'] = json_encode($previewData);

                    $this->fixDatabaseEntry($elementData['id'], json_encode($elementData['value']));
                }
            }
        }

        return $data;
    }

    /**
     * @param array  $mediaObject
     * @param string $baseUrl
     *
     * @return bool
     */
    private function validateMedia(array $mediaObject, $baseUrl)
    {
        return strpos($mediaObject['source'], $baseUrl) !== false;
    }

    /**
     * @param int    $id
     * @param string $newValue (json)
     */
    private function fixDatabaseEntry($id, $newValue)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('s_emotion_element_value')
            ->set('value', ':newValue')
            ->where('id = :id')
            ->setParameters(['newValue' => $newValue, 'id' => $id])
            ->execute();
    }
}
