<?php

namespace Netlogix\Nxcachetags\ObjectIdentificationHelper;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Identify a given object if possible.
 */
interface ObjectIdentificationHelperInterface extends SingletonInterface
{

    /**
     * Returns cache cache tag parts for the given object if known.
     * @param mixed $object
     *
     * @return array
     */
    public function identifyCacheTagForObject($object): array;

}
