<?php
/* _db
 * Database wrapper for PHP
 * Version 2.2 - MySQL
 * Copyright (c) 2006-2013 Cyril Bornet, all rights reserved
 * ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
 * Historique des versions :
 *  05/03/2006 | 1.0 | Version initiale, comprenant méthodes openTable(), db_s(), db_g(), db_e(), db_i(), db_u() et db_d().
 *  25/05/2007 | 1.2 | Renommage des fonctions ci-dessus vers db_o(), db_s(), db_g(), db_e(), db_i(), db_u(), db_d(), pour compatibilité avec version publique.
 *  03/08/2007 | 1.3 | Correction affectant la fonction db_o() : suppression du paramètre $table (inutile) / ajout de la fonction ouverte db_x().
 *  03/08/2007 | 1.4 | Système de journalisation pour toutes les méthodes altérant des données.
 *  12/11/2010 | 1.5 | Potential security flaws fixes.
 *  04/01/2011 | 1.6 | Unique connection in global variable
 *  09/02/2012 | 2.0 | Updated sort arguments calls, added transactions support
 *	31/01/2013 | 2.1 | UPDATE/DELETE by References
 *	07/06/2013 | 2.2 | Operators in WHERE clauses (VB)
 */

// === VARIABLES de connexion pour ce site =====================================================================================================================

$GLOBALS['DBHost'] = 'localhost';
$GLOBALS['DBUser'] = 'dhwriter';
$GLOBALS['DBPass'] = 'gcd2386*';
$GLOBALS['DBName'] = 'dhwriter';

// === Ouvre une CONNEXION globale au serveur de DB ============================================================================================================
$GLOBALS['db_link'] = false;
function db_o() {
	// MySQL 4.1 et supérieur
	if ($GLOBALS['db_link']===false) {
		$GLOBALS['db_link'] = mysql_connect($GLOBALS['DBHost'], $GLOBALS['DBUser'], $GLOBALS['DBPass']);					//  Connexion à MySQL
		if (!$GLOBALS['db_link']) {
			if ((@include 'inc_down.html')===false) {
				print('<h1>Down for maintenance</h1><p>This website is currently down for maintenance. We are currently working on it, so please come back in a few hours...</p><hr/>'.$_SERVER['HTTP_HOST'].'<i>');
			}
			die();																											//  En cas d'erreur de connexion, on arrête tout !
		}
		mysql_select_db($GLOBALS['DBName'], $GLOBALS['db_link']);															// Sélectionne la base de données
		mysql_set_charset('UTF8');
	}
	return $GLOBALS['db_link'];
}

// === Effectue une RECHERCHE dans la base de données du site ==================================================================================================
function db_s($table, $refs=array(), $sortParams=array()) {
	$link = db_o();																											// Ouvre une connexion
	$sql = 'SELECT * FROM '.$table.db_w($refs);
	// Sort parameters ______________________________________________________________________
	if (count($sortParams)>0) {
		$sort = array();
		foreach ($sortParams as $key => $dir) {
			$sort[] = $key.' '.$dir;
		}
		$sql.=' ORDER BY '.implode(', ', $sort);
	}
	//print_r("dbs");
	//print_r($sql);
    $result = mysql_query($sql, $link);
 	if (mysql_errno($link) != 0) {
 		dieWithError(mysql_errno($link), mysql_error($link), $sql);
 	}
 	//print_r($result);
    return $result;
}

function db_w($refs) {
	// Filter parameters ____________________________________________________________________
	$link = db_o();																	// Ouvre une connexion
	if (count($refs)>0) {
		$where = array();
		foreach ($refs as $key => $value) {
			if (strstr($key, '%') !== false) {
				$proper_key = str_replace('%','',$key);
				$str_val = ($value===null)?'null':'"'.db_escape(str_replace($proper_key,$value,$key), $link).'"';
				$where[] = 'LOWER('.$proper_key.') LIKE '.$str_val;
			}elseif(strstr($key, "!")){
				$proper_key = str_replace('!','',$key);
				$str_val = ($value===null)?'null':'"'.db_escape($proper_key, $link).'"';
				$where[] = $proper_key.' != '.$str_val;
			}else{
				$str_val = ($value===null)?'null':'"'.db_escape($value, $link).'"';
				$where[] = $key.' = '.$str_val;
			}
		}
		return ' WHERE ('.implode(' AND ', $where).')';
	}
	else return '';
}

// === INSERE les données $datas dans la table $table de la base de donnés de ce site ==========================================================================
function db_i($table, $datas, $do_log=true) {
	$link = db_o();																	// Ouvre une connexion
	$keys = array();
	$values = array();
	foreach ($datas as $key => $value) {											// \
		$keys[] = $key;																//  |
		$values[] = '"'.mysql_real_escape_string($value, $link).'"';				//  Parcourt les données en paramètres pour les réarranger conformément à la requête SQL
	}																				// /
	$sql = 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).');';				// Requête SQL

    if ($do_log) { db_log($sql); }
    $result = mysql_query($sql, $link);												//

 	if (mysql_errno($link) == 0) { return mysql_insert_id($link); } else { dieWithError(mysql_errno($link), mysql_error($link), $sql); return false; }	// Témoin d'enregistrement (true = OK)
}

// === REMPLACE la ligne avec sélecteur spécifié ===============================================================================================================
function db_r($table, $datas, $do_log=true) {
	$link = db_o();																	// Ouvre une connexion
	$keys = array();
	$values = array();
	foreach ($datas as $key => $value) {											// \
		$keys[] = $key;																//  |
		$values[] = '"'.mysql_real_escape_string($value, $link).'"';				//  Parcourt les données en paramètres pour les réarranger conformément à la requête SQL
	}																				// /
	$sql = 'REPLACE INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).');';				// Requête SQL

    if ($do_log) { db_log($sql); }
    $result = mysql_query($sql, $link);												//

 	if (mysql_errno($link) == 0) { return mysql_insert_id($link); } else { dieWithError(mysql_errno($link), mysql_error($link), $sql); return false; }	// Témoin d'enregistrement (true = OK)
}


// === MODIFIE la ligne avec id=$id dans la table $table de la base de donnés de ce site =======================================================================
function db_u($table, $refs, $datas, $do_log=true) {
	$link = db_o();																		// Ouvre une connexion

	$test = db_s($table, $refs);											// Pour éviter le risque d'écraser des données, on fait un test de cohérence avant d'entamer les modifs.
	if (db_count($test) > 1) {													//
		dieWithError('', '', 'db_u() cannot be used on tables with non-unique IDs.');	// Si plusieurs lignes ont le même ID, on arrête tout ici par précaution
	}
	$toChange = array();															// \
	foreach ($datas as $key => $value) {											//  |
		$str_val = ($value===null)?'null':'"'.mysql_real_escape_string($value, $link).'"';						//  |
		$toChange[] = $key.'='.$str_val;											//  |
	}																				// /
	$sql = 'UPDATE '.$table.' SET '.implode(',',$toChange).db_w($refs);				// Requête SQL

	if ($do_log) { db_log($sql); }
	$result = mysql_query($sql, $link);												//

 	if (mysql_errno($link) == 0) { return mysql_affected_rows($link); } else { dieWithError(mysql_errno($link), mysql_error($link), $sql); }	// Témoin d'enregistrement (true = OK)
}

// === SUPPRIME la ligne avec id=$id dans la table $table de la base de donnés de ce site ======================================================================
function db_d($table, $refs, $do_log=true) {
	$link = db_o();																	// Ouvre une connexion

	$sql = 'DELETE FROM '.$table.db_w($refs);

	if ($do_log) { db_log($sql); }
	$result = mysql_query($sql, $link);

	if (mysql_errno($link) == 0) { return mysql_affected_rows($link); } else { dieWithError(mysql_errno($link), mysql_error($link), $sql); }	// Témoin d'enregistrement (true = OK)
}

// === EXECUTE la requête passée en paramètre ==================================================================================================================
function db_x($request, $do_log=true, $qParams=array()) {
	$link = db_o();
	$result = mysql_query($request, $link);
	if ($do_log && substr($request, 0, 7) != 'SELECT ' && mysql_errno($link)>0) { db_log($request); }

	if (mysql_errno($link)) {
		dieWithError(mysql_errno($link), mysql_error($link), $request);
	}
	else {
		return $result;
	}
}

// === TRANSACTIONS ============================================================================================================================================

function db_begin($title='default') {
	$link = db_o();
	$sql = 'BEGIN TRANSACTION '.$title.'';
	$result = mysql_query($sql, $link);
}

function db_commit($title='default') {
	$link = db_o();
	$sql = 'COMMIT TRANSACTION '.$title.'';
	$result = mysql_query($sql, $link);
}

// === TOOLS & Helpers =========================================================================================================================================

function db_escape($string) {
	$link = db_o();
	return mysql_real_escape_string($string, $link);
}

function db_fetch($src) {
	return mysql_fetch_assoc($src);
}

function db_seek($src, $offset = 0) {
	return mysql_data_seek($src, $offset);
}

function db_count($src) {
	return mysql_num_rows($src);
}

function dieWithError($code, $msg, $stmt) {
	echo('<br/><br/><b>MySQL error '.$code.': '.$msg.'</b><br/>When executing : <pre style="background:#CCC;padding:5px;">'.$stmt.'</pre>');
}

// === JOURNALISE la requête en paramètre, selon variables courantes ===========================================================================================

function db_log($request) {
	$link = db_o();
	$result = mysql_query('INSERT INTO db_log (user_id, date, query) VALUES ("'.@$_SESSION['user_id'].'", CURRENT_TIMESTAMP, "'.db_escape($request, $link).'");', $link);
}



?>
