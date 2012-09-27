<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 *
 * @package CONTENIDO API
 * @version 1.4
 * @author Timo Hummel
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Container configuration collection
 *
 * @package CONTENIDO API
 * @subpackage Model
 */
class cApiContainerConfigurationCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg['tab']['container_conf'], 'idcontainerc');
        $this->_setItemClass('cApiContainerConfiguration');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiTemplateConfigurationCollection');

        if ($select !== false) {
            $this->select($select);
        }
    }

    /**
     *
     * @deprecated [2011-03-15] Old constructor function for downwards
     *             compatibility
     */
    public function cApiContainerConfigurationCollection($select = false) {
        cDeprecated("Use __construct() instead");
        $this->__construct($select = false);
    }

    public function create($idtplcfg, $number, $container) {
        $item = parent::createNewItem();
        $item->set('idtplcfg', (int) $idtplcfg);
        $item->set('number', (int) $number);
        $item->set('container', $this->escape($container));
        $item->store();
    }

}

/**
 * Container configuration item
 *
 * @package CONTENIDO API
 * @subpackage Model
 */
class cApiContainerConfiguration extends Item {

    /**
     * Constructor Function
     *
     * @param mixed $mId Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['container_conf'], 'idcontainerc');
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /**
     *
     * @deprecated [2011-03-15] Old constructor function for downwards
     *             compatibility
     */
    public function cApiContainerConfiguration($mId = false) {
        cDeprecated("Use __construct() instead");
        $this->__construct($mId);
    }

}
