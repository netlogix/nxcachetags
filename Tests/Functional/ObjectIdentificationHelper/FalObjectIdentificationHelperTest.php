<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\ObjectIdentificationHelper;

use Netlogix\Nxcachetags\ObjectIdentificationHelper\FalObjectIdentificationHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class FalObjectIdentificationHelperTest extends FunctionalTestCase
{
    /**
     * @test
     *
     * @return void
     * @throws FileDoesNotExistException
     */
    public function itCanIdentifyFileReference()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');
        $this->setUpBackendUserFromFixture(1);

        $refUid = rand(1, 9999);
        $origUid = rand(1, 9999);

        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $conn->insert('sys_file', ['uid' => $origUid]);

        $ref = new FileReference(
            ['uid' => $refUid, 'uid_local' => $origUid, 'name' => uniqid('refName_'), 'storage' => 1]
        );

        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject($ref);

        self::assertCount(2, $res);
        self::assertEquals('sys_file_reference_' . $refUid, $res[0]);
        self::assertInstanceOf(File::class, $res[1]);
    }

    /**
     * @test
     *
     * @return void
     * @throws FileDoesNotExistException
     */
    public function itCanIdentifyFile()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');
        $this->setUpBackendUserFromFixture(1);

        $fileUid = rand(1, 9999);
        $metaUid = rand(1, 9999);

        $file = new File(['uid' => $fileUid, 'storage' => 1],
            GeneralUtility::makeInstance(StorageRepository::class)->findByUid(1));
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_metadata');
        $conn->insert('sys_file_metadata', ['uid' => $metaUid, 'file' => $fileUid]);

        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject($file);

        self::assertCount(3, $res);
        self::assertEquals('sys_file_' . $fileUid, $res[0]);
        self::assertInstanceOf(ResourceStorage::class, $res[1]);
        self::assertEquals('sys_file_metadata_' . $metaUid, $res[2]);
    }


    /**
     * @test
     *
     * @return void
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function itCanIdentifyResourceStorage()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');
        $this->setUpBackendUserFromFixture(1);

        $storage = GeneralUtility::makeInstance(StorageRepository::class)->findByUid(1);

        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject($storage);

        self::assertCount(1, $res);
        self::assertEquals('sys_file_storage_' . $storage->getUid(), $res[0]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itCanIdentifyExtbaseFileReference()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');
        $this->setUpBackendUserFromFixture(1);

        $refUid = rand(1, 9999);
        $fileUid = rand(1, 9999);

        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $conn->insert('sys_file', ['uid' => $fileUid, 'storage' => 1]);
        $conn->insert('sys_file_reference', ['uid' => $refUid, 'uid_local' => $fileUid]);

        $refData = ['uid' => $refUid, 'uid_local' => $fileUid];

        $ref = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(DataMapper::class)
            ->map(\TYPO3\CMS\Extbase\Domain\Model\FileReference::class, [$refData])[0];

        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject($ref);

        self::assertCount(1, $res);
        self::assertInstanceOf(FileReference::class, $res[0]);

        /** @var FileReference $outRef */
        $outRef = $res[0];

        self::assertEquals($outRef->getUid(), $refUid);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itCanIdentifyExtbaseFile()
    {
        $this->importDataSet('ntf://Database/sys_file_storage.xml');
        $this->setUpBackendUserFromFixture(1);

        $fileUid = rand(1, 9999);

        $fileData = ['uid' => $fileUid, 'storage' => 1];

        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $conn->insert('sys_file', $fileData);


        $file = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(DataMapper::class)
            ->map(\TYPO3\CMS\Extbase\Domain\Model\File::class, [$fileData])[0];

        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject($file);

        self::assertCount(1, $res);
        self::assertInstanceOf(File::class, $res[0]);

        /** @var File $outRef */
        $outRef = $res[0];

        self::assertEquals($outRef->getUid(), $fileUid);
    }

    /**
     * @test
     *
     * @return void
     */
    public function itReturnsEmptyTagsForUnknownType()
    {
        $subject = new FalObjectIdentificationHelper();

        $res = $subject->identifyCacheTagForObject(new \stdClass());

        self::assertEmpty($res);
    }

}