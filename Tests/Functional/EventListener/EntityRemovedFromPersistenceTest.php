<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\EventListener;

use Netlogix\Nxcachetags\Domain\Repository\CategoryRepository;
use Netlogix\Nxcachetags\Service\CacheTagService;
use Netlogix\Nxcachetags\Domain\Model\Category;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class EntityRemovedFromPersistenceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @return void
     */
    public function itCallsEventAfterAddingObjectToPersistence()
    {
        $mockCacheTagService = $this->getMockBuilder(CacheTagService::class)
            ->onlyMethods(['flushCachesByTag'])
            ->getMock();
        $mockCacheTagService->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));
        $mockCacheTagService->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $mockCacheTagService->initializeObject();
        $mockCacheTagService->expects(self::atLeastOnce())->method('flushCachesByTag');
        GeneralUtility::setSingletonInstance(CacheTagService::class, $mockCacheTagService);

        $repo = GeneralUtility::makeInstance(CategoryRepository::class);

        $cat = new Category();
        $cat->setTitle(uniqid('title_'));
        $repo->add($cat);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();


        $cat->setTitle(uniqid('title_'));
        $repo->remove($cat);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
    }
}
