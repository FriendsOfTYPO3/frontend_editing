<?php
declare(strict_types = 1);
namespace TYPO3\CMS\FrontendEditing\EditingPanel;

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

/**
 * Allow to manipulate with content before wrap
 */
interface FrontendEditingDropzoneModifier
{
    /**
     * @param string $table DB table name
     * @param int $editUid Content uid
     * @param array $dataArr Content data array
     * @param string $content HTML content of element
     * @return bool TRUE if content was wrapped
     */
    public function wrapWithDropzone(
        string $table,
        int $editUid,
        array $dataArr,
        string &$content
    ): bool;
}
