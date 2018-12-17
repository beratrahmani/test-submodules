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

class TicketPdf implements TicketPdfInterface
{
    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @var string
     */
    private $docPath;

    /**
     * @param \Enlight_Template_Manager $templateManager
     * @param string                    $docPath
     */
    public function __construct(\Enlight_Template_Manager $templateManager, $docPath)
    {
        $this->templateManager = $templateManager;
        $this->docPath = $docPath;
    }

    /**
     * {@inheritdoc}
     */
    public function downloadPdf($ticketId, array $detailData, array $ticketHistoryData)
    {
        include_once $this->docPath . '/engine/Library/Mpdf/mpdf.php';

        $this->templateManager->assign('ticket', $detailData);
        $this->templateManager->assign('ticketHistory', $ticketHistoryData);

        $data = $this->templateManager->fetch('documents/pdf.tpl');

        // Delete previous generated pdf
        $filename = 'ticket-' . $ticketId . '.pdf';

        $mpdf = new \mPDF('win-1252', 'A4', '', '', 25, 10, 20, 20);
        $mpdf->WriteHTML($data);
        $mpdf->Output($filename, 'D');
    }
}
