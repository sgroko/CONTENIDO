<?php

/**
 * This file contains the cHTMLParagraph class.
 *
 * @package Core
 * @subpackage GUI_HTML
 *
 * @author Simon Sprankel
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * cHTMLParagraph class represents a paragraph.
 *
 * @package Core
 * @subpackage GUI_HTML
 */
class cHTMLParagraph extends cHTMLContentElement {

    /**
     * Constructor to create an instance of this class.
     *
     * Creates an HTML p element.
     *
     * @param mixed $content [optional]
     *         String or object with the contents
     * @param string $class [optional]
     *         class of this element
     * @param string $id [optional]
     *         the ID of this element
     */
    public function __construct($content = '', $class = '', $id = '') {
        parent::__construct($content, $class, $id);
        $this->_tag = 'p';
    }
}
