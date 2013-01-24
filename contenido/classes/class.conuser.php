<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * This class will be a replacement for all other
 * user classes, which encapsulates only small parts
 * of user related tasks.
 *
 * In current version you can administer optional password checks
 * via following configuration values:
 *
 * - En- or disabling checks:
 *  $cfg['password']['check_password_mask'] = [true|false]
 *  Use this flag to enable (true) or disable (false) the mask checks.
 *
 *  $cfg['password']['use_cracklib'] = [true|false]
 *  Use this to enable (true) or disable (false) the strength check, currently done with cracklib.
 *
 * - Mask checks:
 *  Password mask checks are checks belonging to the "format" of the needed password string.
 *
 *  $cfg['password']['min_length'], int
 *     Minimum length a password has to have. If not set, 8 chars are set as default
 *  $cfg['password']['numbers_mandatory'], int
 *     If set to a value greater than 0, at least $cfg['password']['numbers_mandatory'] numbers
 *     must be in password
 *  $cfg['password']['symbols_mandatory'], int && $cfg['password']['symbols_regex'], String
 *      If 'symbols_mandatory' set to a value greater than 0, at least so many symbols has to appear in
 *      given password. What symbols are regcognized can be administrated via 'symbols_regex'. This has
 *      to be a regular expression which is used to "find" the symbols in $sNewPassword. If not set, following
 *      RegEx is used: "/[|!@#$%&*\/=?,;.:\-_+~^�\\\]/"
 *  $cfg['password']['mixed_case_mandatory'], int
 *      If set to a value greater than 0 so many lower and upper case character must appear in the password.
 *      (e.g.: if set to 2, 2 upper and 2 lower case characters must appear)
 *
 * - Strength check
 *  Passwords should have some special characteristics to be a strong, i.e. not easy to guess, password. Currently
 *  cracklib is supported. These are the configuration possibilities:
 *
 *  $cfg['password']['cracklib_dict'], string
 *     Path and file name (without file extension!) to dictionary you want to use. This setting is
 *     mandatory!
 *
 *  Keep in mind that these type of check only works if crack module is available.
 *
 * @package CONTENIDO Backend Classes
 * @subpackage Backend User
 *
 * @version 1.4.0
 * @author Bilal Arslan, Holger Librenz
 * @copyright four for business AG
 *
 * {@internal
 *   created 04.11.2008
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

// Exception classes
cInclude("exceptions", "exception.conuser.php");

/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Class User to handle all user related task.
 * In first implementations, it will only do some little
 * things, like checking and setting passwords.
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Classes
 * @subpackage Backend User
 *
 * @version    0.2.0
 * @author     Bilal Arslan, Holger Librenz
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release => 4.8.8
 *
 * @deprecated [2012-02-23] Use cApiUser instead
 */
class ConUser extends cApiUser {

    public function load($sUserId) {
        cDeprecated("Deprecated class. Please use cApiUser instead");

        $this->loadByPrimaryKey($sUserId);

        return true;
    }

    /**
     * Calls constructor in base class.
     *
     * @param array $aCfg
     * @param DB_Contenido $oDb
     * @param string $sUserId User ID the instnace of this class represents
     *
     * @return ConUser
     * @throws ConUserException
     */
    public function __construct($aCfg, $oDb = null, $sUserId = null) {
        cDeprecated("Deprecated class. Please use cApiUser instead");

        parent::__construct($sUserId);
    }

    /**
     * This function does update without password column to all columns of con_user table.
     *
     * @return void
     */
    public function saveUser() {
        cDeprecated("Deprecated class. Please use cApiUser instead");

        $this->store();
    }

}

?>