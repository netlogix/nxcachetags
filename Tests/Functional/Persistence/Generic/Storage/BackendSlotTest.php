<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Functional\Persistence\Generic\Storage;

use Netlogix\Nxcachetags\Persistence\Generic\Storage\BackendSlot;
use Netlogix\Nxcachetags\Service\CacheTagService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Backend;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class BackendSlotTest extends FunctionalTestCase
{

    /**
     * @test
     * @return void
     */
    public function slotIsCalledWhenInsertingObject()
    {
        $dispatcher = GeneralUtility::makeInstance(
            Dispatcher::class
        );
        $dispatcher->connect(
            Backend::class,
            'afterInsertObject',
            BackendSlot::class,
            'flushCacheForObject'
        );

        $object = new Category();
        $object->setTitle(uniqid('title_'));

        $om = GeneralUtility::makeInstance(ObjectManager::class);

        $subject = $this->getMockBuilder(BackendSlot::class)
            ->onlyMethods(['flushCacheForObject'])
            ->getMock();
        $subject->injectCacheTagService($om->get(CacheTagService::class));

        $subject->expects(self::once())->method('flushCacheForObject')->with($object);
        GeneralUtility::setSingletonInstance(BackendSlot::class, $subject);


        $repo = $om->get(CategoryRepository::class);
        $repo->add($object);
        $om->get(PersistenceManager::class)->persistAll();
    }

    /**
     * @test
     * @return void
     */
    public function slotIsCalledWhenUpdatingObject()
    {
        $dispatcher = GeneralUtility::makeInstance(
            Dispatcher::class
        );
        $dispatcher->connect(
            Backend::class,
            'afterUpdateObject',
            BackendSlot::class,
            'flushCacheForObject'
        );

        $uid = rand(1, 9999);

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_category')
            ->insert('sys_category', ['uid' => $uid, 'title' => uniqid('title_')]);

        $om = GeneralUtility::makeInstance(ObjectManager::class);

        $repo = $om->get(CategoryRepository::class);
        /** @var Category $cat */
        $cat = $repo->findByUid($uid);
        $cat->setTitle(uniqid('newtitle_'));


        $subject = $this->getMockBuilder(BackendSlot::class)
            ->onlyMethods(['flushCacheForObject'])
            ->getMock();
        $subject->injectCacheTagService($om->get(CacheTagService::class));


        $subject->expects(self::once())->method('flushCacheForObject')->with($cat);
        GeneralUtility::setSingletonInstance(BackendSlot::class, $subject);


        $repo->update($cat);
        $om->get(PersistenceManager::class)->persistAll();
    }

}
