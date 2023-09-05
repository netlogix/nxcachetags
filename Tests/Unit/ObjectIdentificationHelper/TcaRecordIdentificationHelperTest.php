<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\ObjectIdentificationHelper;

use Netlogix\Nxcachetags\ObjectIdentificationHelper\TcaRecordIdentificationHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TcaRecordIdentificationHelperTest extends UnitTestCase
{

    /**
     * @test
     * @dataProvider tableNameDataProvider
     *
     * @param string $tableName
     * @return void
     */
    public function itCanIdentifyObjectsWithUnderscore(string $tableName)
    {
        $uid = rand(1, 99999);

        $subject = new TcaRecordIdentificationHelper();

        $res = $subject->identifyCacheTagForObject(sprintf('%s_%d', $tableName, $uid));

        self::assertCount(1, $res);
        self::assertEquals(sprintf('%s_%d', $tableName, $uid), $res[0]);
    }

    /**
     * @test
     * @dataProvider tableNameDataProvider
     *
     * @param string $tableName
     * @return void
     */
    public function itCanIdentifyObjectsWithDash(string $tableName)
    {
        $uid = rand(1, 99999);

        $subject = new TcaRecordIdentificationHelper();

        $res = $subject->identifyCacheTagForObject(sprintf('%s-%d', $tableName, $uid));

        self::assertCount(1, $res);
        self::assertEquals(sprintf('%s_%d', $tableName, $uid), $res[0]);
    }

    /**
     * @test
     * @dataProvider tableNameDataProvider
     *
     * @param string $tableName
     * @return void
     */
    public function itCanIdentifyObjectsWithColon(string $tableName)
    {
        $uid = rand(1, 99999);

        $subject = new TcaRecordIdentificationHelper();

        $res = $subject->identifyCacheTagForObject(sprintf('%s:%d', $tableName, $uid));

        self::assertCount(1, $res);
        self::assertEquals(sprintf('%s_%d', $tableName, $uid), $res[0]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itReturnsEmptyTagsForNonStringInput()
    {
        $subject = new TcaRecordIdentificationHelper();

        self::assertEmpty($subject->identifyCacheTagForObject(1));
        self::assertEmpty($subject->identifyCacheTagForObject(new \stdClass()));
        self::assertEmpty($subject->identifyCacheTagForObject([]));
        self::assertEmpty($subject->identifyCacheTagForObject(false));
    }

    /**
     * @test
     *
     * @return void
     */
    public function itReturnsEmptyTagsForUnknownRecordIdentifier()
    {
        $subject = new TcaRecordIdentificationHelper();

        self::assertEmpty($subject->identifyCacheTagForObject(uniqid()));
    }

    public static function tableNameDataProvider(): array
    {
        // no TCA is available in UnitTest
        $tables = ['pages', 'tt_content', 'be_users', 'sys_file', 'sys_file_reference', 'sys_category', uniqid('tx_')];

        $data = [];

        foreach ($tables as $table) {
            $data[$table] = [$table];
        }

        return $data;
    }

}