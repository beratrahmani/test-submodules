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

use Shopware\Components\Model\ModelManager;
use SwagTicketSystem\Models\Ticket\File;

/**
 * @see Shopware_Controllers_Backend_Ticket::answerTicketAction
 */
class CreateAnswerAdapter implements CreateAnswerAdapterInterface
{
    /**
     * @var TicketSystem
     */
    private $ticketSystem;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @param TicketSystemInterface $ticketSystem
     * @param ModelManager          $modelManager
     * @param DependencyProvider    $dependencyProvider
     */
    public function __construct(TicketSystemInterface $ticketSystem, ModelManager $modelManager, DependencyProvider $dependencyProvider)
    {
        $this->ticketSystem = $ticketSystem;
        $this->modelManager = $modelManager;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createAnswer(array $mailData, $ticketUniqueLink = null, array $attachments = [])
    {
        $mailData['subject'] = $this->ticketSystem->renderMailData($mailData['id'], $mailData['subject']);
        $mailData['message'] = $this->ticketSystem->renderMailData($mailData['id'], $mailData['message']);
        $swUser = $this->getAuthenticatedUser();

        $sendAnswerPerMail = $mailData['onlyEmailAnswer'] === '1';
        $notifyUser = $mailData['noNotify'] === 'false';

        if ($sendAnswerPerMail) {
            $this->sendAnswerAsMailToCustomer($mailData, $attachments);
        }

        if (!$sendAnswerPerMail && $notifyUser) {
            $this->sendNotificationAsMailToCustomer($mailData, $ticketUniqueLink);
        }

        $this->ticketSystem->setTicketData($mailData['id'], $mailData['status'], $mailData['employeeCombo']);
        $historyId = $this->storeTicketHistory($mailData, $swUser);

        if ($historyId) {
            $this->storeAttachments($attachments, $historyId, $mailData['id']);
        }
    }

    /**
     * persists and flush attachments
     *
     * @param array  $attachments
     * @param int    $historyId
     * @param string $mailId
     */
    private function storeAttachments(array $attachments, $historyId, $mailId)
    {
        if (!$attachments) {
            return;
        }

        foreach ($attachments as $file) {
            $attachment = new File();
            $attachment->setName($file);
            $attachment->setAnswerId($historyId);
            $attachment->setTicketId((int) $mailId);
            $attachment->setLocation('backend');
            $attachment->setHash(uniqid(mt_rand(), true));
            $attachment->setUploadDate();
            $this->modelManager->persist($attachment);
        }

        $this->modelManager->flush();
    }

    /**
     * @param array $mailData
     * @param array $attachments
     *
     * @throws \RuntimeException
     */
    private function sendAnswerAsMailToCustomer(array $mailData, array $attachments)
    {
        $success = $this->ticketSystem->sendTicketMail(
            $mailData['isHTML'],
            $mailData['email'],
            $mailData['cc'],
            $mailData['senderAddress'],
            $mailData['senderName'],
            $mailData['subject'],
            $mailData['message'],
            null,
            $attachments
        );

        if ($success !== true) {
            throw new \RuntimeException('Could not send mail, returned error: "' . $success . '"');
        }
    }

    /**
     * @return string
     */
    private function getAuthenticatedUser()
    {
        $loggedInUser = $this->dependencyProvider->getAuth()->getIdentity();

        $swUser = '';

        if ($loggedInUser) {
            $swUser = $loggedInUser->name;
        }

        return $swUser;
    }

    /**
     * @param array  $mailData
     * @param string $swUser
     *
     * @return int
     */
    private function storeTicketHistory(array $mailData, $swUser)
    {
        //saves the message to the history
        return $this->ticketSystem->addMessageToTicketHistory(
            $mailData['id'],
            $mailData['subject'],
            $mailData['message'],
            $mailData['onlyEmailAnswer'],
            $mailData['email'],
            TicketSystem::ANSWER_DIRECTION_OUT,
            $swUser,
            $mailData['status']
        );
    }

    /**
     * @param array       $mailData
     * @param string|null $ticketUniqueLink
     *
     * @throws \RuntimeException
     */
    private function sendNotificationAsMailToCustomer(array $mailData, $ticketUniqueLink)
    {
        $submissionName = TicketSystem::NOT_REGISTERED_USER_SUBMISSION;

        if ($this->ticketSystem->isUserRegistered($mailData['id'])) {
            $submissionName = TicketSystem::REGISTERED_USER_SUBMISSION;
        }

        $success = $this->ticketSystem->sendSubmissionMail(
            $mailData['id'],
            $mailData,
            $submissionName,
            $mailData['shopId'],
            $ticketUniqueLink
        );

        if ($success !== true) {
            throw new \RuntimeException('Unable to send answer as Submission mail: "' . $success . '"');
        }
    }
}
