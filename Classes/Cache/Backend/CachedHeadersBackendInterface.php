<?php

namespace Netlogix\Nxcachetags\Cache\Backend;

use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;

interface CachedHeadersBackendInterface extends TaggableBackendInterface
{

    /**
     * Returns all cache tags being stored for this particular identifier.
     *
     * @param string $entryIdentifier
     * @return string[]
     */
    public function getCachedHeaders(string $entryIdentifier): array;

}
