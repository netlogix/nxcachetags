<?php

declare(strict_types=1);

namespace Netlogix\Nxcachetags\Tests\Unit\Service;

use Netlogix\Nxcachetags\Service\RenderingContextIdentificationService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

class RenderingContextIdentificationServiceTest extends UnitTestCase
{

    /**
     * @test
     *
     * @return void
     */
    public function itGeneratesHashOfRenderingContext()
    {
        $subject = $this->getMockBuilder(RenderingContextIdentificationService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $renderintContext = $this->getMockBuilder(RenderingContext::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariableProvider', 'getViewHelperVariableContainer'])
            ->getMock();

        $renderintContext->expects(self::once())->method('getVariableProvider')->willReturn(
            new StandardVariableProvider(['foo' => uniqid()])
        );
        $renderintContext->expects(self::once())->method('getViewHelperVariableContainer')->willReturn(
            new ViewHelperVariableContainer()
        );

        $res = $subject->identifyRenderingContext($renderintContext);

        self::assertNotEmpty($res);
    }
}

