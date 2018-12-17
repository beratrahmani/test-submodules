<?php declare(strict_types=1);

namespace Shopware\B2B\Common\File;

class CsvWriter
{
    /**
     * @param array $data
     * @param string $fileName
     */
    public function write(array $data, string $fileName = 'php://output')
    {
        $file = fopen($fileName, 'w');

        foreach ($data as $line) {
            fputcsv($file, $line);
        }

        fclose($file);
    }
}
