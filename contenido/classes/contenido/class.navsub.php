<?php
/**
 * This file contains the nav sub collection and item class.
 *
 * @package          Core
 * @subpackage       GenericDB_Model
 * @version          SVN Revision $Rev:$
 *
 * @author           Frederic Schneider
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * File collection
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiNavSubCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['nav_sub'], 'idnavs');
        $this->_setItemClass('cApiNavSub');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiNavMainCollection');
        $this->_setJoinPartner('cApiAreaCollection');
    }

    /**
     * Create new item with given values.
     *
     * @param int $navm
     * @param int|string  $area  Aread id or area name
     * @param int $level
     * @param string $location
     * @param int $online
     * @return cApiNavSub
     */
    public function create($navm, $area, $level, $location, $online = 1) {
        $item = parent::createNewItem();

        if (is_string($area)) {
            $c = new cApiArea();
            $c->loadBy('name', $area);

            if ($c->isLoaded()) {
                $area = $c->get('idarea');
            } else {
                $area = 0;
                cWarning(__FILE__, __LINE__, "Could not resolve area [$area] passed to method [create], assuming 0");
            }
        }

        $item->set('idnavm', $navm);
        $item->set('idarea', $area);
        $item->set('level', $level);
        $item->set('location', $location);
        $item->set('online', $online);

        $item->store();

        return $item;
    }

}

/**
 * NavMain item
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiNavSub extends Item {

    /**
     * Constructor Function
     *
     * @param mixed $mId Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['nav_sub'], 'idnavs');
        $this->setFilters(array(
            'addslashes'
        ), array(
            'stripslashes'
        ));
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

    /**
     * Userdefined setter for navsub fields.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $bSafe Flag to run defined inFilter on passed value
     * @todo should return return value of overloaded method
     */
    public function setField($name, $value, $bSafe = true) {
        switch ($name) {
            case 'idarea':
            case 'idnavm':
            case 'level':
                $value = (int) $value;
                break;
            case 'online':
                $value = (1 == $value) ? 1 : 0;
                break;
        }

        parent::setField($name, $value, $bSafe);
    }

}