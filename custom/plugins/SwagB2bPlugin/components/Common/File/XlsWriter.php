<?php declare(strict_types=1);

namespace Shopware\B2B\Common\File;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class XlsWriter
{
    /**
     * @param array $data
     * @param string $fileName
     */
    public function write(array $data, string $fileName = 'php://output')
    {
        $spreadsheet = new Spreadsheet();
        $alphabet = range('A', 'Z');

        $rowCounter = 1;
        foreach ($data as $row) {
            $columnCounter = 1;
            foreach ($row as $column) {
                $index = $alphabet[$columnCounter - 1] . $rowCounter;
                $spreadsheet->setActiveSheetIndex(0)->setCellValue($index, $column);
                $columnCounter++;
            }
            $rowCounter++;
        }

        $spreadsheet->getActiveSheet()->setTitle('Export');

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save($fileName);
    }
}
