<?php

namespace TYPO3\CMS\FrontendEditing\Provider\Avatar;

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

use TYPO3\CMS\Backend\Backend\Avatar\DefaultAvatarProvider;
use TYPO3\CMS\Backend\Backend\Avatar\Image;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Provide avatar for top panel
 */
class FrontendEditingAvatarProvider extends DefaultAvatarProvider
{
    /**
     * Return an Image object for rendering the avatar, based on a FAL-based file
     *
     * @param array $backendUser be_users record
     * @param int $size
     * @return Image|null
     */
    public function getImage(array $backendUser, $size)
    {
        $image = parent::getImage($backendUser, $size);

        // Add trailing slashes to make sure we get an absolute url for FE
        if ($image === null) {
            $image = GeneralUtility::makeInstance(
                Image::class,
                PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('core')) .
                'Resources/Public/Icons/T3Icons/avatar/avatar-default.svg',
                $size,
                $size
            );
        } elseif (!GeneralUtility::isFirstPartOfStr($image->getUrl(true), '/')) {
            $image = GeneralUtility::makeInstance(
                Image::class,
                '//' . $image->getUrl(true),
                $image->getWidth(),
                $image->getHeight()
            );
        }

        return $image;
    }
}
