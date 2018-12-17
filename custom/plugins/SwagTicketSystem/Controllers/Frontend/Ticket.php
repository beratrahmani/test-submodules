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
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Form\Field;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Translation as Translator;
use SwagTicketSystem\Components\TicketSystemInterface;
use SwagTicketSystem\Models\Ticket\File;
use SwagTicketSystem\Models\Ticket\History;
use SwagTicketSystem\Models\Ticket\Status;
use SwagTicketSystem\Models\Ticket\Support;
use SwagTicketSystem\Models\Ticket\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * Ticket Frontend Controller this controller implements the frontend logic for the ticket system
 */
class Shopware_Controllers_Frontend_Ticket extends Shopware_Controllers_Frontend_Forms
{
    /**
     * Entity Manager
     *
     * @var Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * Default blacklist file extension
     */
    protected $blackList = [
        'php',
        'php3',
        'php4',
        'php5',
        'phtml',
        'cgi',
        'pl',
        'sh',
        'com',
        'bat',
        '',
        'py',
        'rb',
        'exe',
    ];

    /**
     * on preDispatch set the noRenderer if the actionName is in array
     */
    public function preDispatch()
    {
        $this->manager = $this->get('models');

        if (in_array($this->Request()->getActionName(), ['download', 'upload'])) {
            $this->get('plugins')->Controller()->ViewRenderer()->setNoRender();
        }

        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
    }

    /**
     * Show Ticket form
     */
    public function indexAction()
    {
        $formId = (int) $this->Request()->getParam('sFid');
        $userData = $this->Request()->getParam('userData');

        if (!empty($userData)) {
            $this->presetForm($formId, $userData);
        }

        $this->View()->assign('forceMail', (int) $this->Request()->getParam('forceMail'));
        $this->View()->loadTemplate('frontend/forms/index.tpl');

        $id = $this->Request()->getParam('id');
        if (!empty($id)) {
            $this->View()->assign('sUserLoggedIn', $this->get('modules')->Admin()->sCheckUser());
        }

        $this->View()->assign('showAccountMenu', false);
        $this->View()->assign('showMenuEntry', true);
        $ticketRequest = $this->Request()->getParam('ticketRequest');
        if (!empty($ticketRequest)) {
            $this->View()->assign('showAccountMenu', true);
            $this->View()->assign('showMenuEntry', false);
        }

        parent::indexAction();

        $this->phpMaxSizeUploadError();
    }

    /**
     * Save new ticket into database
     */
    public function commitForm()
    {
        $forceMail = $this->Request()->getParam('forceMail');

        if ($forceMail) {
            return parent::commitForm();
        }

        $id = $this->Request()->getParam((int) $this->Request()->getParam('sFid') ? 'sFid' : 'id');

        $ticketAnswerId = (int) $this->Request()->getParam('answerId');

        $sql = <<<SQL
SELECT *
FROM `s_cms_support_fields`
WHERE `supportID` = :id
ORDER BY `position`
SQL;

        $formFields = $this->get('dbal_connection')->executeQuery($sql, ['id' => $id])->fetchAll();

        $sql = <<<SQL
SELECT *
FROM `s_cms_support`
WHERE `id` = :id
SQL;
        $formData = $this->get('dbal_connection')->executeQuery($sql, ['id' => $id])->fetch();

        if ($formData['ticket_typeID'] == 0) {
            return parent::commitForm();
        }
        $customerEmail = '';
        $userId = 0;

        //create new ticket model
        $ticketModel = new Support();
        $ticketModel->setIsRead(0);
        $ticketModel->setAdditional('');
        $additionalFields = [];
        $uploadFieldName = null;
        $session = $this->get('session');

        foreach ($formFields as $field) {
            //check if it is a special field
            if (empty($field['ticket_task'])) {
                $additionalValue = $this->_postData[$field['id']];

                if (is_array($additionalValue)) {
                    $additionalValue = implode(' / ', $additionalValue);
                }
                $additionalFields[] = [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'typ' => $field['typ'],
                    'value' => stripcslashes($additionalValue),
                ];

                if ($field['typ'] === 'upload') {
                    $uploadFieldName = $field['name'];
                }
            }

            switch ($field['ticket_task']) {
                case 'message':
                    $ticketModel->setMessage($this->_postData[$field['id']]);
                    break;

                case 'email':
                    $sUserMail = $session->offsetGet('sUserMail');
                    if (!empty($sUserMail)) {
                        $this->_postData[$field['id']] = stripcslashes(
                            $session->offsetGet('UserMail')
                        );
                    }
                    $customerEmail = $this->Request()->getPost('email');
                    $ticketModel->setEmail($customerEmail);
                    break;

                case 'author':
                case 'name':
                    $sUserId = $session->offsetGet('sUserId');
                    if (!empty($sUserId)) {
                        $name = $this->get('modules')->Admin()
                            ->sGetUserNameById($session->offsetGet('sUserId'));

                        $this->_postData[$field['id']] = $name['firstname'] . ' ' . $name['lastname'];
                    }

                    $additionalValue = $this->_postData[$field['id']];

                    if (is_array($additionalValue)) {
                        $additionalValue = implode(' / ', $additionalValue);
                    }
                    $additionalFields[] = [
                        'name' => $field['name'],
                        'label' => $field['label'],
                        'typ' => $field['typ'],
                        'value' => stripcslashes($additionalValue),
                    ];
                    break;

                case 'subject':
                    $ticketModel->setSubject($this->_postData[$field['id']]);
                    break;
                default:
                    // do nothing
                    break;
            }

            if (!empty($additionalFields)) {
                $ticketModel->setAdditional(serialize($additionalFields));
            }
        }

        $sUserId = $session->offsetGet('sUserId');
        if (!empty($sUserId)) {
            $userId = $sUserId;
        } elseif (!empty($customerEmail)) {
            $sql = <<<SQL
SELECT id
FROM `s_user`
WHERE `email` = :customerEmail
SQL;
            $userId = (int) $this->get('dbal_connection')->executeQuery($sql, ['customerEmail' => $customerEmail])->fetchColumn();
        }

        $isoCode = !empty($formData['isocode']) ? $formData['isocode'] : 'de';
        $ticketModel->setIsoCode($isoCode);
        $ticketModel->setReceipt(new \DateTime());
        $ticketModel->setLastContact(new \DateTime());
        $ticketModel->setUniqueId(md5(mt_rand(0, 1000) . time()));
        $ticketModel->setFormId($id);

        /** @var Customer $model */
        if (($model = $this->findModelById($userId, Customer::class)) !== null) {
            $ticketModel->setCustomer($model);
        }
        /** @var Status $model */
        if (($model = $this->findModelById(1, Status::class)) !== null) {
            $ticketModel->setStatus($model);
        }

        /** @var Shop $model */
        $model = $this->findModelById($this->get('shop')->getId(), Shop::class);
        if ($model) {
            $ticketModel->setShop($model);
        }

        /** @var Type $model */
        if (($model = $this->findModelById($formData['ticket_typeID'], Type::class)) !== null) {
            $ticketModel->setType($model);
        }

        $this->getManager()->persist($ticketModel);
        $this->getManager()->flush();

        try {
            $this->uploadFile($uploadFieldName, $ticketModel->getId(), $id, $ticketAnswerId);
        } catch (Exception $ex) {
            // Do nothing
        }

        /* @var TicketSystemInterface $ticketSystem */
        $ticketSystem = $this->get('swag_ticket_system.ticket_service');
        $ticketSystem->sendNotificationEmails($ticketModel->getId());
    }

    /**
     * Show ticket history
     */
    public function listingAction()
    {
        $userID = (int) $this->get('session')->offsetGet('sUserId');
        if (empty($userID)) {
            $this->Request()->setParam('sTargetAction', 'swagTicketSystemListing');

            return $this->forward('index', 'account');
        }

        $order = (array) $this->Request()->getParam(
            'sort',
            [
                [
                    'property' => 'ticket.id',
                    'direction' => 'DESC',
                ],
            ]
        );

        $result = $this->getManager()->getRepository(Support::class)->getFrontendTicketListQuery($userID, null, $order);
        $ticketStore = $result->getArrayResult();

        /* pagination */
        $device = $this->Request()->getDeviceType();

        $limitPerPage = 3;
        if ($device === 'tablet') {
            $limitPerPage = 7;
        }

        $destinationPage = $this->Request()->getParam('sPage');
        if (empty($destinationPage) || $destinationPage < 1) {
            $destinationPage = 1;
        }

        $paginator = $this->getManager()->createPaginator($result);
        $paginator->getQuery()->setFirstResult($limitPerPage * ($destinationPage - 1))->setMaxResults($limitPerPage);

        $entries = [];
        foreach ($paginator as $entry) {
            $entries[] = $this->getStatusTranslation($entry);
        }

        $totalEntries = count($ticketStore);
        $numberOfPages = ceil($totalEntries / $limitPerPage);

        /** @var TicketSystemInterface $ticketSystemService */
        $ticketSystemService = $this->get('swag_ticket_system.ticket_service');
        $pages = $ticketSystemService->getPagerStructure($destinationPage, $numberOfPages);

        /* assign view variables */
        $this->View()->assign('sAction', 'listing');
        $this->View()->assign('sNumberPages', $numberOfPages);
        $this->View()->assign('sPages', $pages);
        $this->View()->assign('sPage', $destinationPage);
        $this->View()->assign('entries', $entries);
        $this->View()->assign('sUserLoggedIn', $this->get('modules')->Admin()->sCheckUser());
        $this->View()->assign('ticketStore', $ticketStore);
    }

    /**
     * Open new ticket mask
     */
    public function requestAction()
    {
        /** @var sAdmin $adminModule */
        $adminModule = $this->get('modules')->Admin();
        if (!$adminModule->sCheckUser()) {
            $this->Request()->setParam('sTargetAction', 'swagTicketSystemRequest');

            return $this->forward('index', 'account');
        }

        /** @var CachedConfigReader $cachedConfigReader */
        $cachedConfigReader = $this->get('shopware.plugin.cached_config_reader');
        $config = $cachedConfigReader->getByPluginName('SwagTicketSystem', $this->get('shop'));
        $userData = $this->get('modules')->Admin()->sGetUserData();

        $firstName = $userData['billingaddress']['firstname'];
        $lastName = $userData['billingaddress']['lastname'];
        $userName = $firstName . ' ' . $lastName;
        $email = $userData['additional']['user']['email'];

        $ticketFormId = $config['ticketAccountFormId'];
        if (!$this->isTicketFormIdValid($ticketFormId)) {
            $ticketFormId = $this->getValidFormId();
        }

        $sUserLoggedIn = $this->get('modules')->Admin()->sCheckUser();
        $this->Request()->setParam('sFid', $ticketFormId);

        $this->View()->assign(
            [
                'userName' => $userName,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'sUserLoggedIn' => $sUserLoggedIn,
                'sAction' => 'request',
            ]
        );

        parent::indexAction();
    }

    /**
     * Show ticket details
     */
    public function detailAction()
    {
        $ticketIdentifier = $this->Request()->getParam('tid');
        $ticketUID = $this->Request()->getParam('sAID');

        $ticketId = null;

        if (empty($ticketIdentifier)) {
            $ticketId = (int) $this->getTicketIdByUniqueId($ticketUID);
        } else {
            //Gets the actual ticket Id by the tickets unique Id
            $ticketId = (int) $this->getTicketIdByUniqueId($ticketIdentifier);
        }

        if (!$ticketId) {
            $this->forward('index', 'account');

            return;
        }

        $detailData = $this->getTicketDetailData($ticketId);

        $detailData['additional'] = unserialize($detailData['additional']);

        $dataQuery = $this->getManager()->getRepository(Support::class)->getTicketHistoryQuery($ticketId);
        $ticketHistoryData = $dataQuery->getArrayResult();

        //Show answer field if current ticket status is set to 'open'
        if ($detailData['statusId'] == 1) {
            $detailData['showAnswer'] = true;

            //Hide answer field if custom last message is for 'open' status of ticket
            if ($ticketHistoryData && $ticketHistoryData[0]['direction'] === TicketSystemInterface::ANSWER_DIRECTION_IN) {
                $detailData['showAnswer'] = false;
            }
        }

        $mediaService = $this->container->get('shopware_media.media_service');
        foreach ($ticketHistoryData as &$ticketAnswer) {
            $attachments = $this->getManager()->getRepository(File::class)
                ->findBy(['location' => 'backend', 'answerId' => $ticketAnswer['id']]);
            if ($attachments) {
                /** @var File $attachment */
                foreach ($attachments as $attachment) {
                    $ticketAnswer['attachment'][] = [
                        'name' => $mediaService->getUrl($attachment->getName()),
                        'hash' => $attachment->getHash(),
                    ];
                }
            }
        }
        unset($ticketAnswer);

        $userAttachments = $this->ticketAttachments($ticketId);

        $this->View()->assign('ticketDetails', $detailData);
        $this->View()->assign('userAttachments', $userAttachments);
        $this->View()->assign('ticketHistoryDetails', $ticketHistoryData);
        $this->View()->assign('sUserLoggedIn', $this->get('modules')->Admin()->sCheckUser());
        $this->View()->assign('ticketId', $ticketId);
    }

    /**
     * saves a new answer of the customer to the ticket history
     */
    public function sendAnswerAction()
    {
        $ticketId = (int) $this->Request()->getParam('ticketId');
        if (!$ticketId) {
            $this->forward('index', 'account');

            return;
        }

        $detailData = $this->getTicketDetailData($ticketId);

        $answer = trim(stripslashes($this->Request()->getParam('sAnswer')));

        if (!$answer) {
            return;
        }

        // add answer to ticket system
        $subject = $this->get('snippets')->getNamespace('frontend/ticket/detail')
            ->get('TicketDetailInfoAnswerSubject', 'Answer');

        /* @var TicketSystemInterface $ticketSystem */
        $ticketSystem = $this->get('swag_ticket_system.ticket_service');
        $answerId = $ticketSystem->addMessageToTicketHistory(
            $ticketId,
            $subject,
            nl2br($answer),
            false,
            $this->get('config')->get('mail'),
            TicketSystemInterface::ANSWER_DIRECTION_IN,
            null,
            $detailData['statusId']
        );

        //Change ticket status to 'open'
        $detailData['statusId'] = 1;

        //update ticket status status
        $ticketSystem->setTicketStatus($ticketId, 1);

        //Handle attachments for responsible answer
        $frontendAttachments = $this->getManager()->getRepository(File::class)
            ->findBy(['answerId' => '-1', 'location' => 'frontend', 'ticketId' => $ticketId]);
        if ($frontendAttachments) {
            /** @var File $frontendAttachment */
            foreach ($frontendAttachments as $frontendAttachment) {
                $frontendAttachment->setAnswerId($answerId);

                $this->getManager()->persist($frontendAttachment);
            }

            $this->getManager()->flush();
        }
        //send notification to the customer and the shop host if necessary
        $ticketSystem->sendNotificationEmails($ticketId);
        //set responsible to false so the customer can not answer directly
        $detailData['responsible'] = false;

        $this->redirect([
            'controller' => 'ticket',
            'action' => 'detail',
            'tid' => $detailData['uniqueId'],
        ]);
    }

    /**
     * Download attachment
     *
     * @throws Enlight_Exception
     */
    public function downloadAction()
    {
        $attachmentHash = $this->Request()->getParam('attachment');
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('frontend/ticket/detail');

        $attachment = $this->getManager()->getRepository(File::class)->findOneBy(['hash' => $attachmentHash]);
        if (!$attachment) {
            throw new Enlight_Exception($namespace->get('TicketDownloadAttachmentNotFound', 'No attachment found'));
        }

        $filename = $attachment->getName();

        /** @var \Shopware\Bundle\MediaBundle\MediaService $mediaService */
        $mediaService = $this->get('shopware_media.media_service');

        $filename = $mediaService->encode($mediaService->getUrl($filename));

        if (!file_exists($filename)) {
            throw new Enlight_Exception($namespace->get('TicketDownloadAttachmentDontExist', 'File does not exist'));
        }

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Type', 'application/octet-stream');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . basename($filename));
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', filesize($filename));
        $response->sendHeaders();
        readfile($filename);
    }

    /**
     * Delete action for user uploaded attachment
     */
    public function deleteAction()
    {
        $hashId = $this->Request()->getParam('attachment');

        $redirectUrl = ['controller' => 'ticket', 'action' => 'listing'];

        $attachment = $this->getManager()->getRepository(File::class)->findOneBy(['location' => 'frontend', 'hash' => $hashId]);

        if ($attachment) {
            $this->getManager()->remove($attachment);
            $this->getManager()->flush();

            unlink($attachment->getName());

            $ticket = $this->getManager()->getRepository(Support::class)->find($attachment->getTicketId());

            if ($ticket) {
                $redirectUrl = ['controller' => 'ticket', 'action' => 'detail', 'tid' => $ticket->getUniqueId()];
            }
        }

        $this->redirect($redirectUrl);
    }

    /**
     * Upload function for current answer
     */
    public function uploadAction()
    {
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('frontend/ticket/detail');
        if (!$this->get('config')->get('allowUploads')) {
            $data[] = ['error' => $namespace->get('TicketUploadsAreNotAllowed', 'Uploads are not allowed')];

            echo json_encode($data);

            return;
        }

        $ticketId = (int) $this->Request()->getParam('ticketId');
        $ticketAnswerId = (int) $this->Request()->getParam('answerId');

        try {
            $ticketForm = $this->getManager()->getRepository(Support::class)
                ->findOneBy(['id' => $ticketId]);
            $formId = null;
            if ($ticketForm) {
                $formId = $ticketForm->getFormId();
            }

            if ($ticketAnswerId !== -1) {
                /** @var History $answer */
                $answer = $this->getManager()->getRepository(History::class)
                    ->findOneBy(['id' => $ticketAnswerId]);

                if (!$answer) {
                    $uploadAnswer = $namespace->get('TicketUploadAnswerFail', 'Unable to upload file to this answer');
                    throw new Exception($uploadAnswer);
                }

                $ticketId = $answer->getTicket()->getId();
                $ticketAnswerId = $answer->getId();
            }

            $this->phpMaxSizeUploadCheck();
            $this->uploadFile('files', $ticketId, $formId, $ticketAnswerId);
            $message = $namespace->get('TicketUploadSuccess', 'File uploaded successfully');

            $data[] = ['success' => true, 'message' => $message];
        } catch (Exception $ex) {
            $data[] = ['error' => $ex->getMessage()];
        }

        echo json_encode($data);
    }

    /**
     * Create multi upload field for form if upload field exist
     *
     * @param array $element
     * @param array $post
     *
     * @return string
     */
    protected function _createInputElement(array $element, $post = null)
    {
        $output = parent::_createInputElement($element, $post);
        if (!$this->get('config')->get('allowUploads')) {
            return $output;
        }

        $req = '';
        if ($element['required'] == 1) {
            $req = 'required';
        }

        switch ($element['typ']) {
            case 'upload':
                $output .= "<input type=\"file\" class=\"{$element['class']} $req file\" id=\"{$element['name']}\"
                    name=\"{$element['name']}[]\" maxlength=\"100000\" accept=\"{$element['value']}\"
                    multiple=\"multiple\"/>\r\n";
                break;
        }

        return $output;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return ModelManager
     */
    protected function getManager()
    {
        return $this->manager;
    }

    /**
     * @param array $inputs
     * @param array $elements
     *
     * @return array
     */
    protected function _validateInput(array $inputs, array $elements)
    {
        $errors = parent::_validateInput($inputs, $elements);

        foreach ($elements as $element) {
            if ($element['typ'] === 'upload') {
                $fileBag = new FileBag($_FILES);
                $files = $fileBag->get($element['name']);
                if (is_array($files)) {
                    /** @var UploadedFile $file */
                    foreach ($files as $file) {
                        if (!$file && $element['required'] == 1) {
                            $errors['v'][] = $element['id'];
                            $errors['e'][$element['id']] = true;
                        } else {
                            unset($errors['e'][$element['id']]);
                        }

                        if ($file && $file->getClientOriginalName()) {
                            $sFid = $this->Request()->getParam('sFid');
                            $id = $this->Request()->getParam('id');

                            $formId = (int) $sFid ? $sFid : $id;
                            $fileInfo = pathinfo($file->getClientOriginalName());
                            $fileCheck = $this->fileUploadCheck($formId, $fileInfo['extension'], $file->getSize());

                            if ($fileCheck) {
                                $errors['v'][] = $element['id'];
                                $this->_elements[$element['id']]['error_msg'] = $fileCheck;
                                $errors['e'][$element['id']] = true;
                            }
                        }
                    }
                }

                // remove empty error array so we can submit form
                if (count($errors['e']) < 1) {
                    unset($errors['e']);
                }
            }
        }

        return $errors;
    }

    /**
     * Sets the default value to the form
     *
     * @param $formId
     * @param $userData
     */
    private function presetForm($formId, $userData)
    {
        $eMailSupportId = $this->getSupportFieldIdByTicketTask($formId, 'email');
        $nameSupportId = $this->getSupportFieldIdByTicketTask($formId, 'name');

        if (!empty($userData)) {
            //to preset the form
            $this->_postData = [
                $eMailSupportId => $userData['email'],
                $nameSupportId => $userData['customerName'],
            ];
        }
    }

    /**
     * Get all attachments from current ticket answers
     *
     * @param int $ticketId
     *
     * @return array
     */
    private function ticketAttachments($ticketId)
    {
        $userAttachments = [];
        $ticketAttachments = $this->getManager()->getRepository(File::class)
            ->findBy(['location' => 'frontend', 'ticketId' => $ticketId]);

        if ($ticketAttachments) {
            /** @var File $ticketAttachment */
            foreach ($ticketAttachments as $ticketAttachment) {
                $userAttachments[] = [
                    'filename' => basename($ticketAttachment->getName()),
                    'hash' => $ticketAttachment->getHash(),
                    'date' => $ticketAttachment->getUploadDate(),
                ];
            }
        }

        return $userAttachments;
    }

    /**
     * Helper Method to returns the id of the support field
     *
     * @param $formId
     * @param $taskName
     *
     * @return string
     */
    private function getSupportFieldIdByTicketTask($formId, $taskName)
    {
        $sql = <<<SQL
SELECT id
FROM  s_cms_support_fields
WHERE supportID = :supportId
AND ticket_task = :task
SQL;

        return $this->get('dbal_connection')->executeQuery($sql, ['supportId' => $formId, 'task' => $taskName])->fetchColumn();
    }

    /**
     * Get file extension blacklist for current form
     *
     * @param int $formId
     *
     * @return array
     */
    private function getExtBlacklist($formId = null)
    {
        $blacklistExt = [];

        if ($formId) {
            $uploadField = $this->getManager()->getRepository(Field::class)
                ->findOneBy(['typ' => 'upload', 'formId' => $formId]);

            if ($uploadField) {
                $blacklistExt = explode(';', $uploadField->getValue());
            }
        }

        $this->blackList = array_merge($this->blackList, $blacklistExt);

        return $this->blackList;
    }

    /**
     * Helper Method to get the right model
     *
     * @param $id
     * @param string $model
     *
     * @return null|object
     */
    private function findModelById($id, $model)
    {
        if (!empty($id)) {
            $repository = $this->getManager()->getRepository($model);
            $model = $repository->find($id);

            if (!empty($model) && is_object($model)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Method checks if the Ticket-ID valid
     *
     * @param int $ticketFormId
     *
     * @return bool
     */
    private function isTicketFormIdValid($ticketFormId)
    {
        $sql = <<<SQL
SELECT COUNT(id)
FROM s_cms_support
WHERE id = :formId
SQL;
        $result = (int) $this->container->get('dbal_connection')->executeQuery($sql, ['formId' => $ticketFormId])->fetchColumn();

        if ($result > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method gets a default Form-ID
     *
     * @return int
     */
    private function getValidFormId()
    {
        $sql = <<<SQL
SELECT id
FROM s_cms_support LIMIT 1
SQL;

        return $this->container->get('dbal_connection')->executeQuery($sql)->fetchColumn();
    }

    /**
     * @param int $ticketId
     *
     * @return array
     */
    private function getTicketDetailData($ticketId)
    {
        $detailQuery = $this->getManager()->getRepository(Support::class)->getTicketDetailQueryById($ticketId);

        return $detailQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Gets the ticket id by the ticket's unique id
     *
     * @param $uniqueId
     *
     * @return string
     */
    private function getTicketIdByUniqueId($uniqueId)
    {
        $sql = <<<SQL
SELECT `id`
FROM `s_ticket_support`
WHERE `uniqueID`= :uniqueId
SQL;

        return $this->get('dbal_connection')->executeQuery($sql, ['uniqueId' => $uniqueId])->fetchColumn();
    }

    /**
     *  Add attachment to media manager, create thumbnail for current ticket support or ticket support answer
     *
     * @param $file UploadedFile
     * @param $ticketId
     * @param $formId
     * @param null $answerId
     *
     * @throws Exception
     */
    private function fileHandler($file, $ticketId, $formId, $answerId = null)
    {
        $fileInfo = pathinfo($file->getClientOriginalName());
        $extension = $fileInfo['extension'];

        if (!$answerId) {
            $answerId = 0;
        }

        $fileCheck = $this->fileUploadCheck($formId, $extension, $file->getSize());
        if ($fileCheck) {
            throw new Exception($fileCheck);
        }

        $albumRepo = $this->getManager()->getRepository(Album::class);
        $album = $albumRepo->findOneBy(['name' => 'TicketAttachment']);

        if ($album) {
            $media = new Media();
            $media->setAlbumId($album->getId());
            $media->setAlbum($album);

            $media->setName(pathinfo($fileInfo['filename'], PATHINFO_FILENAME));
            $media->setDescription('');
            $media->setCreated(new \DateTime());
            $media->setUserId(0);
            $media->setFile($file);

            $this->getManager()->persist($media);
            $this->getManager()->flush();

            if ($media->getType() === Media::TYPE_IMAGE) {
                $manager = $this->get('thumbnail_manager');
                $manager->createMediaThumbnail($media, [], true);
            }

            $attachment = new File();

            $attachment->setName($media->getPath());
            $attachment->setAnswerId($answerId);
            $attachment->setTicketId($ticketId);
            $attachment->setLocation('frontend');
            $attachment->setHash(uniqid(mt_rand(), true));
            $attachment->setUploadDate();

            $this->getManager()->persist($attachment);
            $this->getManager()->flush();
        }
    }

    /**
     * Handle single or multiple uploaded attachments
     *
     * @param string $fileFieldName
     * @param int    $ticketId
     * @param int    $formId
     * @param int    $answerId
     */
    private function uploadFile($fileFieldName, $ticketId, $formId, $answerId)
    {
        $fileBag = new FileBag($_FILES);
        $files = $fileBag->get($fileFieldName);
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file) {
                    $this->fileHandler($file, $ticketId, $formId, $answerId);
                }
            }
        } else {
            if ($files) {
                $this->fileHandler($files, $ticketId, $formId, $answerId);
            }
        }
    }

    /**
     * Check uploaded attachment extension and file size
     *
     * @param int    $formId
     * @param string $fileExt
     * @param int    $fileSize
     *
     * @return bool
     */
    private function fileUploadCheck($formId, $fileExt, $fileSize)
    {
        $uploadMaxSizeKB = $this->get('config')->get('attachmentFileSize');
        $namespace = $this->get('snippets')->getNamespace('frontend/ticket/detail');

        $extBlacklist = $this->getExtBlacklist($formId);
        if (in_array(strtolower($fileExt), $extBlacklist)) {
            return sprintf($namespace->get('TicketUploadExtension', 'File extension %s not allowed'), $fileExt);
        }

        if ($fileSize > ($uploadMaxSizeKB * 1024) || $fileSize == 0) {
            return sprintf(
                $namespace->get('TicketUploadSize', 'Cannot upload file bigger than %sKB'),
                $uploadMaxSizeKB
            );
        }

        return false;
    }

    /**
     * Show error on ticket create when uploaded attachment is larger from php upload mix size
     */
    private function phpMaxSizeUploadError()
    {
        $sSupport = $this->View()->getAssign('sSupport');

        try {
            $this->phpMaxSizeUploadCheck();
        } catch (Exception $ex) {
            foreach ($sSupport['sElements'] as $field) {
                if ($field['typ'] === 'upload') {
                    $fieldId = $field['id'];

                    $sSupport['sErrors']['v'][$fieldId] = $fieldId;
                    $sSupport['sElements'][$fieldId]['error_msg'] = $ex->getMessage();
                }
            }

            $this->View()->assign('sSupport', array_merge($this->View()->getAssign('sSupport'), $sSupport));
        }
    }

    /**
     * Check if there are upload file which exceeded php max upload size
     *
     * @throws \Exception
     */
    private function phpMaxSizeUploadCheck()
    {
        $uploadMaxSizeKB = $this->get('config')->get('attachmentFileSize');
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('frontend/ticket/detail');

        $post = $this->Request()->getPost();

        if ($this->Request()->getServer('REQUEST_METHOD') === 'POST'
            && empty($post) && empty($_FILES) && $this->Request()->getServer('CONTENT_LENGTH') > 0
        ) {
            $message = sprintf(
                $namespace->get('TicketUploadSize', 'Cannot upload file bigger than %sKB'),
                $uploadMaxSizeKB
            );
            throw new Exception($message);
        }
    }

    /**
     * Returns the translation for the ticket-status if any is available.
     *
     * @param array $ticketData
     *
     * @return array
     */
    private function getStatusTranslation(array $ticketData)
    {
        $translationCmp = new Translator();

        $localeId = (int) $this->get('shop')->getLocale()->getId();
        if ($localeId === 1) {
            return $ticketData;
        }

        $fallbackLocaleId = 2;

        $statusTranslation = $translationCmp->read($localeId, 'ticketStatus', $ticketData['statusId']);

        if (!$statusTranslation || !isset($statusTranslation['description'])) {
            // Try with default-fallback, english in this case
            $statusTranslation = $translationCmp->read($fallbackLocaleId, 'ticketStatus', $ticketData['statusId']);

            if (!$statusTranslation || !isset($statusTranslation['description'])) {
                return $ticketData;
            }
        }

        $ticketData['status'] = $statusTranslation['description'];

        return $ticketData;
    }
}
