<?php

namespace Netlogix\Nxcachetags\ViewHelpers;

use Netlogix\Nxcachetags\Service\CacheService;
use Netlogix\Nxcachetags\Service\RenderingContextIdentificationService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CacheViewHelper extends AbstractViewHelper
{

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var RenderingContextIdentificationService
     */
    protected $renderingContextIdentificationService;

    protected $escapeOutput = false;

    public function injectCacheService(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function injectRenderingContextIdentificationService(RenderingContextIdentificationService $renderingContextIdentificationService
    ) {
        $this->renderingContextIdentificationService = $renderingContextIdentificationService;
    }

    public function initializeArguments()
    {
        $this->registerArgument('identifiedBy', 'array', '', true, []);
        $this->registerArgument('lifetime', 'int', '', null, 0);
        $this->registerArgument('lifetimeSource', 'array', '', null, []);
        $this->registerArgument('taggedBy', 'array', '', null, []);
        $this->registerArgument('includeLanguage', 'bool', '', null, true);
        $this->registerArgument('includeUserGroups', 'bool', '', null, true);
        $this->registerArgument('includeBackendLogin', 'bool', '', null, true);
        $this->registerArgument('includeRootPage', 'bool', '', null, true);
    }

    public function render()
    {
        $identifiedBy = $this->arguments['identifiedBy'];
        foreach ($identifiedBy as $key => $identifierPart) {
            if (!$identifierPart) {
                $identifiedBy[$key] = $this->renderingContextIdentificationService->identifyRenderingContext($this->renderingContext);
            }
        }

        return $this->cacheService->render(
            [$this, 'renderChildren'],
            $identifiedBy,
            $this->arguments['lifetime'],
            $this->arguments['lifetimeSource'],
            $this->arguments['taggedBy'],
            $this->arguments['includeLanguage'],
            $this->arguments['includeUserGroups'],
            $this->arguments['includeBackendLogin'],
            $this->arguments['includeRootPage']
        );
    }

}
