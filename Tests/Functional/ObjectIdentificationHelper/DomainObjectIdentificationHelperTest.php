<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\ObjectIdentificationHelper;

use Netlogix\Nxcachetags\ObjectIdentificationHelper\DomainObjectIdentificationHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class DomainObjectIdentificationHelperTest extends FunctionalTestCase
{

    protected $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @dataProvider domainObjectDataProvider
     * @return void
     */
    public function itCanIdentifyDomainObject(string $type, string $table)
    {
        $uid = rand(1, 9999);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $subject = $objectManager->get(DomainObjectIdentificationHelper::class);
        $mapper = $objectManager->get(DataMapper::class);
        $data = $mapper->map($type, [['uid' => $uid]]);

        $res = $subject->identifyCacheTagForObject($data[0]);

        self::assertCount(1, $res);
        self::assertEquals(sprintf('%s_%s', $table, $uid), $res[0]);
    }

    /**
     * @test
     *
     * @return void
     * @throws \Nimut\TestingFramework\Exception\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function itCanIdentifyDataFromQueryResult()
    {
        $this->importDataSet('ntf://Database/be_users.xml');

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $repo = $objectManager->get(BackendUserRepository::class);
        $data = $repo->findAll();

        $subject = $objectManager->get(DomainObjectIdentificationHelper::class);
        $res = $subject->identifyCacheTagForObject($data);

        self::assertCount(1, $res);
        self::assertEquals('be_users', $res[0]);
    }


    /**
     * Returns a collection of mappable entities.
     *
     * @return string[][]
     */
    public function domainObjectDataProvider(): array
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
        ];
    }
}