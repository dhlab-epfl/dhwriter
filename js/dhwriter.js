var kAutoSaveDelay = 30000;		// in ms

Aloha.ready(function(){
	Aloha.require(['PubSub', 'genericbutton/genericbutton-plugin'], function(PubSub, GenericButton) {
		var metasModified = false;
		function updateWordStats() {
			var text = Aloha.getEditableById('canvas').getContents();
			var w = text.trim().replace(/<([^>]*)>/gi, ' ').replace(/\s+/gi, ' ').split(' ').length;
			var citationsLength = $('#canvas cite>span').text().trim().replace(/<([^>]*)>/gi, ' ').replace(/\s+/gi, ' ').split(' ').length;
			$('#wordsCount').html(w - citationsLength);
			$('#wordsLimit').html('5000');
		}
		function paperWasModified() {
			var editor = Aloha.getEditableById('canvas');
			return $('#writer #container .toolbar .btn.nosave').length==0&&(editor.isModified()||metasModified);
		}
		function setPaperUnmodified() {
			$('#exportReview').stop(false,true).fadeIn();
			var editor = Aloha.getEditableById('canvas');
			editor.setUnmodified();
			metasModified = false;
		}
		function bindMetasForm() {
			$('#metas>form input, #metas>form textarea, #metas>form select').unbind('input').bind('input change', function(){ metasModified = true; });
			$('#metas>form input[name=title]').unbind('input').bind('input', function(){
				$('#metas>header>h1').html($(this).val());
				$('#currentDocMenuItem>b').html($(this).val());
			});
			$('#fAuthors>div').first().children('input[type=text]').slice(0,2).unbind('input').bind('input', function(){
				$('#metas>header>h2').html($(this).parent().children('input[type=text]:eq(0)').val()+' '+$(this).parent().children('input[type=text]:eq(1)').val());
			});
			$('#fAuthors>div').each(function(){
				var subForm = $(this);
				$(this).find('.btn.delete').unbind('click touchdown').bind('click touchdown', function(){
					$.get('_.php', {'f':'deleteAuthor', 'id':subForm.find('input[type="hidden"]').val()});
					subForm.slideUp(function(){$(this).remove();});
				});
			});
			$('#fAuthors').sortable({handle:'.drag', containment:'parent', scroll:true, update:function(e,ui){
				$.get('_.php?'+$('#fAuthors').sortable('serialize'), {'f':'sort', 't':'authors'});
			}});
		}
		function htmlEscape(str) {			// http://stackoverflow.com/questions/1219860/html-encoding-in-javascript-jquery
			return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}
/*					agree: "required"*/
/*					agree: "Please accept our policy"*/
		// ______________________________________________________________________________________________
		GenericButton.getButtons()['save'].enable(false);							// Save button is disabled until something was changed
		$('.btn.save').html('Saved');
		bindMetasForm();
		// ______________________________________________________________________________________________
		$('#metas>form #fAuthors .btn.add').bind('click touchdown', function(){
			var lastAuthorSubForm = $('#metas>form #fAuthors>div').last();
			var newForm = lastAuthorSubForm.clone();
			newForm.css({'display':'none'}).find('.btn.delete').removeClass('hidden');
			lastAuthorSubForm.after(newForm);
			$.get('_.php', {'f':'addAuthor', 'paper_id':paper_id}, function(newId){
				if (parseInt(newId,10)>0) {
					var oldId = newForm.find('input[type="hidden"]').val();
					newForm.find('input[name="first_name'+oldId+'"]').attr('name', 'first_name'+newId);
					newForm.find('input[name="last_name'+oldId+'"]').attr('name', 'last_name'+newId);
					newForm.find('input[name="email'+oldId+'"]').attr('name', 'email'+newId);
					newForm.find('input[name="affiliation'+oldId+'"]').attr('name', 'affiliation'+newId);
					newForm.find('input').each(function(){ $(this).val(''); });
					newForm.find('input[type="hidden"]').val(newId);
					newForm.slideDown();
				}
			});
			bindMetasForm();
		});
		$('.btn.login').bind('click touchdown', function(){
			$('#loginFull').fadeIn();
		});
		$('#loginFull>.close').bind('click touchdown', function(){
			$('#loginFull').fadeOut();
		});
		$('aside a.export').bind('click touchdown', function(e){
			e.preventDefault();
			var url = 'exporter.php?ext='+$(this).data('ext')+'&rev='+$(this).data('rev');
			var form = $('<form action="'+url+'" method="post">'+'<input type="hidden" name="src" value="'+htmlEscape(Aloha.getEditableById('canvas').getContents())+'" /></form>');
			form.append($('#metas form>*').clone());
			console.log(form);
			form.submit();
		});
		// ______________________________________________________________________________________________
		function savePreview(callback_function){
			var editor = Aloha.getEditableById('canvas');
			if (paperWasModified()) {
				setPaperUnmodified(); // setUnmodified, to avoid another concurrent save from firing while this one is still ongoing.
				/*
				var $html = $('<html />');
				$html.attr('xmlns', 'http://www.w3.org/1999/xhtml');
				$html.attr('xmlns:c', 'http://cnx.rice.edu/cnxml');
				$html.attr('xmlns:md', 'http://cnx.rice.edu/mdml/0.4');
				$html.attr('xmlns:qml', 'http://cnx.rice.edu/qml/1.0');
				$html.attr('xmlns:mod', 'http://cnx.rice.edu/#moduleIds' );
				$html.attr('xmlns:bib', 'http://bibtexml.sf.net/');
				$html.attr('xmlns:data', 'http://dev.w3.org/html5/spec/#custom');

				// Build simple header with title.
				var $head = $('<head/>');
				$('<title/>').text(editor.obj.find('>div.title').text()).appendTo($head);
				$html.append($head);
				*/
				var $body = $('<section/>');
				$body.append(editor.getContents());

				var html;
				if (typeof window.XMLSerializer != "undefined") {
					html = (new XMLSerializer()).serializeToString($body.get(0));					// “This is going to break in IE. Which is fine, because we don't support that right now. Hint to poor person  who has to develop that: ActiveXObject  Microsoft.XMLDOM”
				} else if (typeof xmlNode.xml != "undefined") {
				    html = $body.get(0).xml;														// http://stackoverflow.com/questions/4916327/javascript-replacement-for-xmlserializer-serializetostring
				}
				if (save_url !== null) {
					var data = $('#metas>form').serializeArray();
					data.push({name: 'html', value: html});
					$.post(save_url, data, function(data, statustext){
						if(data.error){
							$('#statusmessage').data('message')(data.error, 'error', 0);
						} else {
							$('#statusmessage').data('message')('Saved');
							GenericButton.getButtons()["save"].enable(false);
							$('.btn.save').html('Saved');
							if (callback_function) {
								callback_function();
							}
						}
						PubSub.pub('swordpushweb.saved');
					});
				} else {
					$('#statusmessage').data('message')('Saved');
					GenericButton.getButtons()["save"].enable(false);
					$('.btn.save').html('Saved');
					if (callback_function) {
						callback_function();
					}
				}
			}
		}

		// Attach save handler to Save button
		PubSub.sub('swordpushweb.save', function(data){
			savePreview();
			if (data && data.callback){
				data.callback();
			}
		});

		// Set up status message area
		var statusarea = $('#statusmessage');
		statusarea.data('message', function(message, type, delay) {
			type = type || 'info';
			if(delay === undefined){
				delay = 1500;
			}
			var ob = $('<div/>', {'class': type, text: message}).hide().appendTo(statusarea).center().fadeIn(700);
			if(delay>0){
				ob.delay(delay).fadeOut(800, function() { $(this).remove(); });
			} else {
				ob.addClass('persistent-error');
				var close = $('<i> </i>');
				ob.append(close);
				close.on('click', function(e){
					$(e.target).off('click');
					ob.remove();
				});
			}
		});

		// Fetch the preview
//		$('#statusmessage').data('message')('Loading preview...');
		Aloha.jQuery.get(body_url, function(data){
			var $d = Aloha.jQuery('<div />').html(data);
			var $editable = Aloha.jQuery('#canvas').html($d.find('>section> *'));
			if ($editable.length > 0) {
				// Remove the pyramid debug toolbar from the preview
				// if it exists. This code should do nothing in production
				$editable.find('#pDebug').remove();
				$editable.aloha().focus();

				MathJax.Hub.Configured();


				window.setInterval(savePreview, kAutoSaveDelay);			// Auto-save periodically

				setInterval(function() {
					updateWordStats();
					if (paperWasModified()) {
						$('.btn.save').html('Save');
						GenericButton.getButtons()["save"].enable(true);
						$('#exportReview').stop(false,true).fadeOut();
					}
				}, 250);
	//			Aloha.jQuery('#statusmessage').data('message')('Preview loaded');
			}
			$('#wait').fadeOut();
		});
	});
});

function initSignupForm() {
	$('#signupForm').validate({
		rules: {
			first_name: 'required',
			last_name: 'required',
			institution: 'required',
			email: {
				required: true,
				email: true
			},
			password1: {
				required: true,
				minlength: 5
			},
			password2: {
				required: true,
				minlength: 5,
				equalTo: '#password1'
			}
		},
		messages: {
			firstname: 'Please fill in your first name',
			lastname: 'Please fill in your last name',
			institution: 'Please fill in your institution',
			email: 'Please enter a valid email address',
			password1: {
				required: 'Please provide a password',
				minlength: 'Your password must be at least 5 characters long'
			},
			password2: {
				required: 'Please provide a password',
				minlength: 'Your password must be at least 5 characters long',
				equalTo: 'Please enter the same password as above'
			}
		}
	});
}

$(window).ready(function(){
	$('#metas>header, #container>footer>div>h2').bind('click touchdown', function(){
		var refsDiv = $(this).closest('div');
		refsDiv.toggleClass('details', 200, function(){
			$('#canvas').css({'padding-bottom':refsDiv.height()+50});
		});
	});
	$('.action.documents.delete').bind('click touchdown', function(e){
		e.preventDefault();
		e.stopPropagation();
		window.location.href = '?delete_paper='+$('#metas>form input[name=paper_id]').val();
	});
	initSignupForm();
});