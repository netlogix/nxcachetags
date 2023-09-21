<?php
declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\EventListener;

use Netlogix\Nxcachetags\EventListener\GeneratePublicUrlForResource;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\MetaDataAspect;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class GeneratePublicUrlForResourceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     * @return void
     */
    public function itAddCacheTagsWhenInvokedWithFile(): void
    {
        $metaDataAspectMock = $this->getMockBuilder(MetaDataAspect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metaDataAspectMock->method('get')
            ->willReturn([
                'uid' => '41'
            ]);

        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileMock->method('getMetaData')
            ->willReturn($metaDataAspectMock);
        $fileMock->method('getUid')
            ->willReturn(42);



        $GLOBALS['TSFE']
            ->expects($this->once())
            ->method('addCacheTags')
            ->with(['sys_file_42', 'sys_file_metadata_41']);

        $event = new GeneratePublicUrlForResourceEvent(
            $fileMock,
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(DriverInterface::class)->getMock()
        );

        $subject = new GeneratePublicUrlForResource();
        $subject->__invoke($event);
    }

    /**
     * @test
     * @return void
     */
    public function itGetsOriginalFileOfProcessedFile(): void
    {
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processedFileMock = $this->getMockBuilder(ProcessedFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processedFileMock->expects($this->once())
            ->method('getOriginalFile')
            ->willReturn($fileMock);

        $event = new GeneratePublicUrlForResourceEvent(
            $processedFileMock,
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(DriverInterface::class)->getMock()
        );

        $subject = new GeneratePublicUrlForResource();
        $subject->__invoke($event);
    }

    /**
     * @test
     * @return void
     */
    public function itDoesNotAddCacheTagsIfTSFCIsNull(): void
    {
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TSFE'] = null;

        $event = new GeneratePublicUrlForResourceEvent(
            $fileMock,
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(DriverInterface::class)->getMock()
        );

        $subject = new GeneratePublicUrlForResource();
        $subject->__invoke($event);
    }

    /**
     * @test
     * @return void
     */
    public function itDoesNotAddCacheTagsMoreThenOnce(): void
    {
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileMock->method('getUid')
            ->willReturn(42);

        $GLOBALS['TSFE']
            ->expects($this->never())
            ->method('addCacheTags');

        $GLOBALS['TSFE']
            ->method('getPageCacheTags')
            ->willReturn(['sys_file_42']);

        $event = new GeneratePublicUrlForResourceEvent(
            $fileMock,
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(DriverInterface::class)->getMock()
        );

        $subject = new GeneratePublicUrlForResource();
        $subject->__invoke($event);
    }

    /**
     * @test
     * @return void
     */
    public function itDoesNotAddCacheTagsIfFileIsNotInstanceOfFileInterface (): void
    {
        $event = new GeneratePublicUrlForResourceEvent(
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(ResourceInterface::class)->getMock(),
            $this->getMockBuilder(DriverInterface::class)->getMock()
        );

        $GLOBALS['TSFE']
            ->expects($this->never())
            ->method('addCacheTags');

        $subject = new GeneratePublicUrlForResource();
        $subject->__invoke($event);
    }
}


