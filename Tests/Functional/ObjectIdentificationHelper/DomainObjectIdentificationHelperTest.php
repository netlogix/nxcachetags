<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\ObjectIdentificationHelper;

use Netlogix\Nxcachetags\ObjectIdentificationHelper\DomainObjectIdentificationHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Netlogix\Nxcachetags\Domain\Model\BackendUser;
use Netlogix\Nxcachetags\Domain\Model\BackendUserGroup;
use Netlogix\Nxcachetags\Domain\Model\Category;
use Netlogix\Nxcachetags\Domain\Model\FrontendUser;
use Netlogix\Nxcachetags\Domain\Model\FrontendUserGroup;
use Netlogix\Nxcachetags\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class DomainObjectIdentificationHelperTest extends FunctionalTestCase
{

    protected array $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @dataProvider domainObjectDataProvider
     * @return void
     */
    public function itCanIdentifyDomainObject(string $type, string $table)
    {
        $uid = rand(1, 9999);

        $container = $this->getContainer();
        $subject = GeneralUtility::makeInstance(DomainObjectIdentificationHelper::class);
        $mapper = $container->get(DataMapper::class);
        $data = $mapper->map($type, [['uid' => $uid]]);

        $res = $subject->identifyCacheTagForObject($data[0]);

        self::assertCount(1, $res);
        self::assertEquals(sprintf('%s_%s', $table, $uid), $res[0]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itCanIdentifyDataFromQueryResult()
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');

        $repo = GeneralUtility::makeInstance(BackendUserRepository::class);
        $data = $repo->findAll();

        $subject = GeneralUtility::makeInstance(DomainObjectIdentificationHelper::class);
        $res = $subject->identifyCacheTagForObject($data);

        self::assertCount(1, $res);
        self::assertEquals('be_users', $res[0]);
    }


    /**
     * Returns a collection of mappable entities.
     *
     * @return string[][]
     */
    public static function domainObjectDataProvider(): array
    {
        return [
            Category::class => [
                Category::class,
                'sys_category'
            ],
            BackendUser::class => [
                BackendUser::class,
                'be_users'
            ],
            BackendUserGroup::class => [
                BackendUserGroup::class,
                'be_groups'
            ],
            FrontendUser::class => [
                FrontendUser::class,
                'fe_users'
            ],
            FrontendUserGroup::class => [
                FrontendUserGroup::class,
                'fe_groups'
            ],
        ];
    }
}