<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\EventListener;

use Netlogix\Nxcachetags\EventListener\FlushCacheTagForFile;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\Event\AfterFileContentsSetEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileDeletedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileMovedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileRenamedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FlushCacheTagForFileTest extends UnitTestCase
{

    public function generateFileMock(): File
    {
        $resourceMock = $this->getMockBuilder(ResourceStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new File(['uid' => 42], $resourceMock);
    }

    /**
     * @test
     * @return void
     */
    public function itFlushesCacheForFileAfterFileMoved(): void
    {
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('flushCachesByTag')
            ->with('sys_file_42');

        $subject = new FlushCacheTagForFile($cacheManagerMock);
        $event = new afterFileMovedEvent(
            $this->generateFileMock(),
            $this->getMockBuilder(Folder::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(FolderInterface::class)->disableOriginalConstructor()->getMock()
        );
        $subject->afterFileMoved($event);
    }
    /**
     * @test
     * @return void
     */
    public function itFlushesCacheForFileAfterFileDeleted(): void
    {
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('flushCachesByTag')
            ->with('sys_file_42');

        $subject = new FlushCacheTagForFile($cacheManagerMock);
        $event = new AfterFileDeletedEvent($this->generateFileMock());
        $subject->afterFileDeleted($event);
    }
    /**
     * @test
     * @return void
     */
    public function itFlushesCacheForFileAfterFileRenamed(): void
    {
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('flushCachesByTag')
            ->with('sys_file_42');

        $subject = new FlushCacheTagForFile($cacheManagerMock);
        $event = new afterFileRenamedEvent(
            $this->generateFileMock(),
            ""
        );
        $subject->afterFileRenamed($event);
    }
    /**
     * @test
     * @return void
     */
    public function itFlushesCacheForFileAfterFileReplaced(): void
    {
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('flushCachesByTag')
            ->with('sys_file_42');

        $subject = new FlushCacheTagForFile($cacheManagerMock);
        $event = new afterFileReplacedEvent(
            $this->generateFileMock(),
            ""
        );
        $subject->afterFileReplaced($event);
    }
    /**
     * @test
     * @return void
     */
    public function itFlushesCacheForFileAfterFileContentsSet(): void
    {
        $cacheManagerMock = $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cacheManagerMock->expects($this->once())
            ->method('flushCachesByTag')
            ->with('sys_file_42');

        $subject = new FlushCacheTagForFile($cacheManagerMock);
        $event = new afterFileContentsSetEvent(
            $this->generateFileMock(),
            ""
        );
        $subject->afterFileContentsSet($event);
    }
}