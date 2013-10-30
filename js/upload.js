var kMsgInterrupt = 'Téléversement interrompu. Veuillez réessayer en sélectionnant à nouveau votre fichier.';
var kMsgFail = 'Une erreur est survenue lors du chargement.';
var kMsgWrongType = 'Mauvais type de fichier. Les formats suivants sont acceptés : ';
var kImageLoading = '/i/loading.gif';
var kImageSuccess = '/i/ok.png';
var kImageError = '/i/error.png';
var kProgressUnknown = 'Chargement...';
var kColorProgressComplete = '#888888';
var kLabelComplete = 'OK';

if (!Array.indexOf) {
	Array.prototype.indexOf = function(obj) {
		for (var i=0; i<this.length; i++){
			if (this[i]==obj) { return i; }
		}
		return -1;
	};
}

function fileSelected(fieldID) {
	var file = document.getElementById('f'+fieldID).files[0];
	var allowedTypes = $.parseJSON($('#allowedTypes'+fieldID).html());
	if (allowedTypes.length > 0 && allowedTypes.indexOf(file.type)==-1) {
		alert(kMsgWrongType+allowedTypes+" ("+file.type+")");
		$('#f'+fieldID).val();
	}
	else {
		var fileSize = 0;
		if (file.size > 1024*1024) {
			fileSize = (Math.round(file.size * 100 / (1024*1024)) / 100).toString() + 'MB'; }
		else {
			fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB'; }
		$('#f'+fieldID).hide();
		$('#t'+fieldID).show();
		$('#info'+fieldID).html(fileSize+' ('+file.type+')');
		$('#icon'+fieldID).attr('src', kImageLoading);
		uploadFile(fieldID, file);
	}
}

function uploadFile(fieldID, file) {
	var fd = new FormData();
	fd.append('fileName', file.name);
	fd.append(document.getElementById('f'+fieldID).name, file);

	var xhr = new XMLHttpRequest();
	xhr.upload.addEventListener('progress', function(evt){uploadProgress(evt,fieldID);}, false);
	xhr.addEventListener('load', function(evt){uploadComplete(evt,fieldID);}, false);
	xhr.addEventListener('error', function(evt){uploadFailed(evt,fieldID);}, false);
	xhr.addEventListener('abort', function(evt){uploadCanceled(evt,fieldID);}, false);
	xhr.open('post', '/upload_post.php?n='+$('#fNamePolicy'+fieldID).val());
	xhr.send(fd);
}

function uploadProgress(evt,fieldID) {
	if (evt.lengthComputable) {
		var bytesUploaded = evt.loaded;
		var bytesTotal = evt.total;
		var percentComplete = Math.round(evt.loaded*100/evt.total);
		var norm, unit;
		if (bytesUploaded > 100*1024) {
			norm = (1024*1024);
			unit = 'Mo';
		}
		else {
			norm = (1024);
			unit = 'Ko';
		}
		$('#progressLabel'+fieldID).html((Math.round(bytesUploaded * 10/norm)/10).toString()+'/'+(Math.round(bytesTotal * 10/norm)/10).toString()+' '+unit);
		$('#progressBar'+fieldID).css('width', percentComplete.toString()+'%');
	}
	else {
		$('#progressBar'+fieldID).html(kProgressUnknown);
	}
}

function uploadComplete(evt,fieldID) {
	var uploadResponse = $.parseJSON(evt.target.responseText);
	if (uploadResponse.status==1) {
		showFile(fieldID, uploadResponse.file, true);
	}
	else {
		$('#icon'+fieldID).attr('src', kImageError);
		alert(uploadResponse.msg);
	}
}

function showFile(fieldID, fileName, progressVisible) {
	$('#sel'+fieldID).hide();
	$('#rep'+fieldID).show();
	//________________________________________________________
	$('#rep'+fieldID+'>.field').html('<a href="/tmp/'+fileName+'" rel="lightbox">'+fileName+'</a>');
	$('#fc'+fieldID).hide();
	$('#fFileName'+fieldID).val(fileName);
	$('#info'+fieldID).html(fileName);
	if (progressVisible) {
		$('#progressLabel'+fieldID).html(kLabelComplete);
		$('#icon'+fieldID).attr('src', kImageSuccess);
		$('#progressBar'+fieldID).css('background', kColorProgressComplete);
	}
}

function resetFile(id) {
	$('#sel'+id).show();
	$('#rep'+id).hide();
	//________________________________________________________
	$('#rep'+id+'>.field').html('');
	$('#fFileName'+id).val('');
	$('#fc'+id).show();
	$('#f'+id).wrap('<form>').closest('form').get(0).reset();	// show the file input and clear its contents
	$('#f'+id).unwrap().show();
	$('#t'+id).hide();
	$('#info'+id).html('');
	$('#del'+id).val($('#fFileName'+id).attr('name'));
}


/* Unknown error ********************************************************************************************/
function uploadFailed(/*evt,fieldID*/) {
	alert(kMsgFail);
}

/* The upload has been canceled by the user or the browser dropped the connection. **************************/
function uploadCanceled(/*evt,fieldID*/) {
	alert(kMsgInterrupt);
}