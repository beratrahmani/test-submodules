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

namespace SwagTicketSystem\Components;

use Doctrine\ORM\AbstractQuery;
use Enlight_Components_Db_Adapter_Pdo_Mysql as DatabaseConnection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Shop;
use Shopware\Models\User\User;
use SwagTicketSystem\Models\Ticket\History;
use SwagTicketSystem\Models\Ticket\Mail;
use SwagTicketSystem\Models\Ticket\Repository;
use SwagTicketSystem\Models\Ticket\Status;
use SwagTicketSystem\Models\Ticket\Support;

/**
 * Shopware Ticket System Component
 */
class TicketSystem implements TicketSystemInterface
{
    /**
     * @var Repository
     */
    protected $ticketRepository;

    /**
     * @var Repository
     */
    protected $ticketSubmissionRepository;

    /**
     * @var Repository
     */
    protected $statusRepository;

    /**
     * @var Repository
     */
    protected $historyRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var CachedConfigReader
     */
    private $pluginConfigReader;

    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var \Shopware_Components_Config
     */
    private $configCmp;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @param ModelManager                $modelManager
     * @param CachedConfigReader          $pluginConfigReader
     * @param DatabaseConnection          $databaseConnection
     * @param \Shopware_Components_Config $configCmp
     * @param \Enlight_Template_Manager   $templateManager
     * @param DependencyProvider          $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        CachedConfigReader $pluginConfigReader,
        DatabaseConnection $databaseConnection,
        \Shopware_Components_Config $configCmp,
        \Enlight_Template_Manager $templateManager,
        DependencyProvider $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->pluginConfigReader = $pluginConfigReader;
        $this->databaseConnection = $databaseConnection;
        $this->configCmp = $configCmp;
        $this->templateManager = $templateManager;
        $this->dependencyProvider = $dependencyProvider;

        $this->ticketRepository = $this->modelManager->getRepository(Support::class);
        $this->statusRepository = $this->modelManager->getRepository(Status::class);
        $this->historyRepository = $this->modelManager->getRepository(History::class);
    }

    /**
     * {@inheritdoc}
     */
    public function sendNotificationEmails($ticketId)
    {
        $config = $this->pluginConfigReader->getByPluginName('SwagTicketSystem', $this->dependencyProvider->getShop());

        if ($config['sendShopOperatorNotification']) {
            $sql = <<<SQL
SELECT id
FROM s_ticket_support_history
WHERE ticketID = :ticketId
SQL;
            $stmt = $this->databaseConnection->executeQuery($sql, ['ticketId' => $ticketId]);

            $historyId = $stmt->fetchColumn();

            if (!$historyId) {
                $this->sendSubmissionMail(
                    $ticketId,
                    ['email' => $this->configCmp->get('mail')],
                    self::NOTIFY_NEW_TICKET_SUBMISSION,
                    $this->dependencyProvider->getShop()->getId()
                );
            } else {
                $this->sendSubmissionMail(
                    $ticketId,
                    ['email' => $this->configCmp->get('mail')],
                    self::NOTIFY_TICKET_ANSWER_SUBMISSION,
                    $this->dependencyProvider->getShop()->getId()
                );
            }
        }

        if ($config['sendCustomerNotification']) {
            /** @var $ticketModel Support */
            $ticketModel = $this->ticketRepository->find($ticketId);

            if ($ticketModel !== null) {
                $this->sendSubmissionMail(
                    $ticketId,
                    ['email' => $ticketModel->getEmail()],
                    self::NOTIFY_CUSTOMER_SUBMISSION,
                    $this->dependencyProvider->getShop()->getId()
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendSubmissionMail(
        $ticketId,
        array $mailData,
        $submissionName,
        $shopId = null,
        $ticketUniqueLink = null,
        array $attachments = []
    ) {
        if (!empty($shopId)) {
            $dataQuery = $this->ticketRepository->getSystemSubmissionQuery($submissionName, $shopId);
            /** @var Mail $submissionModel */
            $submissionModel = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
            if ($submissionModel === null) {
                //fallback get the default data
                $dataQuery = $this->ticketRepository->getSystemSubmissionQuery($submissionName);
                $submissionModel = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
            }
        } else {
            $dataQuery = $this->ticketRepository->getSystemSubmissionQuery($submissionName);
            /** @var Mail $submissionModel */
            $submissionModel = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        }

        $isHTML = $submissionModel->getIsHTML();
        $isHTML = empty($isHTML) ? false : $submissionModel->getIsHTML();

        return $this->sendTicketMail(
            $isHTML,
            $mailData['email'],
            $mailData['cc'],
            $submissionModel->getFromMail(),
            $submissionModel->getFromName(),
            $this->renderMailData($ticketId, $submissionModel->getSubject(), $ticketUniqueLink),
            $this->renderMailData($ticketId, $submissionModel->getContent(), $ticketUniqueLink),
            $this->renderMailData($ticketId, $submissionModel->getContentHTML(), $ticketUniqueLink),
            $attachments
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setTicketData($ticketId, $statusId, $employeeId)
    {
        if (!$ticketId) {
            return;
        }

        /** @var $ticketModel Support */
        $ticketModel = $this->ticketRepository->find($ticketId);
        /** @var $statusModel Status */
        $statusModel = $this->statusRepository->find($statusId);

        if ($ticketModel && $statusModel) {
            $ticketModel->setEmployeeId($employeeId);
            $ticketModel->setStatus($statusModel);
        }

        $this->modelManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setTicketStatus($ticketId, $statusId)
    {
        if (!$ticketId) {
            return;
        }

        /** @var $ticketModel Support */
        $ticketModel = $this->ticketRepository->find($ticketId);
        /** @var $statusModel Status */
        $statusModel = $this->statusRepository->find($statusId);

        if ($statusModel instanceof Status && $ticketModel instanceof Support) {
            $ticketModel->setStatus($statusModel);
        }
        $this->modelManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function addMessageToTicketHistory(
        $ticketId,
        $subject,
        $message,
        $answerOnlyOnEmail,
        $receiverAddress,
        $direction,
        $swUser = null,
        $statusId = null
    ) {
        /** @var $ticketModel Support */
        $ticketModel = $this->ticketRepository->find($ticketId);

        $history = new History();
        $history->setMessage($message);
        $history->setSubject($subject);
        $history->setDirection($direction);
        $history->setReceiver($receiverAddress);
        if (!empty($swUser)) {
            $history->setSwUser($swUser);
        }
        $supportType = $answerOnlyOnEmail ? self::SUPPORT_TYPE_DIRECT : self::SUPPORT_TYPE_MANAGE;
        $history->setSupportType($supportType);

        $now = new \DateTime('now');
        $history->setReceipt($now);
        $history->setTicket($ticketModel);
        $history->setStatusId($statusId);

        $ticketModel->setLastContact($now);

        $this->modelManager->persist($history);
        $this->modelManager->flush();

        return $history->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function sendTicketMail(
        $isHTML,
        $eMailAddress,
        $cc = null,
        $senderAddress,
        $senderName,
        $subject,
        $plainMessage,
        $htmlMessage = null,
        array $attachments
    ) {
        if (is_string($isHTML)) {
            $isHTML = $isHTML === 'false' ? false : true;
        }

        $mail = new \Enlight_Components_Mail('UTF-8');
        $mail->addTo($eMailAddress);
        if (!empty($cc)) {
            $mail->addCc($cc);
        }
        $mail->setFrom($senderAddress, $senderName);
        $mail->setSubject($subject);
        if ($isHTML) {
            if (!empty($htmlMessage)) {
                $mail->setBodyHtml($htmlMessage);
            } else {
                $mail->setBodyHtml($plainMessage);
            }
        } else {
            //replace br to nl
            $plainMessage = preg_replace('#<br\s*/?>#i', "\n", $plainMessage);
            $mail->setBodyText(strip_tags(html_entity_decode($plainMessage, null, 'UTF-8')));
        }

        $mail->IsHTML($isHTML);

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $fileHandle = fopen($attachment, 'r');
                $fileName = basename($attachment);
                $mail->createAttachment(
                    $fileHandle,
                    \Zend_Mime::TYPE_OCTETSTREAM,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $fileName
                );
            }
        }

        try {
            $mail->send();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function renderMailData($ticketId, $mailData, $ticketLink = null)
    {
        if (!empty($ticketLink)) {
            $mailData = str_replace('{sTicketDirectUrl}', $ticketLink, $mailData);
        }

        $mailData = str_replace('{sTicketID}', '#' . $ticketId, $mailData);
        $mailData = str_replace(
            '{emailheader}',
            '{include file="string: {config name=emailheaderhtml}"}',
            $mailData
        );
        $mailData = str_replace(
            '{emailfooter}',
            '{include file="string: {config name=emailfooterhtml}"}',
            $mailData
        );

        $mailData = $this->renderSmarty($ticketId, $mailData);

        return $mailData;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserRegistered($ticketId)
    {
        /** @var $ticketModel Support */
        $ticketModel = $this->ticketRepository->find($ticketId);
        if ($ticketModel !== null && is_object($ticketModel)) {
            /* @var Customer $customerModel */
            $customerModel = $ticketModel->getCustomer();
            if ($customerModel !== null && is_object($customerModel)) {
                $customerId = $customerModel->getId();

                return !empty($customerId);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getPagerStructure($destinationPage, $numberOfPages, array $additionalParams = [])
    {
        $destinationPage = !empty($destinationPage) ? $destinationPage : 1;
        $pagesStructure = [];
        $baseFile = $this->configCmp->get('sBASEFILE');

        /** @var \sCore $coreModule */
        $coreModule = $this->dependencyProvider->getModule('core');

        if ($numberOfPages > 1) {
            for ($i = 1; $i <= $numberOfPages; ++$i) {
                $pagesStructure['numbers'][$i]['markup'] = ($i == $destinationPage);
                $pagesStructure['numbers'][$i]['value'] = $i;
                $pagesStructure['numbers'][$i]['link'] = $baseFile . $coreModule->sBuildLink(
                        $additionalParams + ['sPage' => $i]
                    );
            }

            // Previous page
            if ($destinationPage != 1) {
                $pagesStructure['previous'] = $baseFile . $coreModule->sBuildLink(
                        $additionalParams + ['sPage' => $destinationPage - 1]
                    );
            } else {
                $pagesStructure['previous'] = null;
            }
            // Next page
            if ($destinationPage != $numberOfPages) {
                $pagesStructure['next'] = $baseFile . $coreModule->sBuildLink(
                        $additionalParams + ['sPage' => $destinationPage + 1]
                    );
            } else {
                $pagesStructure['next'] = null;
            }
        }

        return $pagesStructure;
    }

    /**
     * renders the mail message through smarty and replaces
     * the smarty variables
     *
     * @param $ticketId
     * @param $message
     *
     * @return string
     */
    private function renderSmarty($ticketId, $message)
    {
        /* @var Support $ticketModel */
        $ticketModel = $this->ticketRepository->find($ticketId);

        /* @var History $historyModel */
        $historyModels = $this->historyRepository->findBy(['ticketId' => $ticketId]);

        /* @var Customer $customerModel */
        $customerModel = $ticketModel->getCustomer();

        /* @var User $userModel */
        $userModel = $this->modelManager->getRepository(User::class)
            ->find($ticketModel->getEmployeeId());

        $employee = '';
        if ($userModel) {
            $employee = $userModel->getName();
        }
        $originalDate = $ticketModel->getReceipt()->format('Y-m-d H:i');
        $originalSubject = $ticketModel->getSubject();
        $originalMessage = $ticketModel->getMessage();

        $lastAnswerDate = $originalDate;
        $lastAnswerSubject = $originalSubject;
        $lastAnswerMessage = $originalMessage;
        $historyCount = 0;

        if ($historyModels) {
            foreach ($historyModels as $historyModel) {
                ++$historyCount;
                if ($historyModel->getReceipt() > $lastAnswerDate) {
                    $lastAnswerDate = $historyModel->getReceipt()->format('Y-m-d H:i');
                    $lastAnswerSubject = $historyModel->getSubject();
                    $lastAnswerMessage = $historyModel->getMessage();
                }
            }
        }

        $salutation = '';
        $firstName = '';
        $lastName = '';
        $mail = '';
        $street = '';
        $zip = '';
        $city = '';
        $customerNumber = '';

        if (is_object($customerModel)) {
            $customerId = $customerModel->getId();
        }
        if (!empty($customerId)) {
            /* @var Address $customerBillingModel */
            $customerBillingModel = $customerModel->getDefaultBillingAddress();

            $salutation = $customerBillingModel->getSalutation();
            $firstName = $customerBillingModel->getFirstName();
            $lastName = $customerBillingModel->getLastName();
            $mail = $customerModel->getEmail();
            $street = $customerBillingModel->getStreet();
            $zip = $customerBillingModel->getZipCode();
            $city = $customerBillingModel->getCity();
            $customerNumber = $customerModel->getNumber();
        }

        $additionalDataTemp = unserialize($ticketModel->getAdditional());
        $additionalData = [];
        foreach ($additionalDataTemp as $data) {
            $additionalData[$data['name']] = $data['value'];
            $message = str_replace('{sVars.' . $data['name'] . '}', $data['value'], $message);
        }

        $message = str_replace('{sVars.email}', $ticketModel->getEmail(), $message);
        $message = str_replace('{sVars.message}', $originalMessage, $message);
        if ($originalSubject) {
            $message = str_replace('{sVars.subject}', $originalSubject, $message);
        }

        $shopUrl = '';

        if ($ticketModel->getShop()) {
            /** @var Shop $shop */
            $shop = $ticketModel->getShop();
            $shopUrl = ($shop->getSecure() ? 'https://' : 'http://') . $shop->getHost() . $shop->getBasePath();
        }

        $ticketType = $ticketModel->getType();
        $ticketStatus = $ticketModel->getStatus();
        $ticketReceipt = $ticketModel->getReceipt();

        $smartyVariables = [
            'sShop' => $this->configCmp->get('shopName'),
            'sShopURL' => $shopUrl,
            'sTicket' => [
                'id' => $ticketId,
                'type' => [
                    'id' => $ticketType->getId(),
                    'gridColor' => $ticketType->getGridColor(),
                    'name' => $ticketType->getName(),
                ],
                'status' => [
                    'id' => $ticketStatus->getId(),
                    'closed' => $ticketStatus->getClosed(),
                    'color' => $ticketStatus->getColor(),
                    'description' => $ticketStatus->getDescription(),
                    'responsible' => $ticketStatus->getResponsible(),
                ],
                'reception' => [
                    'timestamp' => $ticketReceipt,
                    'date' => $ticketReceipt->format('d.m.Y'),
                    'time' => $ticketReceipt->format('H:i:s'),
                ],
                'employee' => $employee,
                'originalDate' => $originalDate,
                'originalSubject' => $originalSubject,
                'originalMessage' => $originalMessage,
                'lastAnswerDate' => $lastAnswerDate,
                'lastAnswerSubject' => $lastAnswerSubject,
                'lastAnswerMessage' => $lastAnswerMessage,
                'historyCount' => $historyCount,
            ],
            'sCustomer' => [
                'salutation' => $salutation,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mail' => $mail,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'customerNumber' => $customerNumber,
            ],
            'sAdditionalData' => $additionalData,
        ];

        try {
            return $this->templateManager->fetch('string:' . $message, $smartyVariables);
        } catch (\Exception $e) {
            return $message;
        }
    }
}
