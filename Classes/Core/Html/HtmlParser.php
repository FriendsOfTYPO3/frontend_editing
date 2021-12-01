<?php

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

namespace TYPO3\CMS\FrontendEditing\Core\Html;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * A class for being able to use Frontend Editing with TYPO3 11 and PHP 8.
 * The only modification in this file is on line 220 and check for if
 * userFunc is set.
 *
 * if (isset($params['userFunc.']) && is_array($params['userFunc.'])) {
 */
class HtmlParser extends \TYPO3\CMS\Core\Html\HtmlParser
{
    /**
     * @inheritDoc
     */
    public function HTMLcleaner($content, $tags = [], $keepAll = 0, $hSC = 0, $addConfig = [])
    {
        $newContent = [];
        $tokArr = explode('<', $content);
        $newContent[] = $this->bidir_htmlspecialchars(current($tokArr), $hSC);
        // We skip the first element in foreach loop
        $tokArrSliced = array_slice($tokArr, 1, null, true);
        $c = 1;
        $tagRegister = [];
        $tagStack = [];
        $inComment = false;
        $inCdata = false;
        $skipTag = false;
        foreach ($tokArrSliced as $tok) {
            if ($inComment) {
                if (($eocPos = strpos($tok, '-->')) === false) {
                    // End of comment is not found in the token. Go further until end of comment is found in other tokens.
                    $newContent[$c++] = '<' . $tok;
                    continue;
                }
                // Comment ends in the middle of the token: add comment and proceed with rest of the token
                $newContent[$c++] = '<' . substr($tok, 0, $eocPos + 3);
                $tok = substr($tok, $eocPos + 3);
                $inComment = false;
                $skipTag = true;
            } elseif ($inCdata) {
                if (($eocPos = strpos($tok, '/*]]>*/')) === false) {
                    // End of comment is not found in the token. Go further until end of comment is found in other tokens.
                    $newContent[$c++] = '<' . $tok;
                    continue;
                }
                // Comment ends in the middle of the token: add comment and proceed with rest of the token
                $newContent[$c++] = '<' . substr($tok, 0, $eocPos + 10);
                $tok = substr($tok, $eocPos + 10);
                $inCdata = false;
                $skipTag = true;
            } elseif (strpos($tok, '!--') === 0) {
                if (($eocPos = strpos($tok, '-->')) === false) {
                    // Comment started in this token but it does end in the same token. Set a flag to skip till the end of comment
                    $newContent[$c++] = '<' . $tok;
                    $inComment = true;
                    continue;
                }
                // Start and end of comment are both in the current token. Add comment and proceed with rest of the token
                $newContent[$c++] = '<' . substr($tok, 0, $eocPos + 3);
                $tok = substr($tok, $eocPos + 3);
                $skipTag = true;
            } elseif (strpos($tok, '![CDATA[*/') === 0) {
                if (($eocPos = strpos($tok, '/*]]>*/')) === false) {
                    // Comment started in this token but it does end in the same token. Set a flag to skip till the end of comment
                    $newContent[$c++] = '<' . $tok;
                    $inCdata = true;
                    continue;
                }
                // Start and end of comment are both in the current token. Add comment and proceed with rest of the token
                $newContent[$c++] = '<' . substr($tok, 0, $eocPos + 10);
                $tok = substr($tok, $eocPos + 10);
                $skipTag = true;
            }
            $firstChar = $tok[0] ?? null;
            // It is a tag... (first char is a-z0-9 or /) (fixed 19/01 2004). This also avoids triggering on <?xml..> and <!DOCTYPE..>
            if (!$skipTag && preg_match('/[[:alnum:]\\/]/', (string)$firstChar) === 1) {
                $tagEnd = strpos($tok, '>');
                // If there is and end-bracket...	tagEnd can't be 0 as the first character can't be a >
                if ($tagEnd) {
                    $endTag = $firstChar === '/' ? 1 : 0;
                    $tagContent = substr($tok, $endTag, $tagEnd - $endTag);
                    $tagParts = preg_split('/\\s+/s', $tagContent, 2);
                    $tagName = strtolower($tagParts[0]);
                    $emptyTag = 0;
                    if (isset($tags[$tagName])) {
                        // If there is processing to do for the tag:
                        if (is_array($tags[$tagName])) {
                            if (preg_match('/^(' . self::VOID_ELEMENTS . ' )$/i', $tagName)) {
                                $emptyTag = 1;
                            }
                            // If NOT an endtag, do attribute processing (added dec. 2003)
                            if (!$endTag) {
                                // Override attributes
                                if (isset($tags[$tagName]['overrideAttribs']) && (string)$tags[$tagName]['overrideAttribs'] !== '') {
                                    $tagParts[1] = $tags[$tagName]['overrideAttribs'];
                                }
                                // Allowed tags
                                if (isset($tags[$tagName]['allowedAttribs']) && (string)$tags[$tagName]['allowedAttribs'] !== '') {
                                    // No attribs allowed
                                    if ((string)$tags[$tagName]['allowedAttribs'] === '0') {
                                        $tagParts[1] = '';
                                    } elseif (isset($tagParts[1]) && trim($tagParts[1])) {
                                        $tagAttrib = $this->get_tag_attributes($tagParts[1]);
                                        $tagParts[1] = '';
                                        $newTagAttrib = [];
                                        $tList = (array)(
                                            $tags[$tagName]['_allowedAttribs']
                                            ?? GeneralUtility::trimExplode(',', strtolower($tags[$tagName]['allowedAttribs']), true)
                                        );
                                        foreach ($tList as $allowTag) {
                                            if (isset($tagAttrib[0][$allowTag])) {
                                                $newTagAttrib[$allowTag] = $tagAttrib[0][$allowTag];
                                            }
                                        }

                                        $tagParts[1] = $this->compileTagAttribs($newTagAttrib, $tagAttrib[1]);
                                    }
                                }
                                // Fixed attrib values
                                if (isset($tags[$tagName]['fixAttrib']) && is_array($tags[$tagName]['fixAttrib'])) {
                                    $tagAttrib = $this->get_tag_attributes($tagParts[1] ?? '');
                                    $tagParts[1] = '';
                                    foreach ($tags[$tagName]['fixAttrib'] as $attr => $params) {
                                        if (isset($params['set']) && $params['set'] !== '') {
                                            $tagAttrib[0][$attr] = $params['set'];
                                        }
                                        if (!empty($params['unset'])) {
                                            unset($tagAttrib[0][$attr]);
                                        }
                                        if (!empty($params['default']) && !isset($tagAttrib[0][$attr])) {
                                            $tagAttrib[0][$attr] = $params['default'];
                                        }
                                        if (($params['always'] ?? false) || isset($tagAttrib[0][$attr])) {
                                            if ($params['trim'] ?? false) {
                                                $tagAttrib[0][$attr] = trim($tagAttrib[0][$attr]);
                                            }
                                            if ($params['intval'] ?? false) {
                                                $tagAttrib[0][$attr] = (int)$tagAttrib[0][$attr];
                                            }
                                            if ($params['lower'] ?? false) {
                                                $tagAttrib[0][$attr] = strtolower($tagAttrib[0][$attr]);
                                            }
                                            if ($params['upper'] ?? false) {
                                                $tagAttrib[0][$attr] = strtoupper($tagAttrib[0][$attr]);
                                            }
                                            if ($params['range'] ?? false) {
                                                if (isset($params['range'][1])) {
                                                    $tagAttrib[0][$attr] = MathUtility::forceIntegerInRange($tagAttrib[0][$attr], (int)$params['range'][0], (int)$params['range'][1]);
                                                } else {
                                                    $tagAttrib[0][$attr] = MathUtility::forceIntegerInRange($tagAttrib[0][$attr], (int)$params['range'][0]);
                                                }
                                            }
                                            if (isset($params['list']) && is_array($params['list'])) {
                                                // For the class attribute, remove from the attribute value any class not in the list
                                                // Classes are case sensitive
                                                if ($attr === 'class') {
                                                    $newClasses = [];
                                                    $classes = GeneralUtility::trimExplode(' ', $tagAttrib[0][$attr], true);
                                                    foreach ($classes as $class) {
                                                        if (in_array($class, $params['list'])) {
                                                            $newClasses[] = $class;
                                                        }
                                                    }
                                                    if (!empty($newClasses)) {
                                                        $tagAttrib[0][$attr] = implode(' ', $newClasses);
                                                    } else {
                                                        $tagAttrib[0][$attr] = $params['list'][0];
                                                    }
                                                } else {
                                                    if (!in_array($this->caseShift($tagAttrib[0][$attr], $params['casesensitiveComp'] ?? false), (array)$this->caseShift($params['list'], $params['casesensitiveComp'], $tagName))) {
                                                        $tagAttrib[0][$attr] = $params['list'][0];
                                                    }
                                                }
                                            }
                                            if (
                                                (($params['removeIfFalse'] ?? false) && $params['removeIfFalse'] !== 'blank' && !$tagAttrib[0][$attr])
                                                || (($params['removeIfFalse'] ?? false) && $params['removeIfFalse'] === 'blank' && (string)$tagAttrib[0][$attr] === '')
                                            ) {
                                                unset($tagAttrib[0][$attr]);
                                            }
                                            if ((string)($params['removeIfEquals'] ?? '') !== '' && $this->caseShift($tagAttrib[0][$attr], $params['casesensitiveComp']) === $this->caseShift($params['removeIfEquals'], $params['casesensitiveComp'])) {
                                                unset($tagAttrib[0][$attr]);
                                            }
                                            if ($params['prefixLocalAnchors'] ?? false) {
                                                if ($tagAttrib[0][$attr][0] === '#') {
                                                    if ($params['prefixLocalAnchors'] == 2) {
                                                        /** @var ContentObjectRenderer $contentObjectRenderer */
                                                        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                                                        $prefix = $contentObjectRenderer->getUrlToCurrentLocation();
                                                    } else {
                                                        $prefix = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
                                                    }
                                                    $tagAttrib[0][$attr] = $prefix . $tagAttrib[0][$attr];
                                                }
                                            }
                                            if ($params['prefixRelPathWith'] ?? false) {
                                                $urlParts = parse_url($tagAttrib[0][$attr]);
                                                if (!$urlParts['scheme'] && $urlParts['path'][0] !== '/') {
                                                    // If it is NOT an absolute URL (by http: or starting "/")
                                                    $tagAttrib[0][$attr] = $params['prefixRelPathWith'] . $tagAttrib[0][$attr];
                                                }
                                            }
                                            if ($params['userFunc'] ?? false) {
                                                // Added a check for if userFunc is set for PHP 8
                                                if (isset($params['userFunc.']) && is_array($params['userFunc.'])) {
                                                    $params['userFunc.']['attributeValue'] = $tagAttrib[0][$attr];
                                                } else {
                                                    $params['userFunc.'] = $tagAttrib[0][$attr];
                                                }
                                                $tagAttrib[0][$attr] = GeneralUtility::callUserFunction($params['userFunc'], $params['userFunc.'], $this);
                                            }
                                        }
                                    }
                                    $tagParts[1] = $this->compileTagAttribs($tagAttrib[0], $tagAttrib[1]);
                                }
                            } else {
                                // If endTag, remove any possible attributes:
                                $tagParts[1] = '';
                            }
                            // Protecting the tag by converting < and > to &lt; and &gt; ??
                            if (!empty($tags[$tagName]['protect'])) {
                                $lt = '&lt;';
                                $gt = '&gt;';
                            } else {
                                $lt = '<';
                                $gt = '>';
                            }
                            // Remapping tag name?
                            if (!empty($tags[$tagName]['remap'])) {
                                $tagParts[0] = $tags[$tagName]['remap'];
                            }
                            // rmTagIfNoAttrib
                            if ($endTag || empty($tags[$tagName]['rmTagIfNoAttrib']) || trim($tagParts[1] ?? '')) {
                                $setTag = true;
                                // Remove this closing tag if $tagName was among $TSconfig['removeTags']
                                if ($endTag
                                    && isset($tags[$tagName]['allowedAttribs']) && $tags[$tagName]['allowedAttribs'] === 0
                                    && isset($tags[$tagName]['rmTagIfNoAttrib']) && $tags[$tagName]['rmTagIfNoAttrib'] === 1
                                ) {
                                    $setTag = false;
                                }
                                if (isset($tags[$tagName]['nesting'])) {
                                    if (!isset($tagRegister[$tagName])) {
                                        $tagRegister[$tagName] = [];
                                    }
                                    if ($endTag) {
                                        $correctTag = true;
                                        if ($tags[$tagName]['nesting'] === 'global') {
                                            $lastEl = end($tagStack);
                                            if ($tagName !== $lastEl) {
                                                if (in_array($tagName, $tagStack, true)) {
                                                    while (!empty($tagStack) && $tagName !== $lastEl) {
                                                        $elPos = end($tagRegister[$lastEl]);
                                                        unset($newContent[$elPos]);
                                                        array_pop($tagRegister[$lastEl]);
                                                        array_pop($tagStack);
                                                        $lastEl = end($tagStack);
                                                    }
                                                } else {
                                                    // In this case the
                                                    $correctTag = false;
                                                }
                                            }
                                        }
                                        if (empty($tagRegister[$tagName]) || !$correctTag) {
                                            $setTag = false;
                                        } else {
                                            array_pop($tagRegister[$tagName]);
                                            if ($tags[$tagName]['nesting'] === 'global') {
                                                array_pop($tagStack);
                                            }
                                        }
                                    } else {
                                        $tagRegister[$tagName][] = $c;
                                        if ($tags[$tagName]['nesting'] === 'global') {
                                            $tagStack[] = $tagName;
                                        }
                                    }
                                }
                                if ($setTag) {
                                    // Setting the tag
                                    $newContent[$c++] = $lt . ($endTag ? '/' : '') . trim($tagParts[0] . ' ' . ($tagParts[1] ?? '')) . ($emptyTag ? ' /' : '') . $gt;
                                }
                            }
                        } else {
                            $newContent[$c++] = '<' . ($endTag ? '/' : '') . $tagContent . '>';
                        }
                    } elseif ($keepAll) {
                        // This is if the tag was not defined in the array for processing:
                        if ($keepAll === 'protect') {
                            $lt = '&lt;';
                            $gt = '&gt;';
                        } else {
                            $lt = '<';
                            $gt = '>';
                        }
                        $newContent[$c++] = $lt . ($endTag ? '/' : '') . $tagContent . $gt;
                    }
                    $newContent[$c++] = $this->bidir_htmlspecialchars(substr($tok, $tagEnd + 1), $hSC);
                } else {
                    $newContent[$c++] = $this->bidir_htmlspecialchars('<' . $tok, $hSC);
                }
            } else {
                $newContent[$c++] = $this->bidir_htmlspecialchars(($skipTag ? '' : '<') . $tok, $hSC);
                // It was not a tag anyways
                $skipTag = false;
            }
        }
        // Unsetting tags:
        foreach ($tagRegister as $tag => $positions) {
            foreach ($positions as $pKey) {
                unset($newContent[$pKey]);
            }
        }
        $newContent = implode('', $newContent);
        $newContent = $this->stripEmptyTagsIfConfigured($newContent, $addConfig);
        return $newContent;
    }
}
