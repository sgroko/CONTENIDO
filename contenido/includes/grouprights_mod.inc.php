<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * CONTENIDO Group Rights Mod
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Includes
 * @version    1.0.0
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   created unknown
 *   $Id$:
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

//notice $oTpl is filled and generated in file rights.inc.php this file renders $oTpl to browser
include_once($cfg['path']['contenido'] . 'includes/grouprights.inc.php');

//set the areas which are in use fore selecting these
$possible_area = "'" . implode("','", $area_tree[$perm->showareas("mod")]) . "'";
$sql = "SELECT A.idarea, A.idaction, A.idcat, B.name, C.name FROM " . $cfg["tab"]["rights"] . " AS A, " . $cfg["tab"]["area"] . " AS B, " . $cfg["tab"]["actions"] . " AS C WHERE user_id='" . cSecurity::escapeDB($groupid, $db) . "' AND idclient='" . cSecurity::toInteger($rights_client) . "' AND A.type = 1 AND idlang='" . cSecurity::toInteger($rights_lang) . "' AND B.idarea IN ($possible_area) AND idcat!='0' AND A.idaction = C.idaction AND A.idarea = C.idarea AND A.idarea = B.idarea";
$db->query($sql);
$rights_list_old = array();
while ($db->next_record()) { //set a new rights list fore this user
    $rights_list_old[$db->f(3) . "|" . $db->f(4) . "|" . $db->f("idcat")] = "x";
}

if (($perm->have_perm_area_action($area, $action)) && ($action == "group_edit")) {
    saverights();
} else {
    if (!$perm->have_perm_area_action($area, $action)) {
        $notification->displayNotification("error", i18n("Permission denied"));
    }
}

// Init the temp variables
$sJsBefore = '';
$sJsAfter = '';
$sJsExternal = '';
$sTable = '';

// declare new javascript variables;
$sJsBefore .= "var itemids = new Array();
               var actareaids = new Array();";
$colspan = 0;
$aSecondHeaderRow = array();
$possible_areas = array();

//Init Table
$oTable = new cHTMLTable;
$oTable->updateAttributes(array("class" => "generic", "cellspacing" => "0", "cellpadding" => "2"));
$objHeaderRow = new cHTMLTableRow;
$objHeaderItem = new cHTMLTableHead;
$objFooterRow = new cHTMLTableRow;
$objFooterItem = new cHTMLTableData;
$objRow = new cHTMLTableRow;
$objItem = new cHTMLTableData;

//table header
//1. zeile
$headeroutput = "";
$items = "";
$objHeaderItem->updateAttributes(array("class" => "center", "valign" => "top", "align" => "center"));
$objHeaderItem->setContent(i18n("Module name"));
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
$objHeaderItem->setContent("Description");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();

// look for possible actions in mainarea []
foreach ($right_list["mod"] as $value2) {
    //if there are some actions
    if (is_array($value2["action"])) {
        foreach ($value2["action"] as $key3 => $value3) {       //set the areas that are in use
            $possible_areas[$value2["perm"]] = "";

            $colspan++;
            //set  the possible areas and actions for this areas
            $sJsBefore .= "actareaids[\"$value3|" . $value2["perm"] . "\"]=\"x\";\n";

            //checkbox for the whole action
            $objHeaderItem->setContent($lngAct[$value2["perm"]][$value3]);
            $items .= $objHeaderItem->render();
            $objHeaderItem->advanceID();
            array_push($aSecondHeaderRow, "<input type=\"checkbox\" name=\"checkall_" . $value2["perm"] . "_$value3\" value=\"\" onclick=\"setRightsFor('" . $value2["perm"] . "','$value3','')\">");
        }
    }
}
//checkbox for all rights
$objHeaderItem->setContent(i18n("Check all"));
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
array_push($aSecondHeaderRow, "<input type=\"checkbox\" name=\"checkall\" value=\"\" onClick=\"setRightsForAll()\">");
$colspan++;

$objHeaderRow->updateAttributes(array("class" => "textw_medium"));
$objHeaderRow->setContent($items);
$items = "";
$headeroutput .= $objHeaderRow->render();
$objHeaderRow->advanceID();

//2. zeile
$objHeaderItem->updateAttributes(array("class" => "center", "valign" => "", "align" => "center", "style" => "border-top-width: 0px;"));
$objHeaderItem->setContent("&nbsp;");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
$objHeaderItem->setContent("&nbsp;");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();

foreach ($aSecondHeaderRow as $value) {
    $objHeaderItem->setContent($value);
    $items .= $objHeaderItem->render();
    $objHeaderItem->advanceID();
}

$objHeaderRow->updateAttributes(array("class" => "textw_medium"));
$objHeaderRow->setContent($items);
$items = "";
$headeroutput .= $objHeaderRow->render();
$objHeaderRow->advanceID();

//table content
$output = "";
//Select the itemid�s
$sql = "SELECT * FROM " . $cfg["tab"]["mod"] . " WHERE idclient='" . cSecurity::toInteger($rights_client) . "' ORDER BY name";
$db->query($sql);

while ($db->next_record()) {
    $tplname = htmlentities($db->f("name"));
    $description = htmlentities($db->f("description"));

    $objItem->updateAttributes(array("class" => "td_rights0"));
    $objItem->setContent($tplname);
    $items .= $objItem->render();
    $objItem->advanceID();

    $objItem->updateAttributes(array("class" => "td_rights1", "style" => "white-space:normal;"));
    $objItem->setContent($description);
    $items .= $objItem->render();
    $objItem->advanceID();

    //set javscript array for itemids
    $sJsAfter .= "itemids[\"" . $db->f("idmod") . "\"]=\"x\";\n";

    // look for possible actions in mainarea[]
    foreach ($right_list["mod"] as $value2) {
        //if there area some
        if (is_array($value2["action"])) {
            foreach ($value2["action"] as $key3 => $value3) {
                //does the user have the right
                if (in_array($value2["perm"] . "|$value3|" . $db->f("idmod"), array_keys($rights_list_old))) {
                    $checked = "checked=\"checked\"";
                } else {
                    $checked = "";
                }

                // Set the checkbox the name consits of areait+actionid+itemid
                $objItem->updateAttributes(array("class" => "td_rights2", "style" => ""));
                $objItem->setContent("<input type=\"checkbox\" name=\"rights_list[" . $value2["perm"] . "|$value3|" . $db->f("idmod") . "]\" value=\"x\" $checked>");
                $items .= $objItem->render();
                $objItem->advanceID();
            }
        }
    }

    //checkbox for checking all actions fore this itemid
    $objItem->updateAttributes(array("class" => "td_rights3"));
    $objItem->setContent("<input type=\"checkbox\" name=\"checkall_" . $value2["perm"] . "_" . $value3 . "_" . $db->f("idmod") . "\" value=\"\" onClick=\"setRightsFor('" . $value2["perm"] . "','$value3','" . $db->f("idmod") . "')\">");
    $items .= $objItem->render();
    $objItem->advanceID();

    $objRow->setContent($items);
    $items = "";
    $output .= $objRow->render();
    $objRow->advanceID();
}

//table footer
$footeroutput = "";
$objItem->updateAttributes(array("class" => "", "valign" => "top", "align" => "right", "colspan" => "10"));
$objItem->setContent("<a href=javascript:submitrightsform('','area')><img src=\"" . $cfg['path']['images'] . "but_cancel.gif\" border=0></a><img src=\"images/spacer.gif\" width=\"20\"> <a href=javascript:submitrightsform('group_edit','')><img src=\"" . $cfg['path']['images'] . "but_ok.gif\" border=0></a>");
$items = $objItem->render();
$objItem->advanceID();
$objFooterRow->setContent($items);
$items = "";
$footeroutput = $objFooterRow->render();
$objFooterRow->advanceID();

$oTable->setContent($headeroutput . $output . $footeroutput);
$sTable = stripslashes($oTable->render());
//Table end

// Set the temp variables
$oTpl->set('s', 'JS_SCRIPT_BEFORE', $sJsBefore);
$oTpl->set('s', 'JS_SCRIPT_AFTER', $sJsAfter);
$oTpl->set('s', 'RIGHTS_CONTENT', $sTable);
$oTpl->set('s', 'EXTERNAL_SCRIPTS', $sJsExternal);
$oTpl->generate('templates/standard/' . $cfg['templates']['rights_inc']);

?>