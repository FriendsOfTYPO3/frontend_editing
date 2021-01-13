<?php

declare(strict_types=1);
namespace TYPO3\CMS\FrontendEditing\Tests\Unit\Service\ContentEditable;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendEditing\Utility\ConfigurationUtility;
use TYPO3\CMS\Styleguide\TcaDataGenerator\TableHandler\General;

/**
 * Test case for class TYPO3\CMS\FrontendEditing\Service\ExtensionManagerConfigurationService.
 *
 * @extensionScannerIgnoreFile
 */
class ExtensionManagerConfigurationServiceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getExtensionManagerSettings()
    {
        $extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);

        $extensionConfigurationMock
            ->method('get')
            ->willReturn([]);

        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfigurationMock);

        self::assertSame(
            ConfigurationUtility::getExtensionConfiguration(),
            []
        );
    }
}
