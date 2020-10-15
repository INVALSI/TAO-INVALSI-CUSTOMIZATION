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

declare(strict_types=1);

namespace oat\taoInvalsi\model\export;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use common_Exception;
use common_exception_Error;
use common_exception_NotFound;
use common_report_Report as Report;
use DateInterval;
use DateTime;
use Exception;
use League\Flysystem\FileExistsException;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoInvalsi\model\export\xls\Converter;
use oat\taoResultExports\model\export\AllBookletsExport as BaseBookletsExport;
use OutOfRangeException;
use qtism\data\storage\php\PhpStorageException;

class AllBookletsExport extends BaseBookletsExport
{
    /**
     * @var string export only one day or all results until $this->dayToExport
     * @deprecated It is not used anymore and will be delete in the next release.
     */
    public const OPTION_ONE_DAY_EXPORT = 'one_day_export';

    public const OPTION_EXPORT_FOLDER = 'export_folder';
    public const DEFAULT_EXPORT_FOLDER = 'PRODUCTION';

    public const PERIODICITY_DAILY = 'DAILY';
    public const PERIODICITY_WEEKLY = 'WEEKLY';

    private $periodicity = self::PERIODICITY_DAILY;

    private $weekToExport;

    /**
     * Generate array with statistics for all deliveries
     *
     * @return Report
     *
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     * @throws WriterNotOpenedException
     * @throws FileExistsException
     * @throws PhpStorageException
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws Exception
     */
    public function export(): Report
    {
        $report = Report::createSuccess();

        // initialize temporary file.
        $location = tempnam(sys_get_temp_dir(), 'invalsiexport');
        $this->exportFile = fopen($location, 'wb+');
        rewind($this->exportFile);

        $this->writeToCsv($this->getHeaders(), true);

        $rowsReport = $this->getRows();
        $report->add($rowsReport);

        if ($report->containsError()) {
            $report->setType(Report::TYPE_ERROR);
            $report->setMessage('Some errors occurred during export :');
        }

        $csv = $this->createExportFile('csv');
        $csv->write($this->exportFile);

        $xls = $this->createExportFile('xlsx');
        $xls->write((new Converter())->toXLS($csv));

        $report->setMessage($rowsReport->getData() . ' row(s) exported to ' . $csv->getPrefix() . '.');

        fclose($this->exportFile);
        unlink($location);

        return $report;
    }

    /**
     * Create the filename for the export
     *
     * If there is a daily mode then add the date as sub-folder
     *
     * @param string $ext
     *
     * @return File
     * @throws Exception
     */
    protected function createExportFile($ext = 'csv'): File
    {
        $date = (new DateTime())->format('Y-m-d');

        // by customers requirement folder should contain yesterday's results.
        if ($this->dayToExport) {
            $date = DateTime::createFromFormat('Y-m-d', $this->dayToExport);
            $date->add(new DateInterval('P1D'));
        }

        if ($this->weekToExport) {
            $date = (new DateTime())->setISODate(date('Y'), $this->weekToExport);
        }

        $prefixParserService = $this->getPrefixParserService();

        $campaign = $prefixParserService->getCampaign($this->prefix);
        $grade = $prefixParserService->getGrade($this->prefix);
        $subject = $prefixParserService->getSubject($this->prefix);

        $folder = $this->getOption(self::OPTION_EXPORT_FOLDER, self::DEFAULT_EXPORT_FOLDER);

        $pathComponents = [
            $campaign . '_' . date("Y"),
            $folder,
            $grade,
            $subject,
            $this->periodicity,
            $date->format('Y_m_d'),
            defined('GENERIS_INSTANCE_NAME') ? GENERIS_INSTANCE_NAME : gethostname()
        ];

        $directory = implode(DIRECTORY_SEPARATOR, $pathComponents) . DIRECTORY_SEPARATOR;
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

    public function weeklyExport(): Report
    {
        // set proper export folder
        $this->periodicity = self::PERIODICITY_WEEKLY;

        $amount = 1;

        if ($this->hasOption(self::OPTION_NUMBER_OF_DAILY_EXPORT)) {
            $amount = (int)$this->getOption(self::OPTION_NUMBER_OF_DAILY_EXPORT);
        }

        $date = new DateTime();
        $lookBack = new DateInterval('P7D'); // one week

        $report = Report::createInfo('Exporting results for the last ' . $amount . ' weeks(s):');

        for ($i=0; $i<$amount; $i++) {
            try {
                $past = $date->sub($lookBack);

                $this->weekToExport = $past->format('W');

                $subReport = Report::createInfo('About export of week ' . $this->weekToExport . '...');
                $subReport->add($this->export());

                $report->add($subReport);
            } catch (Exception $e) {
                $report->setType(Report::TYPE_ERROR);
                $report->setMessage('Some errors occurred during export : ' . $e->getMessage());
            }
        }

        return $report;
    }

    /**
     * @param $startTime
     *
     * @return bool
     */
    protected function withinDateRange($startTime): bool
    {
        if ($this->weekToExport) {
            return $this->weekToExport === (new DateTime("@$startTime"))->format('W');
        }

        $campaign = $this->getPrefixParserService()->getCampaign($this->prefix);

        /** @var CampaignDateRangeLookupStrategy $lookupStrategyService */
        $lookupStrategyService = $this->getServiceLocator()->get(CampaignDateRangeLookupStrategy::SERVICE_ID);

        try {
            $strategy = $lookupStrategyService->findStrategy($campaign);
            return $this->{$strategy}($startTime);
        } catch (OutOfRangeException $e) {
            return false;
        }
    }

    /**
     * @param int $startTime
     *
     * @return bool
     */
    private function sameDayDateRange(int $startTime): bool
    {
        return $this->getEpochDay($startTime) === $this->dayToExport;
    }

    /**
     * @param int $startTime
     *
     * @return bool
     */
    private function beforeEndOfDayDateRange(int $startTime): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $this->dayToExport);
        $date->setTime(23,59,59);

        return $startTime < $date->getTimestamp();
    }

    /**
     * @return PrefixParser
     */
    protected function getPrefixParserService(): PrefixParser
    {
        return $this->getServiceLocator()->get(PrefixParser::SERVICE_ID);
    }
}
