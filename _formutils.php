<?php

	include_once('_phptoolbox.php');

	$GLOBALS['ctrl_id'] = 0;	// Unique identifier for form controles, mainly used for JS

	function beginForm($method='get', $action='', $multipart=false) {
		echo '<form id="'.('form'.$GLOBALS['ctrl_id']).'" method="'.$method.'" action="'.($action==''?$_SERVER['PHP_SELF']:$action).'" '.($multipart?' enctype="multipart/form-data"':'').'>';
	}
	function endForm() {
		echo '</form>';
	}

	function printRadioInput($title, $field, $default, $options, $comment='') {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'">'.$title.' </label>'; }
		$i = 0;
		foreach ($options as $option => $label) {
			echo '<input type="radio" id="'.$GLOBALS['ctrl_id'].'_'.$i.'" name="'.$field.'" value="'.htmlspecialchars($option).'" '.($option==$default?' checked="checked"':'').' />';
			echo '<p style="display:inline; margin-right:10px;">'.$label.'</p>';
			$i++;
		}
		echo '<span class="form_comment">'.$comment.'</span>';
		return $GLOBALS['ctrl_id'];
	}

	function printTextInput($title, $field, $default, $size, $maxchars=0, $comment='', $script='') {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'">'.$title.' </label>'; }
		echo '<input type="text" id="t'.$GLOBALS['ctrl_id'].'" name="'.$field.'" value="'.htmlspecialchars($default).'" size="'.$size.'" '.($maxchars>0?'maxlength="'.$maxchars.'"':'').' '.str_replace('$ID', $GLOBALS['ctrl_id'], $script).'/>';
		if ($comment != '') { echo '<span class="form_comment">'.$comment.'</span>'; }
		return 't'.$GLOBALS['ctrl_id'];
	}

	function printPasswordInput($title, $field, $default, $size, $maxchars=0, $comment='') {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'">'.$title.' </label>'; }
		echo '<input type="password" id="p'.$GLOBALS['ctrl_id'].'" name="'.$field.'" value="'.htmlspecialchars($default).'" size="'.$size.'" '.($maxchars>0?'maxlength="'.$maxchars.'"':'').'/>';
		if ($comment != '') { echo '<span class="form_comment">'.$comment.'</span>'; }
		return 'p'.$GLOBALS['ctrl_id'];
	}

	function printStaticInput($title, $content, $size) {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label>'.$title.' </label>'; }
		echo '<input type="text" id="s'.$GLOBALS['ctrl_id'].'" value="'.htmlspecialchars($content).'" size="'.$size.'" disabled="disabled" />';
		return 's'.$GLOBALS['ctrl_id'];
	}

	function printTextArea($title, $field, $default, $cols, $rows, $class='') {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'">'.$title.' </label>'; }
		echo '<textarea id="'.$GLOBALS['ctrl_id'].'" name="'.$field.'" cols="'.$cols.'" rows="'.$rows.'"'.($class!=''?' class="'.$class.'"':'').'>'.stripslashes($default).'</textarea>';
		return $GLOBALS['ctrl_id'];
	}

	function printSelectInput($title, $field, $default, $options, $autosubmit=false) {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'">'.$title.'</label> '; }
		echo '<select id="'.$GLOBALS['ctrl_id'].'" name="'.$field.'"'.($autosubmit?' onchange="this.form.submit();"':'').'>';
		foreach ($options as $option => $label) {
			echo '<option value="'.htmlspecialchars($option).'" '.($option==$default?' selected="selected"':'').'>'.$label.'</option>';
		}
		echo '</select>';
		return $GLOBALS['ctrl_id'];
	}

	function printCheckInput($title, $field, $defaults, $options, $column=false) {
		$GLOBALS['ctrl_id']++;
		if ($title!='') { echo '<label for="'.$GLOBALS['ctrl_id'].'_0">'.$title.' </label>'; }
		$i=0;
		foreach ($options as $option => $label) {
			$check = '';
			foreach ($defaults as $default) {
				if ($option==$default) $check = ' checked="checked"';
			}
			echo '<div style="'.($column?'':'display:inline;white-space:nowrap;').'"><input type="checkbox" id="'.$GLOBALS['ctrl_id'].'_'.$i.'" name="'.$field.'[]" value="'.htmlspecialchars($option).'" '.$check.' />';
			echo '<p style="display:inline; margin-right:10px;">'.$label.'</p></div>';
			$i++;
		}
		return $GLOBALS['ctrl_id'];
	}

	function printUploadInput($title, $field, $default='', $allowedTypes=array(), $path='./', $autoRename=true, $comment='') {
		$id = ++$GLOBALS['ctrl_id'];
		if ($title!='') { echo '<label for="i'.$GLOBALS['ctrl_id'].'">'.$title.' </label>'; }
		if ($default != '') {
			if (!isset($GLOBALS['js_ul'])) {
				$GLOBALS['js_ul'] = '
				function showUploadSelect(id, field) {
					document.getElementById("del"+id).value=field;
					document.getElementById("i"+id).style.display="none";
					document.getElementById("sel"+id).style.display="inline";
				}';
				printJS($GLOBALS['js_ul']);
			}
			echo '<div id="i'.$GLOBALS['ctrl_id'].'" class="fu">';
				echo '<div class="field">';
				if ($path!='') {
					if (!strstr($default, '.jpg')&&!strstr($default, '.png')&&!strstr($default, '.gif')) { echo '<a href="'.$path.'/'.$default.'">'.$default.'</a>'; }
					else { echo '<a href="'.$path.'/'.$default.'" class="highslide" onclick="return hs.expand(this);">'.$default.'</a>'; }
				}
				else { print($default); }
				echo '</div>';
				echo '<input type="hidden" id="del'.$GLOBALS['ctrl_id'].'" name="deleteFile[]" value="" />';
				echo '<input type="button" value="Replace" onclick="showUploadSelect(\''.$GLOBALS['ctrl_id'].'\',\''.$field.'\');" style="float:right;" />';
			echo '</div>';
			echo '<span id="sel'.$GLOBALS['ctrl_id'].'" style="float:left;display:none;">';
		}
		else {
			echo '<span>';
		}
		echo '<div class="fu" id="'.$id.'">';
			// Parameters _____________________________________________
			echo '<span class="hidden" id="allowedTypes'.$id.'">'.json_encode($allowedTypes).'</span>';
			echo '<input type="hidden" id="fNamePolicy'.$GLOBALS['ctrl_id'].'" value="'.($autoRename?'auto':'file').'" />';
			echo '<input id="fFileName'.$id.'" type="hidden" name="'.$field.'" value="'.$default.'" />';
			echo '<input id="fFileType'.$id.'" type="hidden" name="'.$field.'_T" value="" />';
			// File select + infos ____________________________________
			echo '<span id="fc'.$id.'"><input type="file" name="fileToUpload" id="f'.$id.'" onchange="fileSelected(\''.$id.'\');"/></span>';	# multiple="multiple"
			echo '<div id="info'.$id.'" class="fu_fileInfo"></div>';
			// Upload monitor _________________________________________
			echo '<div id="t'.$id.'" class="fu_progress">';
				echo '<img id="icon'.$id.'" src="" width="16" height="16" alt="..." />';
				echo '<div id="progressLabel'.$id.'" class="progressValue">&nbsp;</div>';
				echo '<div class="progressBar"><div id="progressBar'.$id.'" class="progressLevel"></div></div>';
			echo '</div>';
		echo '</div>';
		echo '</span>';
		return 'i'.$GLOBALS['ctrl_id'];
	}

	function printHiddenInput($field, $value) {
		$GLOBALS['ctrl_id']++;
		echo '<input type="hidden" id="'.$GLOBALS['ctrl_id'].'" name="'.$field.'" value="'.htmlspecialchars($value).'" />';
		return $GLOBALS['ctrl_id'];
	}

	function printSubmitInput($field, $title, $alignLabel=false) {
		if ($alignLabel) { echo '<label>&nbsp;</label>'; }
		echo '<input type="submit" name="'.$field.'" value="'.htmlspecialchars($title).'" />';
	}

	function printDeleteInput($field, $title, $id, $message='Do you really want to delete this item?') {
		if (!isset($GLOBALS['js_delete'])) {
			$GLOBALS['js_delete'] = '
			function confirmDelete(id, field, message) {
				if (confirm(message)) { window.top.location.href = "'.$_SERVER['PHP_SELF'].'?"+field+"="+id; }
			}';
			printJS($GLOBALS['js_delete']);
		}
		if ($title=='') {
			echo '<a href="#" onclick="confirmDelete(\''.$id.'\', \''.$field.'\', \''.addslashes($message).'\');"><img class="btn" src="i/delete.png" alt="[DELETE]" width="16" height="16" /></a>';
		}
		else {
			echo '<input type="button" name="'.$field.'" value="'.htmlspecialchars($title).'" onclick="confirmDelete(\''.$id.'\', \''.$field.'\', \''.addslashes($message).'\');" />';
		}
	}

	function printLinkButton($link, $title, $image='', $inNewWindow=false) {
		if ($inNewWindow) {
			$action = 'open(\''.$link.'\',\'new\',\'width=1040,height=1000,toolbar=yes,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes\')';
		}
		else {
			$action = 'window.top.location.href=\''.$link.'\'';
		}
		if ($image!='') {
			echo '<a href="#" onclick="'.$action.'" title="'.$title.'"><img class="btn" src="i/'.$image.'" alt="'.$title.'" width="16" height="16" /></a>';
		}
		else {
			echo '<input type="button" value="'.htmlspecialchars($title).'" onclick="'.$action.'" />';
		}
	}
