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

namespace SwagEmotionAdvanced\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as EventArgs;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware_Controllers_Backend_Emotion as BackendEmotionController;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    protected $pluginPath;

    /**
     * @var DataPersister
     */
    private $attributeDataPersister;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param string                $pluginPath
     * @param DataPersister         $attributeDataPersister
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(
        $pluginPath,
        DataPersister $attributeDataPersister,
        MediaServiceInterface $mediaService
    ) {
        $this->pluginPath = $pluginPath;
        $this->attributeDataPersister = $attributeDataPersister;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Emotion' => 'onBackendPostDispatch',
            'Enlight_Controller_Action_PreDispatch_Backend_Emotion' => 'onPreDispatchEmotion',
            'Shopware_Controllers_Backend_Emotion_Detail_Filter_Values' => 'onHandlePreview',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPreDispatchEmotion(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Emotion $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();

        if ($request->getActionName() !== 'save') {
            return;
        }

        $data = $request->getPost();

        if (!isset($data['elements'])) {
            return;
        }

        $elements = $data['elements'];

        foreach ($elements as &$element) {
            foreach ($element['data'] as &$elementData) {
                if ($elementData['key'] === 'sideview_banner') {
                    $elementData['value'] = $this->mediaService->normalize($elementData['value']);
                }
            }
            unset($elementData);
        }
        unset($element);

        $data['elements'] = $elements;

        $request->setParams($data);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     *
     * @return mixed
     */
    public function onHandlePreview(\Enlight_Event_EventArgs $args)
    {
        $entry = $args->getReturn();

        if ($entry['name'] === 'sideview_banner') {
            $entry['value'] = $this->mediaService->getUrl($entry['value']);
        }

        return $entry;
    }

    /**
     * extends the emotion component
     *
     * @param EventArgs $args
     */
    public function onBackendPostDispatch(EventArgs $args)
    {
        /** @var BackendEmotionController $controller */
        $controller = $args->getSubject();
        $view = $controller->View();

        // Add template directory
        $view->addTemplateDir($this->pluginPath . '/Resources/views/');

        $actionName = $args->getRequest()->getActionName();

        switch ($actionName) {
            case 'index':
                $view->extendsTemplate('backend/swag_emotion_advanced/app.js');
                break;
            case 'load':
                $view->extendsTemplate('backend/swag_emotion_advanced/model/emotion.js');
                $view->extendsTemplate('backend/emotion/swag_emotion_advanced/view/components/banner_mapping.js');
                break;
            case 'detail':
                $viewData = $view->getAssign('data');
                $viewData['swagRows'] = isset($viewData['attribute']['swagRows']) ? $viewData['attribute']['swagRows'] : 6;
                $viewData['swagQuickview'] = isset($viewData['attribute']['swagQuickview']) ? $viewData['attribute']['swagQuickview'] : false;
                $view->assign('data', $viewData);
                break;
            case 'save':
            case 'savePreview':
                $this->onEmotionSave($controller);
                break;
        }
    }

    /**
     * @param BackendEmotionController $controller
     */
    private function onEmotionSave(BackendEmotionController $controller)
    {
        $view = $controller->View();
        $viewData = $view->getAssign('data');
        $emotionId = $viewData['id'];

        $attributes = [
            'swag_quickview' => $controller->Request()->getParam('swagQuickview', false),
            'swag_rows' => $controller->Request()->getParam('swagRows', 6),
        ];

        $this->attributeDataPersister->persist($attributes, 's_emotion_attributes', $emotionId);

        $view->assign('data', $viewData);
    }
}
