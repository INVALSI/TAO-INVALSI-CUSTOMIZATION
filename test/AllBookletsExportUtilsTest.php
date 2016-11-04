<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 */

namespace oat\taoInvalsi\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoInvalsi\model\export\AllBookletsExportUtils;

class AllBookletsExportUtilsTest extends TaoPhpUnitTestRunner
{
    /**
     * @dataProvider formatDurationProvider
     */
    public function testFormatDuration($strDuration, $expected)
    {
        $this->assertEquals($expected, AllBookletsExportUtils::formatDuration($strDuration));
    }
    
    public function formatDurationProvider()
    {
        return array(
            array('PT5S', '5.000'),
            array('PT1M', '60.000'),
            array('PT0S', '0.000'),
            array('PT5.345678S', '5.346'),
            array('PT0.001434S', '0.001'),
            array('PT0.000000S', '0.000'),
            array('PT1M73.022223S', '133.022'),
            
            array('foobarbaz', false),
            array(null, false),
            array(new \stdClass(), false)
        );
    }
}
