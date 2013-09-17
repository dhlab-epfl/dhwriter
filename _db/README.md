# *_db*  Simple Database Wrapper for PHP
========================================
## Overview

_db is a minimalist, easy to use and safe PHP wrapper. It is aimed to be generic enough to be adapted to any kind of backoffice database engine. Its syntax is meant to be short, simple and expansible.

It is intended to use:

* with simple projects, needing typically only one database
* on projects needing database engine independence

It is not intended to use:

* with complex projects, needing multiple databases or complex SQL queries

Furthermore it provides out-of-the-box:

* queries logging
* security against SQL injections
* graceful error catching in case of database server outage
* automatic, memory-efficient database connection handling

## Syntax philosophy

* Procedural code style
* All method names start with `db_`…
* For querying methods:
	* First argument is always the table name
	* Second argument is an associative array defining the `WHERE` clause (if applicable)
	* Third argument is defines specific parameters for this method, namely :
		* for SELECTs: array with sort parameters
		* for UPDATEs: array with the key/values to be updated
	* Last argument is an optional boolean allowing to disable automatic logging (by default, all queries modifying the database are logged).


## Code samples (MySQL version)

1. **Select** Queries

		$q = db_s('items');
		
    will translate to: `SELECT * FROM items;`

	Now let's specify a WHERE clause:

		$q = db_s('items', array('id' => 4));
		
    will translate to: `SELECT * FROM items WHERE id='4';`

	Sorting:

		$q = db_s('items', array('family' => 'tablets'), array('price' => 'DESC'));
		
    will translate to: `SELECT * FROM items WHERE family='tablets' ORDER BY price DESC;`
    
    This method returns a regular database resource pointer that can be counted or iterated using `mysql_fetch_assoc`-like methods or their provided wrappers :
    	
		$count = db_count($q);
		
		while ($row = db_fetch($q)) {
			print_r($row);
		}

	*Advanced SELECT parameters*:
	
	Operators are to be included in the **field** part of the selector array:
	
		array('%name' => 'Pad')
		
	will translate to: `name LIKE "%Pad"`
	
		array('!name' => 'iPad')
		
	will translate to: `name != "iPad"`

2. **Insert** Queries

		$result = db_i('items', array('name' => 'iPad', 'family' => 'tablets', 'price' => 499));

3. **Update** Queries

		$result = db_u('items', array('id' => 5), array('name' => 'iPad 2'));
		
    will translate to: `DELETE items SET name='iPad 2' WHERE id='5';`
		
4. **Delete** Queries

		$result = db_d('items', array('id' => 5));
		
    will translate to: `DELETE FROM items WHERE id='5';`

5. **Transactions**

		db_begin($title);
	
		db_commit($title);