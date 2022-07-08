<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * To identify a distinct rendering context, some protected
 * properties have to be considered, which means accessing them
 * requires this service to extend the RenderingContext.
 *
 * Just passing the rendering context through serialize doesn't
 * work because the contained controller context might contain
 * closures.
 */
class RenderingContextIdentificationService extends RenderingContext implements SingletonInterface
{

    /**
     * Returns an identifier describing a distinct rendering context
     */
    public function identifyRenderingContext(RenderingContextInterface $renderingContext): string
    {
        return md5(serialize([
            'variableProvider' => $renderingContext->getVariableProvider(),
            'viewHelperVariableContainer' => $renderingContext->getViewHelperVariableContainer(),
        ]));
    }

}
