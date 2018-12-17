<?php declare(strict_types = 1);

namespace Shopware\B2B\Common\File;

use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsReader
{
    /**
     * @param string $file
     * @param CsvContext $context
     * @return array
     */
    public function read(string $file, CsvContext $context): array
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException('the provided file does not exists');
        }

        $oldLibXmlDisableEntityLoaderValue = libxml_disable_entity_loader();

        /** @var \PhpOffice\PhpSpreadsheet\Reader\Excel5 $reader */
        $reader = IOFactory::createReaderForFile($file);
        $spreadsheet = $reader->load($file);

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator(1);

        $data = [];
        $headline = $context->headline;

        foreach ($rowIterator as $row) {
            if ($headline === true) {
                $headline = false;
                continue;
            }

            $columnArray = [];
            $columnIterator = $row->getCellIterator();
            foreach ($columnIterator as $column) {
                $columnArray[] = $column->getValue();
            }

            $data[] = $columnArray;
        }

        libxml_disable_entity_loader($oldLibXmlDisableEntityLoaderValue);

        return $data;
    }
}
