<?php declare(strict_types=1);

namespace Shopware\B2B\Common\File;

class CsvReader
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

        $data = [];
        $fileResource = fopen($file, 'rb');
        $headline = $context->headline;

        while (($row = $this->getCsvRow($fileResource, $context)) !== false) {
            if ($headline === true) {
                $headline = false;
                continue;
            }

            foreach ($row as &$column) {
                $column = $this->convertEncoding($column);
            }
            unset($column);
            $data[] = $row;
        }

        fclose($fileResource);

        return $data;
    }

    /**
     * @internal
     * @param resource $handle
     * @param CsvContext $context
     * @return mixed
     */
    protected function getCsvRow($handle, CsvContext $context)
    {
        if ($context->csvEnclosure) {
            return fgetcsv($handle, null, $context->csvDelimiter, $context->csvEnclosure);
        }

        return fgetcsv($handle, null, $context->csvDelimiter);
    }

    /**
     * @internal
     * @param string|null $string
     * @return string
     */
    protected function convertEncoding($string): string
    {
        if (!$string) {
            return '';
        }
        if (!mb_check_encoding($string, 'UTF-8')
            || $string !== mb_convert_encoding(mb_convert_encoding($string, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32')
        ) {
            $string = mb_convert_encoding($string, 'UTF-8', 'pass');
        }

        return $string;
    }
}
