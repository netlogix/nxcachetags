<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Service;

use Netlogix\Nxcachetags\Service\MinimalLifetimeService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MinimalLifetimeServiceTest extends FunctionalTestCase
{
    /**
     * @test
     * @return void
     */
    public function itReturnsLifetimeIfNoRecordIsFound() {

        $expires = time();

        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, null);
        $res = $subject->_call('findMinimalLifetimeForRecord', $expires, 'pages', rand(1,9999));

        self::assertEquals($expires, $res);
    }
    /**
     * @test
     * @dataProvider lifetimeDataProvider
     * @return void
     */
    public function itCanFindMinimalLifetimeForRecordWithStartAndEndTime(array $times, int $expected) {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        $uid = rand(1,9999);

        $conn->insert('pages',['uid' => $uid, 'starttime' => $times['starttime'], 'endtime' => $times['endtime']]);

        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, null);
        $res = $subject->_call('findMinimalLifetimeForRecord', $times['expires'], 'pages', $uid);

        self::assertEquals($expected, $res);
    }

    public static function lifetimeDataProvider(): array {

        $time = time();

        return [
            'record has endtime and starttime' => [
                [
                    'starttime' => $time - 500,
                    'endtime' => $time + 500,
                    'expires' => $time + 1000
                ],
                $time + 500
            ],
            'record has endtime' => [
                [
                    'starttime' => 0,
                    'endtime' => $time + 500,
                    'expires' => $time + 1000
                ],
                $time + 500
            ],
            'record does not have time' => [
                [
                    'starttime' => 0,
                    'endtime' => 0,
                    'expires' => $time + 1000
                ],
                $time + 1000
            ],
        ];
    }

    /**
     * @test
     * @return void
     */
    public function itCanFindLifetimeFromAllContentRecordsInPage() {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');

        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        $uid = rand(1,9999);
        $expires = $GLOBALS['EXEC_TIME'] + 5000;
        $expectedResult = $GLOBALS['EXEC_TIME'] + 1000;

        // this record has timed out
        $conn->insert('tt_content', ['pid' => 1, 'uid' => rand(1,999999), 'starttime' => 0, 'endtime' => ($GLOBALS['EXEC_TIME'] - 100)]);
        // this record is not active yet
        $conn->insert('tt_content', ['pid' => 1, 'uid' => rand(1,999999), 'starttime' => ($GLOBALS['EXEC_TIME'] + 100), 'endtime' => ($GLOBALS['EXEC_TIME'] + 1000)]);
        // this is the one we want
        $conn->insert('tt_content', ['pid' => 1, 'uid' => $uid, 'starttime' => ($GLOBALS['EXEC_TIME'] - 100), 'endtime' => $expectedResult]);

        $subject = $this->getAccessibleMock(MinimalLifetimeService::class, ['getTyposcriptFrontendController']);

        $mockTFSE = $this->getMockBuilder(TypoScriptFrontendController::class)
        ->disableOriginalConstructor()
        ->getMock();
        $mockTFSE->id = 1;

        $subject->expects(self::any())->method('getTyposcriptFrontendController')->willReturn($mockTFSE);

        $res = $subject->_call('findMinimalLifetimeForTable', $expires, 'tt_content', []);

        self::assertEquals($expectedResult, $res);
    }
}
