<?php
/* Database Tools
 * A collection of tools for making database queries easier in PHP scripts
 * Version 2.b for PostgreSQL
 * Copyright (c) 2006-2013 Cyril Bornet, all rights reserved
 * ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
 * Historique des versions :
 *  05/03/2006 | 1.0 | Version initiale, comprenant méthodes openTable(), db_s(), db_g(), db_e(), db_i(), db_u() et db_d().
 *  25/05/2007 | 1.2 | Renommage des fonctions ci-dessus vers db_o(), db_s(), db_g(), db_e(), db_i(), db_u(), db_d(), pour compatibilité avec version publique.
 *  03/08/2007 | 1.3 | Correction affectant la fonction db_o() : suppression du paramètre $table (inutile) / ajout de la fonction ouverte db_x().
 *  03/08/2007 | 1.4 | Système de journalisation pour toutes les méthodes altérant des données.
 *  12/11/2010 | 1.5 | Potential security flaws fixes.
 *  04/01/2011 | 1.6 | Unique connection in global variable
 *	09/02/2012 | 2.0 | Updated sort arguments calls, added transactions support
 *	23/07/2012 | 2.b | PostgreSQL version
 *	27/05/2013 | 2.1 | Return values in case of errors/successes fix
 *	13/06/2013 | 2.2 | Advanced selector implementation (credits to Vincent Barbay), bug fix in db_d()
 */

// === VARIABLES de connexion pour ce site =====================================================================================================================

$GLOBALS['DBHost'] = 'localhost';	// Host serveur PostgreSQL
$GLOBALS['DBPort'] = '';			// Port serveur PostgreSQL
$GLOBALS['DBName'] = '';			// Nom de la base de données
$GLOBALS['DBUser'] = '';			// Utilisateur Postgres
$GLOBALS['DBPass'] = '';			// Mot de passe utilisateur Postgres

// === Ouvre une CONNEXION globale au serveur de DB ============================================================================================================
$GLOBALS['db_link'] = false;
function db_o() {
	if ($GLOBALS['db_link']===false) {
		$GLOBALS['db_link'] = pg_connect('host='.$GLOBALS['DBHost'].' port='.$GLOBALS['DBPort'].' dbname='.$GLOBALS['DBName'].' user='.$GLOBALS['DBUser'].' password='.$GLOBALS['DBPass']);
		pg_set_client_encoding($GLOBALS['db_link'], 'UTF8');
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
    $result = pg_query($link, $sql);
 	if ( $result == false) {
 		dieWithError(pg_last_error($link), 'error', $sql);	#mysql_error($link)
 	}
    return $result;
}

function db_w($refs) {
	// Filter parameters ____________________________________________________________________
	$link = db_o();																	// Ouvre une connexion
	if (count($refs)>0) {
		$where = array();
		foreach ($refs as $key => $value) {
			if(strstr($key, "%") !== false){
				$proper_key = str_replace('%','',$proper_key);
				$str_val = ($value===null)?'null':'"'.pg_escape_string(str_replace($proper_key,$value,$key), $link).'"';
				$where[] = $proper_key.' ILIKE '.$str_val;
			}elseif(strstr($key, "!")){
				$proper_key = str_replace('!','',$proper_key);
				$str_val = ($value===null)?'null':'"'.pg_escape_string($proper_key, $link).'"';
				$where[] = $proper_key.' != '.$str_val;

			}else{
				$str_val = ($value===null)?'null':'"'.pg_escape_string($value, $link).'"';
				$where[] = $key.' = '.$str_val;
			}
		}

		return ' WHERE ('.implode(' AND ', $where).')';
	}
	else return '';
}




// === INSERE les données $datas dans la table $table de la base de donnés de ce site ==========================================================================
function db_i($table, $datas, $do_log=false) {
	$link = db_o();																	// Ouvre une connexion
	$keys = array();
	$values = array();
	foreach ($datas as $key => $value) {											// \
		$keys[] = $key;																//  |
		$values[] = '\''.pg_escape_string($link, $value).'\'';				//  Parcourt les données en paramètres pour les réarranger conformément à la requête SQL
	}																				// /
	$sql = 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).');';				// Requête SQL

    $result = pg_query($link, $sql);												//

 	if ( $result == false) {
 		dieWithError(pg_last_error($link), 'error', $sql);	#mysql_error($link)
 	}
 	else return true;
 }

// === MODIFIE la ligne avec id=$id dans la table $table de la base de donnés de ce site =======================================================================
function db_u($table, $refs, $datas, $do_log=true) {
	$link = db_o();																		// Ouvre une connexion

	$test = db_s($table, $refs);										// Pour éviter le risque d'écraser des données, on fait un test de cohérence avant d'entamer les modifs.
	if (db_count($test) > 1) {													//
		dieWithError('', '', 'db_u() cannot be used on tables with non-unique IDs.');	// Si plusieurs lignes ont le même ID, on arrête tout ici par précaution
	}

	$toChange = array();																	// \
	foreach ($datas as $key => $value) {											//  |
		$sql_value = db_escape($value);						//  |
		$toChange[] = $key."='".$sql_value."'";								//  |
    }																				// /

	$sql = 'UPDATE '.$table.' SET '.implode(',',$toChange).db_w($refs);				// Requête SQL

    $result = pg_query($link, $sql);												//

 	if ( $result == false) {
 		dieWithError(pg_last_error($link), 'error', $sql);	#mysql_error($link)
 	}
 	else return true;
 }

// === SUPPRIME la ligne avec id=$id dans la table $table de la base de donnés de ce site ======================================================================
function db_d($table, $refs, $do_log=false) {
	$link = db_o();																	// Ouvre une connexion
	// Pour éviter le risque d'écraser des données, on fait un test de cohérence avant d'entamer les modifs.
	$test = db_s($table, $refs);
	if (db_count($test) > 1) {
		dieWithError('', '', 'db_d() cannot be used on tables with non-unique IDs.');	// Si plusieurs lignes ont le même ID, on arrête tout ici par précaution
	}
	elseif (db_count($test) > 0) {
		$sql = 'DELETE FROM '.$table.db_w($refs);

		$result = pg_query($link, $sql);

 		if ( $result == false) {
 			dieWithError(pg_last_error($link), 'error', $sql);	#mysql_error($link)
 		}
 		else return true;
	}
}

// === EXECUTE la requête passée en paramètre ==================================================================================================================
function db_x($request, $do_log=false) {
	$link = db_o();
	$result = pg_query($link, $request);

 	if ( $result == false) {
 		dieWithError(pg_last_error($link), 'error', $sql);	#mysql_error($link)
 	}
	else {
		return $result;
	}
}

// === TRANSACTIONS ============================================================================================================================================

function db_begin() {
	die('Not implemented for PostgreSQL yet.');
}

function db_commit() {
	die('Not implemented for PostgreSQL yet.');
}

// === TOOLS & Helpers =========================================================================================================================================

function db_escape($string) {
	$link = db_o();
	return pg_escape_string($link, $string);
}

function db_fetch($src) {
	return pg_fetch_assoc($src);
}

function db_seek($src, $offset = 0) {
	return pg_result_seek($src,$offset);
}

function db_count($src) {
	return pg_num_rows($src);
}

function dieWithError($code, $msg, $stmt) {
	die('<br/><br/><b>PostgreSQL error '.$code.': '.$msg.'</b><br/>When executing : <pre style="background:#CCC;padding:5px;">'.$stmt.'</pre>');
}
