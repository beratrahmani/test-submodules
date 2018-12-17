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

interface TicketSystemInterface
{
    /**
     * Constant for the direction of an ticket
     */
    const ANSWER_DIRECTION_OUT = 'OUT';

    /**
     * Constant for the direction of an ticket
     */
    const ANSWER_DIRECTION_IN = 'IN';

    /**
     * Constant for the direction of an ticket
     */
    const SUPPORT_TYPE_DIRECT = 'direct';

    /**
     * Constant for the direction of an ticket
     */
    const SUPPORT_TYPE_MANAGE = 'manage';

    /**
     * Constant for the name of the registered user submission
     */
    const REGISTERED_USER_SUBMISSION = 'sSTRAIGHTANSWER';

    /**
     * Constant for the name of the not registered user submission
     */
    const NOT_REGISTERED_USER_SUBMISSION = 'sSTRAIGHTANSWER_UNREG';

    /**
     * Constant for the name of the new ticket submission
     */
    const NOTIFY_NEW_TICKET_SUBMISSION = 'sTICKETNOTIFYMAILNEW';

    /**
     * Constant for the name of the ticket answer submission
     */
    const NOTIFY_TICKET_ANSWER_SUBMISSION = 'sTICKETNOTIFYMAILANS';

    /**
     * Constant for the name of the customer notification submission
     */
    const NOTIFY_CUSTOMER_SUBMISSION = 'sTICKETNOTIFYMAILCOSTUMER';

    /**
     * Sends the configured notification eMails to the customer and the shop operator
     *
     * @param int $ticketId
     *
     * @return bool|string
     */
    public function sendNotificationEmails($ticketId);

    /**
     * This method sends a submission mail depending on the ticketId and the submission name
     * This method loads the submission data and sends a mail with the loaded data
     * Additional mail data can be added
     *
     * @param int         $ticketId
     * @param array       $mailData
     * @param string      $submissionName
     * @param int|null    $shopId
     * @param string|null $ticketUniqueLink
     * @param array       $attachments
     *
     * @return bool|string
     */
    public function sendSubmissionMail(
        $ticketId,
        array $mailData,
        $submissionName,
        $shopId = null,
        $ticketUniqueLink = null,
        array $attachments = []
    );

    /**
     * Sets the data of a ticket
     *
     * @param int   $ticketId
     * @param mixed $statusId
     * @param mixed $employeeId
     */
    public function setTicketData($ticketId, $statusId, $employeeId);

    /**
     * Sets the data of a ticket
     *
     * @param int $ticketId
     * @param int $statusId
     */
    public function setTicketStatus($ticketId, $statusId);

    /**
     * add an item to the ticket history
     *
     * @param int    $ticketId
     * @param string $subject
     * @param string $message
     * @param bool   $answerOnlyOnEmail
     * @param mixed  $receiverAddress
     * @param string $direction
     * @param mixed  $swUser
     * @param mixed  $statusId
     *
     * @return int
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
    );

    /**
     * sends the ticket mail
     *
     * @param bool         $isHTML
     * @param string       $eMailAddress
     * @param string       $cc
     * @param string       $senderAddress
     * @param string       $senderName
     * @param mixed|string $subject
     * @param mixed|string $plainMessage
     * @param mixed|string $htmlMessage
     * @param array        $attachments
     *
     * @return bool|string
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
    );

    /**
     * replaces the variables of the eMail Template
     *
     * @param int         $ticketId
     * @param string      $mailData
     * @param string|null $ticketLink
     *
     * @return mixed
     */
    public function renderMailData($ticketId, $mailData, $ticketLink = null);

    /**
     * returns true if the user is a registered user
     *
     * @param int $ticketId
     *
     * @return bool
     */
    public function isUserRegistered($ticketId);

    /**
     * Calculates and returns the pager structure for the frontend
     *
     * @param int   $destinationPage
     * @param int   $numberOfPages
     * @param array $additionalParams
     *
     * @return array
     */
    public function getPagerStructure($destinationPage, $numberOfPages, array $additionalParams = []);
}
