<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\Persistence\Generic\Storage;

use Netlogix\Nxcachetags\Persistence\Generic\Storage\BackendSlot;
use Netlogix\Nxcachetags\Service\CacheTagService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Domain\Model\Category;

class BackendSlotTest extends UnitTestCase
{

    /**
     * @test
     *
     * @return void
     */
    public function itFlushesTagsForDomainObject() {

        $subject = new BackendSlot();
        $mockCacheTagService = $this->getMockBuilder(CacheTagService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createCacheTags', 'flushCachesByTag'])
            ->getMock();

        $fakeTags = [uniqid(), uniqid()];

        $object = new Category();

        $mockCacheTagService->expects(self::once())->method('createCacheTags')->with($object)->willReturn($fakeTags);
        $mockCacheTagService->expects(self::any())->method('flushCachesByTag')->withConsecutive([$fakeTags[0]], [$fakeTags[1]]);

        $subject->injectCacheTagService($mockCacheTagService);

        $subject->flushCacheForObject($object);

    }
}
