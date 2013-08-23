<?php
	$grant_access = array('author');
	define('PAGE', 'papers');

	$GLOBALS['js'] = array('tableforms.min.js');
	// Libraries _______________________________________________________________________________________________________________________________________________
	include_once('_pageprefix.php');
	include('_formutils.php');
	include('_loginNow.php');

	// Form actions ============================================================================================================================================
	if (isset($_REQUEST['new_paper'])) {
		$paper_data = array(
						'title' => 'New Abstract',
						'user_id' => $_SESSION['user_id'],
					);
		$defaultPaperId = db_i('papers', $paper_data);
		$user = db_fetch(db_s('users', array('id' => $_SESSION['user_id'])));
		$auth_data = array(
						'first_name' => $user['first_name'],
						'last_name' => $user['last_name'],
						'email' => $user['email'],
						'affiliation' => $user['institution'],
						'paper_id' => $defaultPaperId,
						'disp_order' => 1,
					);
		db_i('authors', $auth_data);
	}

	if (isset($_REQUEST['delete_paper'])) {
		db_d('papers', array('id' => $_REQUEST['delete_paper']));
		db_d('authors', array('paper_id' => $_REQUEST['delete_paper']));
	}

	if (isset($_REQUEST['save_paper'])) {
		$data = array(
				'title' => $_REQUEST['title'],
				'abstract' => $_REQUEST['abstract'],
			);
		$auth_ids = (array)$_REQUEST['author'];
		db_u('papers', array('id' => $_REQUEST['paper_id']), $data);
		foreach ($auth_ids as $auth_id) {
			$auth_data = array(
								'first_name' => $_REQUEST['first_name'.$auth_id],
								'last_name' => $_REQUEST['last_name'.$auth_id],
								'email' => $_REQUEST['email'.$auth_id],
								'affiliation' => $_REQUEST['affiliation'.$auth_id],
								);
			db_u('authors', array('id' => $auth_id), $auth_data);
		}
	}

	// Page content ============================================================================================================================================
	echo '<table cellspacing="0" cellpadding="0" border="0" class="data edit" id="papers">';
	echo '<tr><th>'.locStr('title').'</th><th style="width:120px;">'.locStr('date_created').'</th><th style="width:120px;">'.locStr('date_modified').'</th><th style="width:100px;">'.locStr('actions').'</th></tr>';
	$p_r = db_s('papers', array('user_id' => $_SESSION['user_id']), array('date_updated' => 'DESC'));
	while ($paper = db_fetch($p_r)) {
		$colsCount = 0;
		echo '<tr class="row" id="r'.$paper['id'].'">';
			$colsCount+=print '<td>'.$paper['title'].'</td>';
			$colsCount+=print '<td>'.$paper['date_created'].'</td>';
			$colsCount+=print '<td>'.$paper['date_updated'].'</td>';
			$colsCount+=print '<td>';
							printDeleteInput('delete_paper', '', $paper['id']);
							echo '<img src="/i/metas.png" class="btn edit" title="Edit metadatas" />';
							echo '<a href="/writer.php?id='.$paper['id'].'"><img src="/i/edit.png" class="btn open" title="Edit contents" /></a>';
							echo '&nbsp;&nbsp;<img src="/i/send.png" class="btn send" title="Submit for review" />';
							echo '</td>';
		echo '</tr>';
		echo '<tr class="form"><td colspan="'.$colsCount.'">';
			echo '<header><h2><img src="i/edit.png" />'.$paper['title'].'</h2>';
				echo '<img src="i/close.png" class="close" alt="×" title="Close" />';
			echo '</header>';
			beginForm();
				printHiddenInput('paper_id', $paper['id']);
				echo '<fieldset class="half"><legend>Title Statement</legend>';
					printTextInput('Title', 'title', $paper['title'], 40);
					echo '<br/>';
				echo '</fieldset>';
				echo '<fieldset class="half"><legend>Authors</legend>';
					$authors = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
					while ($author = db_fetch($authors)) {
						echo '<div>';
							if ($author['disp_order']>1) {
								echo '<img src="/i/delete.png" class="btn delete" />';
								printDeleteInput('delete_author', '', $author['id']);
							}
							printHiddenInput('author[]', $author['id']);
							printTextInput('First Name', 'first_name'.$author['id'], $author['first_name'], 40);
							echo '<br/>';
							printTextInput('Last Name', 'last_name'.$author['id'], $author['last_name'], 40);
							echo '<br/>';
							printTextInput('e-Mail', 'email'.$author['id'], $author['email'], 40);
							echo '<br/>';
							printTextInput('Affiliation', 'affiliation'.$author['id'], $author['affiliation'], 40);
						echo '</div>';
						echo '<img src="/i/add.png" class="btn" />';
					}
				echo '</fieldset><br/>';
				printTextArea('Abstract', 'abstract', $paper['abstract'], 100, 10);
				echo '<div class="action_board">';
					printSubmitInput('save_paper', 'Save', true);
				echo '</div>';
			endForm();
		echo '</td></tr>';
	}
	echo '<tr><td colspan="'.$colsCount.'"><a href="?new_paper="><img src="/i/add.png" class="btn" /></a></td></tr>';
	echo '</table>';

	include_once('_pageend.php');
?>