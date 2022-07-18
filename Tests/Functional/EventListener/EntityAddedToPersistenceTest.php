<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\EventListener;

use Netlogix\Nxcachetags\EventListener\EntityAddedToPersistence;
use Netlogix\Nxcachetags\Service\CacheTagService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class EntityAddedToPersistenceTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/nxcachetags'];

    /**
     * @test
     * @return void
     */
    public function itCallsEventAfterAddingObjectToPersistence() {
        $mockCacheTagService = $this->getMockBuilder(CacheTagService::class)
            ->onlyMethods(['flushCachesByTag'])
            ->getMock();
        $mockCacheTagService->injectCacheManager(GeneralUtility::makeInstance(CacheManager::class));
        $mockCacheTagService->injectConfigurationManager(GeneralUtility::makeInstance(ConfigurationManager::class));
        $mockCacheTagService->initializeObject();
        $mockCacheTagService->expects(self::atLeastOnce())->method('flushCachesByTag');


        GeneralUtility::setSingletonInstance(CacheTagService::class, $mockCacheTagService);


        $object = new Category();
        $object->setTitle(uniqid('title_'));

        $repo = GeneralUtility::makeInstance(CategoryRepository::class);
        $repo->add($object);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
    }
}
