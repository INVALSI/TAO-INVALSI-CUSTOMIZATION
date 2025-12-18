<?php

namespace oat\taoInvalsi\model\export\xls;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\SpoutException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\WriterFactory;
use oat\oatbox\filesystem\File;
use tao_helpers_File;

class Converter
{
    /**
     * @param File $csv
     *
     * @return string
     * @throws IOException
     * @throws SpoutException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     * @throws WriterNotOpenedException
     */
    public function toXLS(File $csv): string
    {
        $dir = tao_helpers_File::createTempDir();

        $csvFileName = $this->makeTemporaryCopy($dir, $csv);
        $xlsFileName = $dir . str_replace('.csv', '', $csv->getBaseName()) . '.xlsx';

        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($csvFileName);

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($xlsFileName);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $writer->addRow($row);
            }
        }

        $reader->close();
        $writer->close();

        return file_get_contents($xlsFileName);
    }

    /**
     * @param string $location
     * @param File   $file
     *
     * @return string
     */
    private function makeTemporaryCopy(string $location, File $file): string
    {
        $fileName = $location . $file->getBasename();
        file_put_contents($fileName, $file->read());

        return $fileName;
    }
}
