<?php
/**
 * This file contains the cHTMLArticle class.
 *
 * @package Core
 * @subpackage HTML
 * @version SVN Revision $Rev:$
 *
 * @author Simon Sprankel
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * cHTMLArticle class represents an HTML5 article element.
 * Examples: Forum posts, newspaper articles, blog entries, user-submitted
 * comments, ...
 *
 * @package Core
 * @subpackage Frontend
 */
class cHTMLArticle extends cHTMLContentElement {

    public function __construct($content = '', $class = '', $id = '') {
        parent::__construct($content, $class, $id);
        $this->_tag = 'article';
    }

}