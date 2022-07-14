<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\EventListener;

use Netlogix\Nxcachetags\EventListener\EntityUpdatedInPersistence;
use Netlogix\Nxcachetags\Service\CacheTagService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class EntityUpdatedInPersistenceTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @return void
     */
    public function itCallsEventAfterAddingObjectToPersistence()
    {
        $repo = GeneralUtility::makeInstance(CategoryRepository::class);

        $cat = new Category();
        $cat->setTitle(uniqid('title_'));
        $repo->add($cat);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();

        $uid = $cat->getUid();

        $subject = GeneralUtility::makeInstance(EntityUpdatedInPersistence::class);
        $mockCacheTagService = $this->getMockBuilder(CacheTagService::class)
            ->onlyMethods(['flushCachesByTag'])
            ->getMock();
        $mockCacheTagService->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));
        $mockCacheTagService->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $mockCacheTagService->initializeObject();
        $mockCacheTagService->expects(self::once())->method('flushCachesByTag')->with('sys_category_' . $uid);

        $subject->injectCacheTagService($mockCacheTagService);

        GeneralUtility::addInstance(EntityUpdatedInPersistence::class, $subject);


        $cat->setTitle(uniqid('title_'));
        $repo->update($cat);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
    }
}
