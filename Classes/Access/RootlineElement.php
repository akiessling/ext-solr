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

namespace ApacheSolrForTypo3\Solr\Access;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An element in the "Access Rootline". Represents the frontend user group
 * access restrictions for a page, a page's content, or a generic record.
 *
 * @author Ingo Renner <ingo@typo3.org>
 */
class RootlineElement
{

    /**
     * Page access rootline element.
     *
     * @var int
     */
    const ELEMENT_TYPE_PAGE = 1;

    /**
     * Content access rootline element.
     *
     * @var int
     */
    const ELEMENT_TYPE_CONTENT = 2;

    /**
     * Record access rootline element.
     *
     * @var int
     */
    const ELEMENT_TYPE_RECORD = 3;

    /**
     * Delimiter between the page ID and the groups set for a page.
     *
     * @var string
     */
    const PAGE_ID_GROUP_DELIMITER = ':';

    /**
     * Access type, either page (default) or content. Depending on the type,
     * access is granted differently. For pages the user must meet at least one
     * group requirement, for content all group requirements must be met.
     *
     * @var int
     */
    protected $type = self::ELEMENT_TYPE_PAGE;

    /**
     * Page Id for the element. NULL for the content type.
     *
     * @var int|null
     */
    protected ?int $pageId = null;

    /**
     * Set of access groups assigned to the element.
     *
     * @var array
     */
    protected $accessGroups = [];

    /**
     * Constructor for RootlineElement.
     *
     * @param string $element String representation of an element in the access rootline, usually of the form pageId:commaSeparatedPageAccessGroups
     * @throws    RootlineElementFormatException on wrong access format.
     */
    public function __construct($element)
    {
        $elementAccess = explode(self::PAGE_ID_GROUP_DELIMITER, $element);

        if (count($elementAccess) === 1 || $elementAccess[0] === 'c') {
            // the content access groups part of the access rootline
            $this->type = self::ELEMENT_TYPE_CONTENT;

            if (count($elementAccess) === 1) {
                $elementGroups = $elementAccess[0];
            } else {
                $elementGroups = $elementAccess[1];
            }
        } elseif ($elementAccess[0] === 'r') {
            // record element type
            if (count($elementAccess) !== 2) {
                throw new RootlineElementFormatException(
                    'Wrong Access Rootline Element format for a record type element.',
                    1308342937
                );
            }

            $this->type = self::ELEMENT_TYPE_RECORD;
            $elementGroups = $elementAccess[1];
        } else {
            // page element type
            if (count($elementAccess) !== 2 || !is_numeric($elementAccess[0])) {
                throw new RootlineElementFormatException(
                    'Wrong Access Rootline Element format for a page type element.',
                    1294421105
                );
            }

            $this->pageId = (int)($elementAccess[0]);
            $elementGroups = $elementAccess[1];
        }

        $this->accessGroups = GeneralUtility::intExplode(',', $elementGroups);
    }

    /**
     * Returns the String representation of an access rootline element.
     *
     * @return string Access Rootline Element string representation
     */
    public function __toString()
    {
        $rootlineElement = '';

        if ($this->type == self::ELEMENT_TYPE_CONTENT) {
            $rootlineElement .= 'c';
        } elseif ($this->type == self::ELEMENT_TYPE_RECORD) {
            $rootlineElement .= 'r';
        } else {
            $rootlineElement .= $this->pageId;
        }

        $rootlineElement .= self::PAGE_ID_GROUP_DELIMITER;
        $rootlineElement .= implode(',', $this->accessGroups);

        return $rootlineElement;
    }

    /**
     * Gets the access rootline element's type.
     *
     * @return int ELEMENT_TYPE_PAGE for page, ELEMENT_TYPE_CONTENT for content access rootline elements
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the page Id for page type elements.
     *
     * @return int Page Id.
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Gets the element's access group restrictions.
     *
     * @return array Array of user group Ids
     */
    public function getGroups()
    {
        return $this->accessGroups;
    }
}
