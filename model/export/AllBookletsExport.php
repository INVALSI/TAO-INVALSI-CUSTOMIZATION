<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\taoInvalsi\model\export;

use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoResultExports\model\export\AllBookletsExport as BaseBookletsExport;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class AllBookletsExport extends BaseBookletsExport
{

    const DAILY_EXPORTS = 'DAILY_EXPORTS';

    /**
     * Generate array with statistics for all deliveries
     *
     * @return \common_report_Report
     *
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     * @throws \qtism\data\storage\php\PhpStorageException
     */
    public function export()
    {
        $report = \common_report_Report::createSuccess();

        // Initialize temporary file.
        $tmpLocation = tempnam(sys_get_temp_dir(), 'invalsiexport');
        $this->exportFile = fopen($tmpLocation, 'w+');

        $headers = $this->getHeaders();
        $this->writeToCsv($headers, true);

        $rowsReport = $this->getRows();
        $report->add($rowsReport);

        if ($report->containsError()) {
            $report->setType(\common_report_Report::TYPE_ERROR);
            $report->setMessage('Some errors occurred during export :');
        }

        $rows = $rowsReport->getData();

        // Write as stream and close temporary file.
        $exportCsvFile = $this->createExportFile('csv');
        $report->setMessage($rows . ' row(s) exported to ' . $exportCsvFile->getPrefix() . '.');
        rewind($this->exportFile);
        $exportCsvFile->write($this->exportFile);

        $xlsFileName = $this->convertToXls($exportCsvFile);
        $exportXlsFile = $this->createExportFile('xlsx');
        $exportXlsFile->write(file_get_contents($xlsFileName));

        fclose($this->exportFile);
        unlink($tmpLocation);

        return $report;
    }


    /**
     * Create the filename for the export
     *
     * If there is a daily mode then add the date as subfolder
     * @param string $ext
     * @return File
     */
    protected function createExportFile($ext = 'csv')
    {
        $grade = $this->getGrade($this->prefix);
        $subject = $this->getSubject($this->prefix);
        $directory = $grade . DIRECTORY_SEPARATOR .
                     $grade . '_' . $subject . DIRECTORY_SEPARATOR .
                     self::DAILY_EXPORTS . DIRECTORY_SEPARATOR .
                     date('Y_m_d') . DIRECTORY_SEPARATOR .
                     gethostname() . DIRECTORY_SEPARATOR;

        $filename = $this->prefix . '.' . $ext;

        /** @var File $file */
        $file = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory(self::FILESYSTEM_ID)
            ->getFile($directory . $filename);

        if ($this->allowTimestampInFilename === false && $file->exists()) {
            $file->delete();
        }

        return $file;
    }

    /**
     * @param $prefix
     * @return string
     */
    protected function getGrade($prefix)
    {
        preg_match('/.*_(G)RA(\d\d)_(\w\w\w).*/', $prefix, $matches);
        return $matches[1].$matches[2];
    }

    /**
     * @param $prefix
     * @return mixed
     */
    protected function getSubject($prefix)
    {
        preg_match('/.*_(G)RA(\d\d)_(\w\w\w).*/', $prefix, $matches);
        return $matches[3];
    }

    /**
     * @param File $csvFile
     * @return string
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    protected function convertToXls(File $csvFile)
    {
        $dir = \tao_helpers_File::createTempDir();
        $csvFileName = $dir.$csvFile->getBasename();
        file_put_contents($csvFileName, $csvFile->read());
        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($csvFileName);

        $xlsFileName = $dir . str_replace('.csv', '', $csvFile->getBaseName()) . '.xlsx';
        $readCount = 0;
        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($csvFileName);
        $writeCount = 0;
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($xlsFileName);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $readCount++;
                $writer->addRow($row);
                $writeCount++;
            }
        }
        $reader->close();
        $writer->close();
        return $xlsFileName;
    }

}
