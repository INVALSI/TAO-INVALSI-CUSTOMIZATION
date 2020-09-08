<?php

declare(strict_types=1);

namespace oat\taoInvalsi\model\export;

use oat\oatbox\service\ConfigurableService;
use OutOfRangeException;

class DateRangeLookupStrategy extends ConfigurableService
{
    public const SERVICE_ID = 'taoInvalsi/strategy';

    public const OPTION_CAMPAIGNS = 'campaigns';

    public const STRATEGY_SAME_DAY = 'sameDayDateRange';
    public const STRATEGY_BEFORE_END_OF_DAY = 'beforeEndOfDayDateRange';

    /**
     * @param string $campaign
     *
     * @return string
     */
    public function findStrategy(string $campaign): string
    {
        $campaigns = $this->getOption(self::OPTION_CAMPAIGNS);

        if (!in_array($campaign, $campaigns, true)) {
            throw new OutOfRangeException(sprintf('Cannot find campaign config for code %s', $campaign));
        }

        $strategy = $campaigns[$campaign];

        if (!in_array($strategy, [self::STRATEGY_SAME_DAY, self::STRATEGY_BEFORE_END_OF_DAY], true)) {
            throw new OutOfRangeException(sprintf(
                'Defined strategy %s for requested campaign %s is not supported',
                $strategy, $campaign
            ));
        }

        return $strategy;
    }
}
