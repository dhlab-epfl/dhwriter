/*global window: true define: true*/
/*!
* Aloha Editor
* Author & Copyright (c) 2010 Gentics Software GmbH
* aloha-sales@gentics.com
* Licensed unter the terms of http://www.aloha-editor.com/license.html
*/

define([
    'aloha',
	'jquery',
	'overlay/overlay-plugin',
	'aloha/plugin',
	'ui/ui',
	'ui/toggleButton',
	'util/dom',
	'PubSub',
	'i18n!zotero/nls/i18n',
	'i18n!aloha/nls/i18n'
], function (
	Aloha,
	jQuery,
	Popover,
	Plugin,
	Ui,
	ToggleButton,
	domUtils,
	PubSub,
    i18n,
	i18nCore
){
	'use strict';

	var $ = jQuery,
		GENTICS = window.GENTICS,
		ns  = 'aloha-zotero',
		uid = (new Date()).getTime();

	var DIALOG_HTML = '<form class="modal" id="zoteroModal" tabindex="-1" role="dialog" aria-labelledby="zoteroModalLabel" aria-hidden="true">\
      <div class="modal-header">\
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
        <h3 id="zoteroModalLabel">Reference</h3>\
      </div>\
      <div class="modal-body tabs">\
		<ul>\
			<li><a href="#tabs-1">Manual Input</a></li>\
			<li><a href="#tabs-2">My Library</a></li>\
		</ul>\
		<div id="tabs-1">\
	        <div>\
	          <span>Title</span>\
	          <div>\
	            <input id="zotero-title" class="input-xlarge" type="text" placeholder="Enter full citation reference here" required />\
	          </div>\
	        </div>\
        </div>\
		<div id="tabs-2">\
        </div>\
      </div>\
      <div class="modal-footer">\
        <button class="btn btn-primary zotero-save">Save</button>\
        <button class="btn zotero-delete" data-dismiss="modal" aria-hidden="true">Delete</button>\
      </div>\
    </form>';

    var TOOLTIP_TEMPLATE = '<div class="aloha-ephemera tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>';

    var kRefListIdPrefix = 'ref-uid-';
    var kRefTextIdPrefix = 'citation-';

/*<div>\
          <span>Author</span>\
          <div>\
            <input id="zotero-author" class="input-xlarge" type="text" placeholder="Author Name" required />\
          </div>\
        </div>\
*/
	// namespaced classnames
	var nsClasses = {
		quote         : nsClass('quote'),
		blockquote    : nsClass('blockquote'),
		'link-field'  : nsClass('link-field'),
		'note-field'  : nsClass('note-field'),
		references    : nsClass('references')
	};

	/**
	 * Generates a selector string with this plugins's namespace prefixed the
	 * each classname.
	 *
	 * Usage:
	 *    nsSel('header,', 'main,', 'foooter ul')
	 *    will return
	 *    ".aloha-myplugin-header, .aloha-myplugin-main, .aloha-mypluzgin-footer ul"
	 *
	 * @return {string}
	 */
	function nsSel() {
		var strBldr = [], prx = ns;
		jQuery.each(arguments, function () {
			strBldr.push('.' + ('' === this ? prx : prx + '-' + this));
		});
		return jQuery.trim(strBldr.join(' '));
	}

	/**
	 * Generates a string with this plugins's namespace prefixed the each
	 * classname.
	 *
	 * Usage:
	 *		nsClass('header', 'innerheaderdiv')
	 *		will return
	 *		"aloha-myplugin-header aloha-myplugin-innerheaderdiv"
	 *
	 * @return {string}
	 */
	function nsClass() {
		var strBldr = [], prx = ns;
		jQuery.each(arguments, function () {
			strBldr.push('' === this ? prx : prx + '-' + this);
		});
		return jQuery.trim(strBldr.join(' '));
	}

	function getContainerAnchor(q) {
      var el = q;
      while (el) {
        if (el.nodeName.toLowerCase() === 'cite') {
          return el;
        }
        el = el.parentNode;
      }
      return false;
    };

	return Plugin.create('zotero', {
		citations: [],
		referenceContainer: null,
		settings: null,
		config: ['cite'],

		init: function () {
			var that = this;

			// Harverst configuration options that may be defined outside of the plugin.
			if (Aloha.settings && Aloha.settings.plugins && Aloha.settings.plugins.zotero) {

				var referenceContainer = jQuery(Aloha.settings.plugins.zotero.referenceContainer);

				if (referenceContainer.length) {
					that.referenceContainer = referenceContainer;
				}

				if (typeof Aloha.settings.plugins.zotero !== 'undefined') {
					that.settings = Aloha.settings.plugins.zotero;
				}
			}

			this._quoteButton = Ui.adopt('zotero', null, {
				tooltip: i18n.t('zotero.button.add.quote'),
				scope: 'Aloha.continuoustext',
				click: function() {
					var $q, q, dialog, quoteText;
					var editable = Aloha.activeEditable;
					var range = Aloha.Selection.getRangeObject();
					if (range.startContainer === range.endContainer) {
						q = getContainerAnchor(range.startContainer);
						if (q) {
							$q = jQuery(q);
							range.startContainer = range.endContainer = q;
							range.startOffset = 0;
							range.endOffset = q.childNodes.length;
						} else {
							range.select();
							$q = that.addInlineQuote();
						}
						dialog = that.showModalDialog($q);
					} else {
						return;
					}
					return dialog.on('hidden', function() {
						/*
						var newLink;
						Aloha.activeEditable = editable;
						if ($a.hasClass('aloha-new-link')) {
							if (!$a.attr('href')) {
								return;
							}
							range = Aloha.Selection.getRangeObject();
							if (range.isCollapsed()) {
								GENTICS.Utils.Dom.insertIntoDOM($a, range, Aloha.activeEditable.obj);
								range.startContainer = range.endContainer = $a.contents()[0];
								range.startOffset = 0;
								range.endOffset = $a.text().length;
							} else {
								GENTICS.Utils.Dom.removeRange(range);
								GENTICS.Utils.Dom.insertIntoDOM($a, range, Aloha.activeEditable.obj);
							}
							newLink = Aloha.activeEditable.obj.find('.aloha-new-link');
							return newLink.removeClass('aloha-new-link');
						}
						*/
					});
				}
	        });

			var zoteroPlugin = this;


			Aloha.ready(function(ev){
				setTimeout(function(){zoteroPlugin.reloadCitations(zoteroPlugin)}, 1000);
			});

			Aloha.bind('aloha-editable-activated', function (event, params) {
				var config = that.getEditableConfig(params.editable.obj);
				if (!config) {
					return;
				}
				that._quoteButton.show(true);
			});

    		Aloha.bind('aloha-editable-created', function(evt, editable) {
				jQuery(editable.obj).on('click', 'cite', function(evt) {
					var $el = jQuery(this);
					$el.contentEditable(false);								// Make sure the citation point is never editable
					var range = new GENTICS.Utils.RangeObject();			// Update what Aloha thinks is the selection. Can't just use Aloha.Selection.updateSelection because the thing that was clicked isn't editable and setSelection will just silently return without triggering the selection update.
					range.startContainer = range.endContainer = $el[0];
					range.startOffset = range.endOffset = 0;
					Aloha.Selection.rangeObject = range;
					Aloha.trigger('aloha-selection-changed', [range, evt]);
					evt.stopPropagation();
					that.showModalDialog($el);
				});
				if (jQuery.ui && jQuery.ui.tooltip) {
					return editable.obj.tooltip({
						items: 'cite',
						content: function() {
							return 'Click to edit reference';
						},
						template: TOOLTIP_TEMPLATE
					});
				} else {
					return editable.obj.tooltip({
						selector: 'cite',
						placement: 'top',
						title: 'Click to edit reference',
						trigger: 'hover',
						template: TOOLTIP_TEMPLATE
					});
				}
			});

			Aloha.bind( 'aloha-selection-changed', function (event, rangeObject) {
				that._quoteButton.show(Aloha.Selection.rangeObject.startOffset===Aloha.Selection.rangeObject.endOffset);
			});

			PubSub.sub('aloha.selection.context-change', function (message) {
				var rangeObject = message.range;
				var buttons = jQuery('button.aloha-zotero-button');

				// Set to false to prevent multiple buttons being active when they should not.
				var statusWasSet = false;
				var nodeName;
				var effective = rangeObject.markupEffectiveAtStart;
				var i = effective.length;

				// Check whether any of the effective items are citation tags.
				while ( i ) {
					nodeName = effective[--i].nodeName;
					if (nodeName === 'CITE') {
						statusWasSet = true;
						break;
					}
				}

				buttons.filter('.aloha-zotero-block-button').removeClass('aloha-zotero-pressed');

				that._quoteButton.setState(false);

				if ( statusWasSet ) {
					that._quoteButton.setState(true);

					// We've got what we came for, so return false to break the each loop.
					return false;
				}

				// switch item visibility according to config
				var config = [];
				if (Aloha.activeEditable) {
					config = that.getEditableConfig(Aloha.activeEditable.obj);
				}

				// quote
				that._quoteButton.show(Aloha.Selection.rangeObject.startOffset===Aloha.Selection.rangeObject.endOffset);
			});
		},

		reloadCitations: function(zoteroPlugin) {
			var editor = Aloha.getEditableById('canvas');
			if (editor === null) {
				setTimeout(function(){zoteroPlugin.reloadCitations(zoteroPlugin)}, 1000);
			}
			else {
				zoteroPlugin.citations.splice();		// Remove all already loaded citations
				zoteroPlugin.prepareRefContainer();
				var contents = editor.getContents();
				jQuery(contents).find('cite').each(function(/*idx*/){
					var uid = $(this).attr('id').substring(kRefTextIdPrefix.length);
					if (uid!='undefined') {
						var reference = $(this).children('span').html();
						zoteroPlugin.addRefLineToContainer(uid, '#', reference);
						zoteroPlugin.citations.push({
							uid   : uid,
							reference : null
						});
					}
				});
			}
		},
		showModalDialog: function($el) {
			var that = this;
			var root = Aloha.activeEditable.obj;
			var dialog = jQuery(DIALOG_HTML);
			dialog.attr('data-backdrop', false);
			var a = $el.get(0);
			var zoteroDocTitle = dialog.find('#zotero-title');
			var zoteroSave = dialog.find('.zotero-save');

			dialog.find('.tabs').tabs();

			dialog.find('#zotero-title').val($el.children('span').html()).autocomplete({source:'/_.php?f=getCitations&', minLength:2, autoFocus:true}).on('autocompleteselect', function(e, ui){
				that.addCiteDetails(uid, ui.item.value);		// Since the value hasn't been copied into the field yet...
			});

			dialog.find('input, textarea').bind('keyup change', function() {
									that.addCiteDetails(uid, zoteroDocTitle.val());
								});
/*
			var appendOption, figuresAndTables, massageUrlInput, orgElements;

			appendOption = function(id, contentsToClone) {
				var clone, contents, option;
				clone = contentsToClone[0].cloneNode(true);
				contents = jQuery(clone).contents();
				option = jQuery('<option></option>');
				option.attr('value', '#' + id);
				option.append(contents);
				return option.appendTo(linkInternal);
			};
			orgElements = root.find('h1,h2,h3,h4,h5,h6');
			figuresAndTables = root.find('figure,table');
			orgElements.filter(':not([id])').each(function() {
				return jQuery(this).attr('id', GENTICS.Utils.guid());
			});
			orgElements.each(function() {
				var id, item;
				item = jQuery(this);
				id = item.attr('id');
				return appendOption(id, item);
			});
			figuresAndTables.each(function() {
				var caption, id, item;
				item = jQuery(this);
				id = item.attr('id');
				caption = item.find('caption,figcaption');
				if (caption[0]) {
					return appendOption(id, caption);
				}
			});

			dialog.find('a[data-toggle=tab]').on('shown', function(evt) {
				var newTab, prevTab;
				prevTab = jQuery(jQuery(evt.relatedTarget).attr('href'));
				newTab = jQuery(jQuery(evt.target).attr('href'));
				prevTab.find('.link-input').removeAttr('required');
				return newTab.find('.link-input').attr('required', true);
			});
			href = $el.attr('href');
			dialog.find('.active').removeClass('active');
			linkInputId = '#link-tab-external';
			if ($el.attr('href').match(/^#/)) {
				linkInputId = '#link-tab-internal';
			}
			dialog.find(linkInputId).addClass('active').find('.link-input').attr('required', true).val(href);
			dialog.find("a[href=" + linkInputId + "]").parent().addClass('active');
			massageUrlInput = function($input) {
				var url;
				url = $input.val();
				if (/^http/.test(url) || /^htp/.test(url) || /^htt/.test(url)) {

				} else {
					if (!/^https?:\/\//.test(url)) {
						return $input.val('http://' + url);
					}
				}
			};
			linkExternal.on('blur', function(evt) {
				return massageUrlInput(linkExternal);
			});
			linkExternal.bind('keydown', 'return', function(evt) {
				return massageUrlInput(linkExternal);
			});
*/
			dialog.on('submit', function(evt) {
				var active;
				evt.preventDefault();
				if (zoteroDocTitle.val() && zoteroDocTitle.val().trim()) {
					$el.html('<span>'+zoteroDocTitle.val().trim()+'</span>');
				}
				active = dialog.find('.link-input[required]');
				$el.attr('href', active.val());
				$.get('_.php', {'f':'addCitation', 'label':zoteroDocTitle.val().trim()});
				return dialog.modal('hide');
			});
			dialog.on('click', '.btn.zotero-delete', function(evt) {
				var rawText = $el.text();
				$el.replaceWith('');
				that.reloadCitations(that);
			});
			dialog.modal('show');
			dialog.on('hidden', function() {
				return dialog.remove();
			});
			return dialog;
		},
		/**
		 * Do a binary search through all citations for a given uid.  The bit shifting may be a *bit* of an overkill, but with big lists it proves
		 * to be significantly more performant.
		 *
		 * @param {string} uid Th uid of the citation to retreive.
		 * @return {number} The 0-based index of the first citation found that matches the given uid. -1 of no citation is found for the given uid
		 */
		getIndexOfCitation: function(uid) {
			var c = this.citations;
			var max = c.length;
			var min = 0;
			var mid;
			var cuid;

			// Infinite loop guard for debugging...  So your tab/browser
			// doesn't freeze up like a Christmas turkey ;-)
			// var __guard = 1000;

			while (min < max /* && --__guard */ ) {
				mid = (min + max) >> 1;						// Math.floor(i) / 2 == i >> 1 == ~~(i / 2)
				cuid = c[mid].uid;

				// Don't do strict comparison here or you'll get an endless loop
				if (cuid == uid) {
					return mid;
				}

				if (cuid > uid) {
					max = mid;
				} else if (cuid < uid) {
					min = mid + 1;
				}
			}

			return -1;
		},

		addInlineQuote: function () {
			var classes = [nsClass('wrapper'), nsClass(++uid)].join(' ');
			var markup = jQuery('<cite class="'+classes+'" id="'+kRefTextIdPrefix+uid+'" data-reference-id="'+uid+'"><span></span></cite>');

			var rangeObject = Aloha.Selection.rangeObject;

			if (rangeObject !== 'undefined') {
				var index = this.getIndexOfCitation(uid);
				if (-1 === index) {
					index = this.citations.push({
						uid   : uid,
						reference : ''
					}) - 1;
				}

				if (Aloha.activeEditable) {
					jQuery(Aloha.activeEditable.obj[0]).click();
				}

				domUtils.insertIntoDOM(markup, rangeObject, $(Aloha.activeEditable.obj), true);
				// select the modified range
				rangeObject.select();

				if (this.referenceContainer) {
/*					var index = this.getIndexOfCitation(uid);

					if (-1 !== index) {
						var wrapper = jQuery('.aloha-editable-active ' + nsSel(uid));
						var num = index+1;
						var link = '#'+kRefTextIdPrefix+uid;

						wrapper.append('<a href="'+link+'"></a>');
					}*/
					this.reloadCitations(this);
				}

				return markup;
			}

			return false;
		},


		prepareRefContainer: function() {
			this.referenceContainer.html('<ol class="references"></ol>');
		},

		addRefLineToContainer: function(uid, link, reference) {
			this.referenceContainer.find('ol.references').append('<li id="'+kRefListIdPrefix+uid+'"><a href="'+link+'"><span>'+reference+'</span></a></li>');
		},

		/**
		 * Responsible for updating the citation reference in memory, and in
		 * the references list when a user adds or changes information for a
		 * given citation.
		 *
		 * @param {string} uid
		 * @param {string} reference
		 */
		addCiteDetails: function (uid, reference) {
			this.citations[this.getIndexOfCitation(uid)] = {
				uid  : uid,
				reference : reference
			};
			// Update information in references list for this citation.
			if (this.referenceContainer) {
				jQuery('li#'+kRefListIdPrefix+uid+' span').html(reference);
				jQuery('cite#'+kRefTextIdPrefix+uid).children('span').html(reference);
			}
		},

		/**
		 * Make the given jQuery object (representing an editable) clean for saving
		 * Find all quotes and remove editing objects
		 * @param obj jQuery object to make clean
		 * @return void
		 */
		makeClean: function (obj) {

			// find all quotes
			obj.find('cite').each(function () {
				// Remove empty class attributes
				if (jQuery.trim(jQuery(this).attr('class')) === '') {
					jQuery(this).removeAttr('class');
				}
				// Only remove the data-zotero attribute when no reference container was set
				if (!this.referenceContainer) {
					jQuery(this).removeClass('aloha-zotero-' + jQuery(this).attr('data-zotero-id'));

					// We need to read this attribute for IE7. Otherwise it will
					// crash when the attribute gets removed. In IE7 this removal
					// does not work at all. (no wonders here.. :.( )
					if (jQuery(this).attr('data-zotero-id') != null) {
						jQuery(this).removeAttr('data-zotero-id');
					}
				}

				jQuery(this).removeClass('aloha-zotero-wrapper');

			});
		}

	});

});
