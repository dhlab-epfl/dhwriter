<?php

/*  -- ELINCHROM.COM PROJECT --
 *
 *  Description :   Suffix page to be included at the end of each generated XHTML page
 *
 *  Created :       2008-02-12, by Cyril Bornet [cyril dot bornet at gmail dot com]
 *  Last modified : 2008-10-16, by Jonathan Link-
 *
 *  Copyright :     Copyright (c) 2011 Cyril Bornet, Cybor SA. All rights reserved.
 *
 */


	print('</section>');  // End of Page Container

	// === Copyright and footer marks ===
	print('<footer>');
		print('<p>');
			print('<a href="http://www.dh2014.org/">DH 2014</a> | <a href="http://dhlab.epfl.ch">EPFL DHLAB</a>');
			if (isset($_SESSION['user_granted'])&&in_array('root', $_SESSION['user_granted'])) {
				print('| <a>');
				$time_end = microtime(true);
				printf('%d ms', round(($time_end - $time_start)*1000));
				print('</a>');
			}
		print('</p>');

	print('</footer>');	// End of footer


	// === Page end ===
	print('</body></html>');

?>