<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Mail log success management class
 *
 * @package CONTENIDO API
 * @version 0.1
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
 * Mail log success collection
 *
 * @package CONTENIDO API
 * @subpackage Model
 */
class cApiMailLogSuccessCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['mail_log_success'], 'idmailsuccess');
        $this->_setItemClass('cApiMailLogSuccess');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiMailLogCollection');
    }

    /**
     * Creates a new mail log success entry with the given data.
     *
     * @param int $idmail
     * @param array $recipient
     * @param boolean $success
     * @param string $exception
     * @return boolean
     */
    public function create($idmail, $recipient, $success, $exception) {
        $item = parent::createNewItem();

        $item->set('idmail', $idmail);
        $item->set('recipient', json_encode($recipient));
        $item->set('success', $success);
        $item->set('exception', $exception);

        $item->store();

        return true;
    }

}

/**
 * Mail log success item
 *
 * @package CONTENIDO API
 * @subpackage Model
 */
class cApiMailLogSuccess extends Item {

    /**
     *
     * @param mixed $mId
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['mail_log_success'], 'idmailsuccess');
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}
