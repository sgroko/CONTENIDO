<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Path resolving functions
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Includes
 * @version    1.2.1
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   $Id$:
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * prResolvePathViaURLNames: Resolves a path using some fuzzy logic.
 *
 * Warning: If you use this function, try to pass a 'good' path. This
 *          function doesn't guarantee that the matches are logically
 *          best-matches.
 *
 * This function operates on the category aliases. It compares the given path with the urlpaths generated by function
 * prCreateURLNameLocationString() based on category aliases.
 *
 * @param $path string Path to resolve
 * @return integer Closest matching category ID (idcat)
 */
function prResolvePathViaURLNames($path)
{
    global $cfg, $lang, $client;

    $handle = startTiming('prResolvePathViaURLNames', array($path));

    // Initialize variables
    $db = cRegistry::getDb();
    $categories = array();
    $results = array();

    // Pre-process path
    $path = strtolower(str_replace(' ', '', $path));

    // Delete outdated entry in heapcache table, if enabled.
    if ($cfg['pathresolve_heapcache'] == true) {
        $oPathresolveCacheColl = new cApiPathresolveCacheCollection();
        $oPathresolveCache = $oPathresolveCacheColl->fetchLatestByPathAndLanguage($path, $lang);
        if (is_object($oPathresolveCache)) {
            if ($oPathresolveCache->isCacheTimeExpired()) {
                $cacheIdcat = $oPathresolveCache->get('idcat');
                $oPathresolveCacheColl->delete($oPathresolveCache->get('idpathresolvecache'));
                return $cacheIdcat;
            }
        }
    }

    // Fetch all category names, build path strings
    // @todo change the where statement for get all languages
    $sql = "SELECT * FROM ".$cfg["tab"]["cat_tree"]." AS A, ".$cfg["tab"]["cat"]." AS B, ".$cfg["tab"]["cat_lang"]." AS C WHERE A.idcat=B.idcat AND B.idcat=C.idcat AND C.idlang=" . (int) $lang . "
            AND C.visible = 1 AND B.idclient=" . (int) $client . " ORDER BY A.idtree";
    $db->query($sql);

    $catpath = array();
    while ($db->next_record()) {
        $cat_str = '';
        prCreateURLNameLocationString($db->f('idcat'), '/', $cat_str, false, '', 0, 0, true, true);

        // Store path
        $catpath[$db->f('idcat')] = $cat_str;
        $catnames[$db->f('idcat')] = $db->f('name');
        $catlevels[$db->f('idcat')] = $db->f('level');
    }

    // Compare strings using the similar_text algorythm
    $percent = 0;
    foreach ($catpath as $key => $value) {
        $value = strtolower(str_replace(' ', '', $value));

        similar_text($value, $path, $percent);

        $firstpath = strpos($value, '/');

        if ($firstpath !== 0) {
            $xpath = substr($value, $firstpath);
            $ypath = substr($path, 0, strlen($path) - 1);
            if ($xpath == $ypath) {
                $results[$key] = 100;
            } else {
                $results[$key] = $percent;
            }
        } else {
            $results[$key] = $percent;
        }
    }

    arsort($results, SORT_NUMERIC);
    reset($results);

    endAndLogTiming($handle);

    if ($cfg['pathresolve_heapcache'] == true) {
        $oPathresolveCacheColl = new cApiPathresolveCacheCollection();
        $oPathresolveCache = $oPathresolveCacheColl->create($path, key($results), $lang, time());
    }

    return (int) key($results);
}

/**
 * prResolvePathViaCategoryNames: Resolves a path using some fuzzy logic.
 *
 * Warning: If you use this function, try to pass a 'good' path. This
 *          function doesn't guarantee that the matches are logically
 *          best-matches.
 *
 * This function operates on the actual category names.
 *
 * @param $path string Path to resolve
 * @return integer Closest matching category ID (idcat)
 */
function prResolvePathViaCategoryNames($path, &$iLangCheck)
{
    global $cfg, $lang, $client;

    $handle = startTiming('prResolvePathViaCategoryNames', array($path));

    // Initialize variables
    $db = cRegistry::getDb();
    $categories = array();
    $results = array();
    $iLangCheckOrg = $iLangCheck;

    // To take only path body
    if (preg_match('/^\/(.*)\/$/', $path, $aResults)) {
        $aResult = explode('/', $aResults[1]);
    } elseif (preg_match('/^\/(.*)$/', $path, $aResults)) {
        $aResult = explode('/', $aResults[1]);
    } else {
        $aResults[1] = $path;
    }

    $aResults[1] = strtolower(preg_replace('/-/', ' ', $aResults[1]));

    // Init to Compare, save path in array
    $aPathsToCompare = explode('/', $aResults[1]);
    $iCountPath = count($aPathsToCompare);

    // init lang id
    $iLangCheck = 0;

    // Pre-process path
    $path = strtolower(str_replace(' ', '', $path));

    // Fetch all category names, build path strings
    // @todo change the where statement for get all languages
    $sql = "SELECT * FROM ".$cfg["tab"]["cat_tree"]." AS A, ".$cfg["tab"]["cat"]." AS B, ".$cfg["tab"]["cat_lang"]." AS C WHERE A.idcat=B.idcat AND B.idcat=C.idcat
            AND C.visible = 1 AND B.idclient= " . (int) $client . " ORDER BY A.idtree";
    $db->query($sql);

    $catpath = array();
    $arrLangMatches = array();

    while ($db->next_record()) {
        $cat_str = '';
        $aTemp = '';
        $iFor = 0;
        $bLang = false;

        // $level is changeless 0!!!
        conCreateLocationString($db->f('idcat'), '/', $cat_str, false, '', 0, $db->f('idlang'));
        // Store path
        $catpath[$db->f('idcat')] =  $cat_str;
        $catnames[$db->f('idcat')] = $db->f('name');
        $catlevels[$db->f('idcat')] = $db->f('level');

        // Init variables for take a language id
        $aTemp =  strtolower($cat_str);
        $aDBToCompare =  explode('/', $aTemp);
        $iCountDB = count($aDBToCompare);
        $iCountDBFor = $iCountDB - 1;
        // take min. count of two arrays
        ($iCountDB > $iCountPath) ? $iFor = $iCountPath : $iFor = $iCountDB;
        $iCountM = $iFor-1;

        for ($i=0; $i<$iFor; $i++) {
            if ($aPathsToCompare[$iCountM] == $aDBToCompare[$iCountDBFor]) {
                $bLang = true;
            } else {
                $bLang = false;
            }
            $iCountM--;
            $iCountDBFor--;
            // compare, only if current element is lastone and we are in true path
            if ($i == $iFor-1 && $bLang) {
                $iLangCheck = $db->f('idlang');
                $arrLangMatches[] = $iLangCheck;
            }
        }
    }

    // Suppress wrongly language change if url name can be found in current language
    if ($iLangCheckOrg == 0) {
        if (in_array($lang, $arrLangMatches)) {
            $iLangCheck = $lang;
        }
    }

    // Compare strings using the similar_text algorythm
    $percent = 0;
    foreach ($catpath as $key => $value) {
        $value = strtolower(str_replace(' ', '', $value));
        similar_text($value, $path, $percent);
        $results[$key] = $percent;
    }

    foreach ($catnames as $key => $value) {
        $value = strtolower(str_replace(' ', '', $value));
        similar_text($value, $path, $percent);

        // Apply weight
        $percent = $percent * $catlevels[$key];

        if ($results[$key] > $percent) {
            $results[$key] = $percent;
        }
    }

    arsort($results, SORT_NUMERIC);
    reset($results);

    endAndLogTiming($handle);
    return (int) key($results);
}

/**
 * Recursive function to create an URL name location string
 *
 * @param int $idcat ID of the starting category
 * @param string $seperator Seperation string
 * @param string $cat_str Category location string (by reference)
 * @param boolean $makeLink create location string with links
 * @param string $linkClass stylesheet class for the links
 * @param integer first navigation level location string should be printed out (first level = 0!!)
 *
 * @return string location string
 *
 * @author Jan Lengowski <jan.lengowski@4fb.de>
 * @author Marco Jahn <marco.jahn@4fb.de>
 *
 * @copyright four for business AG <www.4fb.de>
 */
function prCreateURLNameLocationString($idcat, $seperator, & $cat_str, $makeLink = false, $linkClass = '',
    $firstTreeElementToUse = 0, $uselang = 0, $final = true, $usecache = false) {
    global $cfg, $client, $cfgClient, $lang, $sess, $_URLlocationStringCache;

    if ($final == true) {
        $cat_str = '';
    }

    if ($idcat == 0) {
        $cat_str = 'Lost and Found';
        return;
    }

    if ($uselang == 0) {
        $uselang = $lang;
    }

    if ($final == true && $usecache == true) {
        if (!is_array($_URLlocationStringCache)) {
            $_URLlocationStringCache = prGetCacheFileContent($client, $uselang);
        }

        if (array_key_exists($idcat, $_URLlocationStringCache)) {
            if ($_URLlocationStringCache[$idcat]['expires'] > time()) {
                $cat_str = $_URLlocationStringCache[$idcat]['name'];
                return;
            }
        }
    }

    $db = cRegistry::getDb();

    $sql = "SELECT
                a.urlname AS urlname,
                a.name    AS name,
                a.idcat AS idcat,
                b.parentid AS parentid,
                c.level as level,
                d.idtpl as idtpl
            FROM
                ".$cfg["tab"]["cat_lang"]." AS a,
                ".$cfg["tab"]["cat"]." AS b,
                ".$cfg["tab"]["cat_tree"]." AS c,
                ".$cfg["tab"]["tpl_conf"]." AS d
            WHERE
                a.idlang    = " . (int) $uselang . " AND
                b.idclient  = " . (int) $client . " AND
                b.idcat     = " . (int) $idcat . " AND
                a.idcat     = b.idcat AND
                c.idcat 	= b.idcat AND
                a.idtplcfg 	= d.idtplcfg";

    $db->query($sql);
    $db->next_record();

    if ($db->f('level') >= $firstTreeElementToUse) {
        $name = $db->f('urlname');

        if (trim($name) == '') {
            $name = $db->f('name');
        }

        $parentid = $db->f('parentid');
		$idtpl = $db->f('idtpl');
        //create link
        if ($makeLink == true) {
            $linkUrl = $sess->url("front_content.php?idcat=$idcat&idtpl=$idtpl");
            $name = '<a href="'.$linkUrl.'" class="'.$linkClass.'">'.$name.'</a>';
        }

        $tmp_cat_str = $name.$seperator.$cat_str;
        $cat_str = $tmp_cat_str;
    }

    if ($parentid != 0) {
        prCreateURLNameLocationString($parentid, $seperator, $cat_str, $makeLink, $linkClass, $firstTreeElementToUse, $uselang, false, $usecache);
    } else {
        $sep_length = strlen($seperator);
        $str_length = strlen($cat_str);
        $tmp_length = $str_length - $sep_length;
        $cat_str = substr($cat_str, 0, $tmp_length);
    }

    if ($final == true && $usecache == true) {
        $_URLlocationStringCache[$idcat]['name'] = $cat_str;
        $_URLlocationStringCache[$idcat]['expires'] = time() + 3600;

        prWriteCacheFileContent($_URLlocationStringCache, $client, $uselang);
    }
}


/**
 * Writes path location string cache data file.
 * @global array $cfgClient
 * @param array $data
 * @param int $client
 * @param int $lang
 * @return bool
 */
function prWriteCacheFileContent($data, $client, $lang)
{
    global $cfgClient;

    $path = $cfgClient[$client]['cache_path'];
    $filename = "locationstring-url-cache-$lang.txt";

    $res = false;
    if (is_writable($path)) {
        $res = cFileHandler::write($path . $filename, serialize($data));
    }

    return ($res) ? true : false;
}

/**
 * Get path location string cache data file content.
 * @global array $cfgClient
 * @param int $client
 * @param int $lang
 * @return array $data
 */
function prGetCacheFileContent($client, $lang)
{
    global $cfgClient;

    $path = $cfgClient[$client]['cache_path'];
    $filename = "locationstring-url-cache-$lang.txt";

    if (cFileHandler::exists($path . $filename)) {
        $data = unserialize(cFileHandler::read($path . $filename));
    } else {
        $data = array();
    }

    return (is_array($data)) ? $data : array();
}


/**
 * Deletes path location string cache data file.
 * @global array $cfgClient
 * @param int $client
 * @param int $lang
 * @return bool
 */
function prDeleteCacheFileContent($client, $lang)
{
    global $cfgClient;

    $path = $cfgClient[$client]['cache_path'];
    $filename = "locationstring-url-cache-$lang.txt";

    $res = false;
    if (is_writable($path . $filename)) {
        $res = @unlink($path . $filename);
    }

    return ($res) ? true : false;
}

?>