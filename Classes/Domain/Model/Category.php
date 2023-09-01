<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class Category extends AbstractEntity
{
    /**
     * @Extbase\Validate("NotEmpty")
     */
    protected string $title = '';

    protected string $description = '';

    /**
     * @Extbase\ORM\Lazy
     */
    protected ?Category $parent = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getParent(): ?Category
    {
        if ($this->parent instanceof LazyLoadingProxy) {
            $this->parent->_loadRealInstance();
        }
        return $this->parent;
    }

    public function setParent(Category $parent)
    {
        $this->parent = $parent;
    }
}