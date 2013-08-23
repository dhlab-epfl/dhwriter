<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>DHWriter Editor</title>
		<!--metal:css_macro define-macro="css"-->
            <link rel="stylesheet" type="text/css" href="/Aloha-Editor/src/css/aloha.css"
                  tal:attributes="href string:${request.application_url}/aloha/src/css/aloha.css"> <!-- xhtml workaround --></link>
            <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css"
                  tal:attributes="href string:${request.application_url}/bootstrap/docs/assets/css/bootstrap.css" type="text/css"> <!-- xhtml workaround --></link>
			<link rel="stylesheet"  href=""
				  tal:attributes="href string:${request.application_url}/bootstrap/docs/assets/css/bootstrap-responsive.css" type="text/css"><!-- xhtml workaround --></link>
			<link rel="stylesheet" type="text/css" href="/Aloha-Editor/oerpub/css/html5_metacontent.css"></link>
			<link rel="stylesheet" type="text/css" href="/Aloha-Editor/oerpub/css/html5_content_in_oerpub.css"></link>
			<link rel="stylesheet" type="text/css" href="/s/dhwriter.css"></link>
		<!--/metal:css_macro-->
		<!--metal:js_macro define-macro="javascript"-->
			<script src="/Aloha-Editor/oerpub/js/jquery-1.7.1.min.js"></script>
			<script src="/Aloha-Editor/oerpub/js/jquery.center.js"></script>
			<!-- Aloha editor -->
			<script type="text/javascript" src="/Aloha-Editor/src/lib/require.js"></script>
			<script src="/Aloha-Editor/oerpub/js/jquery-ui-1.9.0.custom-aloha.js"></script>
			<!-- Mathjax -->
			<script type="text/javascript"
					src="/MathJax/MathJax.js?config=TeX-MML-AM_HTMLorMML-full&amp;delayStartupUntil=configured"
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

			<script type="text/javascript" src="/js/aloha-settings.js"></script>
			<script type="text/javascript" src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js"
					tal:attributes="src string:${request.application_url}/bootstrap/docs/assets/js/bootstrap.js"> <!-- xhtml workaround --></script>
			<script type="text/javascript" src="/Aloha-Editor/src/lib/vendor/pubsub/js/pubsub.js"></script>
			<script type="text/javascript" src="/Aloha-Editor/src/lib/aloha.js"
					tal:attributes="src string:${request.application_url}/aloha/src/lib/aloha.js"
				data-aloha-plugins="common/ui,
									common/undo,
									oer/toolbar,
									oer/overlay,
									oer/format,
									common/contenthandler,
									common/paste,
									common/block,
									common/list,
									common/dom-to-xhtml,
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
			<!-- Include the fake-jquery to make sure that Aloha works even if
				 the user includes his own global jQuery after aloha.js. -->
			<script type="text/javascript" tal:content="string:var body_url = '${body_url}';">
				<?php
					echo 'var body_url = "_.php?f=getPaper&id='.$_REQUEST['id'].'";';
				?>
			</script>
			<script type="text/javascript" tal:content="string:var save_url = '${request.route_url('preview_save')}';">
				<?php
					echo 'var save_url = "_.php?f=savePaper&id='.$_REQUEST['id'].'";';
				?>
			</script>
			<script src="/js/dhwriter.js"></script>


		<!--/metal:js_macro-->
	</head>
	<body id="writer">
		<div id="ie6-container-wrap">
			<div id="container">
				<!-- ================= -->
				<!--  Toolbar Buttons  -->
				<!-- ================= -->
				<metal:toolbar define-macro="toolbar">
				<div class="toolbar aloha-dialog">
					<div class="btn-toolbar">
					  <div class="btn-group">
						<a href="/"><img src="/i/logo-dhwriter-small.png" alt="DHwriter.org" id="logo_small" /></a>
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
						<button class="btn action insertLink" rel="tooltip" title="Insert Link"><i class="icon-link-insert"></i></button>
						<button class="btn action zotero" rel="tooltip" title="Citation [Powered by Zotero]"><i class="icon-zotero"></i></button>
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
						<button class="btn action insertMath" rel="tooltip" title="Insert Math"><i class="icon-math-insert"></i></button>
					  </div>

<!--
					  <span class="separator"></span>
					  <div class="btn-group">
						<button class="btn btn-text dropdown-toggle" data-toggle="dropdown" rel="tooltip" title="Add a new...">
						  Add a new...
						  <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
						  <li><a href="#" class="action insertNote">Note to Reader</a></li>
						  <li><a href="#" class="action insertExercise">Exercise</a></li>
						  <li><a href="#" class="action insertQuotation">Quotation</a></li>
						  <li><a href="#" class="action insertEquation">Equation</a></li>
						</ul>
					  </div>
-->
					  <span class="separator"> </span>
					  <div class="btn-group">
						<button class="btn action save" rel="tooltip" title="Save">Save</button>
					  </div>
					</div>

					<div id="sidebar">
						<div id="draggableParts">
							<h4>Drag to add a new...</h4>

							<div class="semantic-drag-source">
								<div class="note">
									<div class="title"></div>
								</div>
<!--
								<div class="exercise">
									<div class="problem"></div>
								</div>

								<blockquote class="quote">
								</blockquote>
-->
								<div class="equation">
								</div>
							</div>
						</div>
						<div id="citations">
							<h4>Citations</h4>
							<div id="citationsList">
							</div>
						</div>
						<div id="saveload">
							<h4>Export as…</h4>
							<a href="/export/<?php echo $_REQUEST['id'] ?>.tei"><img src="/i/export-tei.png" /></a>
<!--							<a href="/export/<?php echo $_REQUEST['id'] ?>.html"><img src="/i/export-html.png" /></a>-->
						</div>
					</div>
				</div><!-- / ".toolbar" -->
				</metal:toolbar>

				<div id="content">
					<div id="artboard">
						<metal:editor define-macro="editor">
						<div id="statusmessage"></div>
						<div id="editor">
							<div id="canvas" style="margin-left: 200px;"></div>
						</div>
						</metal:editor>
					</div>
				</div>
				<div id="citations">
				</div>
			</div>
		</div>
		<script src="/js/retina.min.js"></script>
	</body>
</html>
