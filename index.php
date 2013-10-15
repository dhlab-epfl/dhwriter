<?php
	date_default_timezone_set('Europe/Zurich');
	include_once('_db/_db.php');
	include_once('_formutils.php');
	include_once('_structure.php');

	if (isset($_REQUEST['signup'])) {		#&&@$_REQUEST['captcha']==''
		$datas = array(	'email' => $_REQUEST['email'],
						'username' => $_REQUEST['email'],
						'first_name' => $_REQUEST['first_name'],
						'last_name' => $_REQUEST['last_name'],
						'institution' => $_REQUEST['institution'],
						'password' => md5(PWD_SALT.md5(trim($_REQUEST['password1']))),
						'status' => 'active',
						'account' => 'visitor,author',
						);
		db_i('users', $datas);
		$_REQUEST['new_paper'] = true;
		$_POST['processLogin'] = true;
		$_POST['username'] = $_REQUEST['email'];
		$_POST['password'] = $_REQUEST['password1'];
	}

	include('_session.php');

	if (isset($_REQUEST['new_paper'])) {
		$maxPaperId = db_fetch(db_s('papers', array(), array('id' => 'DESC')));
		$_REQUEST['id'] = $maxPaperId['id']+1;

		$paper_data = array(
						'id' => $_REQUEST['id'],
						'title' => 'Paper created on '.date('Y-m-d, H:i'),
						'text' => '<section><h2>1. Introduction</h2><h3>1.1. Overview</h3><p>Tell us about your research in a few words.</p><h3>1.2. Methodology</h3><p>More general details go here.</p><h2>2. Getting Started</h2><p></p></section>',
						'user_id' => $_SESSION['user_id'],
						'version' => 1,
					);
		db_i('papers', $paper_data);
		$_SESSION['last_doc'] = db_fetch(db_s('papers', $paper_data));
		$user = db_fetch(db_s('users', array('id' => $_SESSION['user_id'])));
		$auth_data = array(
						'first_name' => $user['first_name'],
						'last_name' => $user['last_name'],
						'email' => $user['email'],
						'affiliation' => $user['institution'],
						'paper_id' => $_REQUEST['id'],
						'user_id' => $_SESSION['user_id'],
						'disp_order' => 1,
					);
		db_i('authors', $auth_data);
		header("Location: /?id=".$_REQUEST['id']);
		die('<a href="/?id='.$_REQUEST['id'].'">Click here if you are not redirected in a few seconds</a>');
	}
	elseif (!isset($_REQUEST['id'])||(int)$_REQUEST['id']==0) {
		if (isset($_SESSION['last_doc'])&&(int)$_SESSION['last_doc']['id']>0) {
			$_REQUEST['id'] = $_SESSION['last_doc']['id'];
		}
		else {
			$_REQUEST['id'] = 1;
		}
		header("Location: /?id=".$_REQUEST['id']);
		die('<a href="/?id='.$_REQUEST['id'].'">Click here if you are not redirected in a few seconds</a>');
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>DHWriter Editor</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/s/dhwriter.css"></link>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<script type="text/javascript" src="/js/aloha-oerpub.min.js"></script>
	<!-- Mathjax -->
	<script type="text/javascript" src="/MathJax/MathJax.js?config=TeX-MML-AM_HTMLorMML-full&amp;delayStartupUntil=configured"
	tal:attributes="src string:${request.application_url}/MathJax/MathJax.js?config=TeX-MML-AM_HTMLorMML-full&amp;delayStartupUntil=configured"> <!-- xhtml --></script>
	<script type="text/x-mathjax-config">MathJax.Hub.Config({
		jax: ["input/MathML", "input/TeX", "input/AsciiMath", "output/NativeMML", "output/HTML-CSS"],
		extensions: ["asciimath2jax.js","tex2jax.js","mml2jax.js","MathMenu.js","MathZoom.js","MathEvents.js","toMathML.js"],
		tex2jax: { inlineMath: [["[TEX_START]","[TEX_END]"], ["\\(", "\\)"]] },
			  // Apparently we can't change the escape sequence for ASCIIMath (MathJax doesn't find it)
			  // asciimath2jax: { inlineMath: [["[ASCIIMATH_START]", "[ASCIIMATH_END]"]], },

			  TeX: {
			  	extensions: ["AMSmath.js","AMSsymbols.js","noErrors.js","noUndefined.js"], noErrors: { disabled: true }
			  },
			  AsciiMath: { noErrors: { disabled: true } }
			});</script>

	<script type="text/javascript" src="/Aloha-Editor/src/lib/aloha.js" tal:attributes="src string:${request.application_url}/aloha/src/lib/aloha.js"
	data-aloha-plugins="common/ui,
    oer/copy,
	oer/toolbar,
	oer/overlay,
	oer/format,
	common/contenthandler,
	common/paste,
	common/block,
	common/list,
	oer/table,
	oer/math,
	oer/mathcheatsheet,
	extra/draganddropfiles,
	common/image,
	oer/assorted,
	oer/title,
	common/undo,
	oer/undobutton,
	oer/genericbutton,
	oer/semanticblock,
	oer/exercise,
	oer/quotation,
	oer/equation,
	oer/note,
	dh/zotero"> <!-- xhtml workaround --></script>
			<!-- Include the fake-jquery to make sure that Aloha works even if the user includes his own global jQuery after aloha.js. -->
<?php
	echo '<script type="text/javascript" tal:content="string:var paper_id = \'${paper_id}\';">var paper_id = '.(int)$_REQUEST['id'].';</script>';
	echo '<script type="text/javascript" tal:content="string:var body_url = \'${body_url}\';">var body_url = "_.php?f=getPaper&id="+paper_id;</script>';
	echo '<script type="text/javascript" tal:content="string:var save_url = \'${request.route_url(\\\'preview_save\\\')}\';">var save_url = "_.php?f=savePaper&id='.(int)$_REQUEST['id'].'";</script>';
?>
			<script type="text/javascript" src="/js/dhwriter.min.js"></script>
			<!--/metal:js_macro-->
		</head>
		<body id="writer">
			<div id="loginFull"><img src="/i/close.png" alt="Close" class="close" /><div id="loginModal" class="loginPanel"><?php include('_loginBox.php'); ?></div></div>
			<div id="ie6-container-wrap">
				<div id="container">
					<!-- ================= -->
					<!--  Toolbar Buttons  -->
					<!-- ================= -->
					<metal:toolbar define-macro="toolbar">
					<div class="toolbar aloha-dialog">
						<div class="btn-toolbar">
							<div class="btn-group">
								<img src="/i/logo-dhwriter-small.png" alt="DHwriter.org" id="logo_small" />
							</div>
<!--
					  <span class="separator"> </span>
					  <div class="btn-group">
						<button class="btn action undo" rel="tooltip" title="Undo"><i class="icon-undo"></i></button>
						<button class="btn action redo" rel="tooltip" title="Redo"><i class="icon-redo"></i></button>
					  </div>
					-->
					<span class="separator"> </span>
					<div class="btn-group headings">
						<button class="btn heading dropdown-toggle" data-toggle="dropdown" rel="tooltip" title="Text Heading" id="headingbutton">
							<span class="currentHeading">&nbsp;</span>
							<span class="caret"></span></button>
							<ul class="dropdown-menu"></ul>
						</div>
						<div class="btn-group">
							<button class="btn action strong" rel="tooltip" title="Bold"><i class="icon-bold"></i></button>
							<button class="btn action emphasis" rel="tooltip" title="Italics"><i class="icon-italic"></i></button>
<!--
						<button class="btn action underline" rel="tooltip" title="Underline"><i class="icon-underline"></i></button>
						<button class="btn action superscript" rel="tooltip" title="Superscript"><i class="icon-superscript"></i></button>
						<button class="btn action subscript" rel="tooltip" title="Subscript"><i class="icon-subscript"></i></button>
					-->
				</div>
				<span class="separator"> </span>
				<div class="btn-group">
<!--					<button class="btn action insertLink" rel="tooltip" title="Insert Link"><i class="icon-link-insert"></i></button>-->
					<button class="btn action zotero" rel="tooltip" title="Citation"><i class="icon-zotero"></i></button>
					<!-- <button class="btn action changeHeading" data-tagname="pre" rel="tooltip" title="Code">Code</button> -->
				</div>
				<span class="separator"> </span>
				<div class="btn-group">
					<button class="btn action unorderedList" rel="tooltip" title="Insert Unordered List"><i class="icon-unordered-list"></i></button>
					<button class="btn action orderedList" rel="tooltip" title="Insert Ordered List"><i class="icon-ordered-list"></i></button>
					<button class="btn action indentList" rel="tooltip" title="Indent list item (move right)"><i class="icon-indent-list"></i></button>
					<button class="btn action outdentList" rel="tooltip" title="Unindent list item (move left)"><i class="icon-outdent-list"></i></button>
				</div>
				<span class="separator"> </span>
				<div class="btn-group">
					<button class="btn action insertImage-oer" rel="tooltip" title="Insert Image"><i class="icon-image-insert"></i></button>
					<!--<button class="btn action insertVideo-oer" rel="tooltip" title="Insert Video"><i class="icon-image-insert"></i></button>-->
					<button class="btn action createTable" rel="tooltip" title="Create Table"><i class="icon-table-insert"></i></button>
					<button class="btn dropdown-toggle" data-toggle="dropdown" rel="tooltip" title="Table Operations">
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li><a href="#" class="action addrowbefore">Add Row Before</a></li>
						<li><a href="#" class="action addrowafter">Add Row After</a></li>
						<li><a href="#" class="action addcolumnbefore">Add Column Before</a></li>
						<li><a href="#" class="action addcolumnafter">Add Column After</a></li>
						<li><a href="#" class="action addheaderrow">Add Header Row</a></li>
						<li><a href="#" class="action deleterow">Delete Row</a></li>
						<li><a href="#" class="action deletecolumn">Delete Column</a></li>
						<li><a href="#" class="action deletetable">Delete Table</a></li>
					</ul>
<!--					<button class="btn action insertMath" rel="tooltip" title="Insert Math"><i class="icon-math-insert"></i></button>-->
				</div>

					<span class="separator"> </span>
					<?php
					if (isset($_SESSION['user_id'])) {
					?>
					<div class="btn-group">
						<button class="btn action save" rel="tooltip" title="Save">Save</button>
					</div>
					<div class="btn-group r">
						<button class="btn action accountMenu" rel="tooltip" title="My Account"><i class="icon-account"></i></button>
						<button class="btn dropdown-toggle" data-toggle="dropdown" rel="tooltip" title="My Account">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="#" class="action my-account">My Account</a></li>
							<li><a href="?logout=<?php echo session_id(); ?>" class="action logout">Logout</a></li>
						</ul>
					</div>
					<div class="btn-group r">
						<button class="btn action documents" rel="tooltip" title="Documents"><i class="icon-documents"></i></button>
						<button class="btn dropdown-toggle" data-toggle="dropdown" rel="tooltip" title="Documents">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
						<?php
							$docs = db_x('SELECT MAX(version) AS version, id, date_updated, title FROM papers WHERE user_id="'.db_escape($_SESSION['user_id']).'" GROUP BY id;');
							while ($doc = db_fetch($docs)) {
								echo '<li><a href="?id='.$doc['id'].'" class="action documents '.($doc['id']==(int)$_REQUEST['id']?'current':'').'" title="Updated on '.$doc['date_updated'].'">'.$doc['title'].' (v.'.$doc['version'].')'.'</a></li>';
							}
						?>
							<li><a href="?new_paper=" class="action documents" title="Create an empty document">[+] New...</a></li>
						</ul>
					</div>
					<?php
						}
						else {
					?>
					<div class="btn-group">
						<button class="btn action nosave" rel="tooltip" disabled="disabled" title="Sign In to Save">Not Saved</button>
					</div>
					<div class="btn-group r">
						<button class="btn action login" rel="tooltip" title="Sign In Now">Sign In</button>
					</div>
					<?php
					}
					?>
				</div>

<!--
				<div id="sidebar">
					<div id="draggableParts">
						<h4>Drag to add a new...</h4>

						<div class="semantic-drag-source">
							<div class="note"><div class="title"></div></div>
							<div class="exercise"><div class="problem"></div></div>
							<blockquote class="quote"></blockquote>
							<div class="equation"></div>
                            <div class="example"><div class="title"></div></div>
						</div>
					</div>
					<div id="citations">
						<div id="citationsList">
							<img src="/i/loading-ajax.gif" alt="…" />
						</div>
					</div>
					<div id="saveload">
						<h4>Export as...</h4>
						<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.tei"><img src="/i/export.png" />TEI</a>
						<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.html"><img src="/i/export.png" />HTML</a>
					</div>
				</div>
-->
			</div><!-- / ".toolbar" -->
		</metal:toolbar>
<?php
		$user_id = isset($_SESSION['user_id'])?$_SESSION['user_id']:'1';
		$paper = db_fetch(db_s('papers', array('id' => (int)$_REQUEST['id'], 'user_id' => $user_id), array('version' => 'DESC')));
		if ($paper==null) {
			echo '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
			$b = new ErrorBox();
			$b->addTitle('403: Not authorized');
			$b->show();
			die();
		}

		echo '<div id="metas">';
			$firstAuthor = db_fetch(db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC')));
			echo '<header><a></a><h1>'.$paper['title'].'</h1><h2>'.$firstAuthor['first_name'].' '.$firstAuthor['last_name'].'</h2></header>';
			beginForm();
				printHiddenInput('paper_id', $paper['id']);
				echo '<fieldset class="half"><legend>Title Statement</legend>';
					printTextInput('Title', 'title', $paper['title'], 40);
					echo '<br/>';
					printSelectInput('Category', 'category', $paper['category'].'/'.$paper['subcategory'], array('/'=>'(choose...)', 'Paper/Short Paper' => 'Short Paper', 'Paper/Long Paper' => 'Long Paper', 'Panel/' => 'Panel', 'Poster/' => 'Poster'));
					echo '<br/>';
					printTextArea('Summary', 'abstract', $paper['abstract'], 100, 10);
					echo '<br/>';
					printTextArea('Keywords', 'keywords', $paper['keywords'], 100, 2);
					echo '<br/>';
					printTextArea('Topics', 'topics', $paper['topics'], 100, 2);
				echo '</fieldset>';
				echo '<fieldset class="half" id="fAuthors"><legend>Authors</legend>';
					$authors = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
					while ($author = db_fetch($authors)) {
						echo '<div id="'.$author['id'].'">';
							echo '<img src="/i/drag.png" class="drag" />';
							echo '<img src="/i/delete.png" class="btn delete'.($author['disp_order']>1?'':' hidden').'" />';
							printHiddenInput('author[]', $author['id']);
							printTextInput('First Name', 'first_name'.$author['id'], $author['first_name'], 35);
							echo '<br/>';
							printTextInput('Last Name', 'last_name'.$author['id'], $author['last_name'], 35);
							echo '<br/>';
							printTextInput('e-Mail', 'email'.$author['id'], $author['email'], 35);
							echo '<br/>';
							printTextInput('Affiliation', 'affiliation'.$author['id'], $author['affiliation'], 35);
						echo '</div>';
					}
					echo '<img src="/i/add.png" class="btn add" />';
				echo '</fieldset><br/>';
			endForm();
		echo '</div>';
?>

		<div id="content">
			<div id="artboard">
				<metal:editor define-macro="editor">
				<div id="statusmessage"></div>
				<div id="editor">
					<div id="canvas" class="aloha-root-editable" ></div>
				</div>
			</metal:editor>
		</div>
	</div>
	<footer>
		<div>
			<h2><a></a>References</h2><div id="citations">
				<div id="citationsList">
					<img src="/i/loading-ajax.gif" alt="…" />
				</div>
			</div>
		</div>
	</footer>
</div>
</div>
<aside><header>Tools</header>
	<h2>Statistics</h2>
	<p id="counters">Words: <span id="wordsCount"></span> / <span id="wordsLimit"></span></p>
	<h2>Export</h2>
	<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.tei" data-ext="tei" data-rev="0" class="export"><img src="/i/export.png" />TEI</a>
	<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.html" data-ext="html" data-rev="0" class="export"><img src="/i/export.png" />HTML</a>
	<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.pdf" data-ext="pdf" data-rev="0" class="export"><img src="/i/export.png" />PDF</a>
	<a href="/export/<?php echo (int)$_REQUEST['id'] ?>.pdf" data-ext="pdf" data-rev="1" id="exportReview"><img src="/i/export.png" />Review PDF</a>
	<h2>Bug report</h2>
	<a href="mailto:support@dhwriter.org?subject=DHwriter%20bug%20report" class="bugreport"><img src="/i/mail.png" />Submit by e-mail</a>
	<a href="https://github.com/cyrilbornet/dhwriter/issues" class="github"><img src="/i/github.png" />Github</a>
</aside>
<div id="wait"><img src="/i/loading.gif" alt="…" /></div>
<script src="/js/retina.min.js"></script>
</body>
</html>
