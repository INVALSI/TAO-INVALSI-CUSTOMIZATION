<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
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

    /** @var string export only one day or all results until $this->dayToExport */
    const OPTION_ONE_DAY_EXPORT = 'one_day_export';
    const OPTION_EXPORT_FOLDER = 'export_folder';
    const DEFAULT_EXPORT_FOLDER = 'PRODUCTION';
    const DAILY_EXPORTS = 'DAILY_EXPORTS';
    const FT_ACTIVITY = 'FT';
    const MS_ACTIVITY = 'MS';

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
        $date = \DateTime::createFromFormat('Y-m-d', $this->dayToExport);
        //by customers requirement folder should contain yesterday's results.
        $date->add(new \DateInterval('P1D'));
        $grade = $this->getGrade($this->prefix);
        $subject = $this->getSubject($this->prefix);
        $exportFolder = $this->getExportFolder();
        $directory = $this->getActivity($this->prefix) . '_' . date("Y") . DIRECTORY_SEPARATOR .
                     $exportFolder . DIRECTORY_SEPARATOR .
                     $grade . DIRECTORY_SEPARATOR .
                     $grade . '_' . $subject . DIRECTORY_SEPARATOR .
                     self::DAILY_EXPORTS . DIRECTORY_SEPARATOR .
                     $date->format('Y_m_d') . DIRECTORY_SEPARATOR .
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
     * @param $startime
     * @return bool
     * @throws \Exception
     */
    protected function withinDateRange($startime)
    {
        if ($this->getActivity($this->prefix) === self::MS_ACTIVITY) {
             return $this->getEpochDay($startime) === $this->dayToExport;
        } else if ($this->getActivity($this->prefix) === self::FT_ACTIVITY) {
            $date = \DateTime::createFromFormat('Y-m-d', $this->dayToExport);
            $date->setTime(23,59,59);
            return $startime < $date->getTimestamp();
        }
        return false;
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
     * @return string
     */
    protected function getExportFolder()
    {
        $exportFolder = self::DEFAULT_EXPORT_FOLDER;
        if ($this->hasOption(self::OPTION_EXPORT_FOLDER) && !empty($this->getOption(self::OPTION_EXPORT_FOLDER))) {
            $exportFolder = $this->getOption(self::OPTION_EXPORT_FOLDER);
        }
        return $exportFolder;
    }

    /**
     * @param $prefix
     * @return string
     * @throws \Exception
     */
    protected function getActivity($prefix)
    {
        if (strpos($prefix, '_FT') !== false) {
            return self::FT_ACTIVITY;
        }
        if (strpos($prefix, '_MS') !== false) {
            return self::MS_ACTIVITY;
        }
        throw new \Exception('Cannot get activity');
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
