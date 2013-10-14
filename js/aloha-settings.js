(function(window, undefined){

    if (window.Aloha === undefined || window.Aloha === null) {
        var Aloha = window.Aloha = {};
    }

    require.config({ waitSeconds: 42 });

    Aloha.settings = {
        jQuery: window.jQuery,
        logLevels: {'error': true, 'warn': true, 'info': true, 'debug': false},
        errorhandling : true,
        requireConfig: {
            waitSeconds: 42,
            paths: {
                // Override location of jquery-ui and use our own. Because
                // jquery-ui and bootstrap conflict in a few cases (buttons,
                // tooltip) our copy has those removed.
                jqueryui: '/Aloha-Editor/oerpub/js/jquery-ui-1.9.0.custom-aloha'
            },
            map: {
                '*': {
                    'ui/ui': 'toolbar/toolbar-plugin'
                }
            }
        },
        plugins: {
            assorted: {
                image: {
                    preview: true,
                    uploadSinglepart: true,
                    uploadurl: '/upload.php'
                }
            },
            genericbutton: {
                buttons: [{'id': 'save', 'title': 'Save', 'event': 'swordpushweb.save' }]
            },
            toolbar: {
                formats: {
                  'p':   'Normal Text',
                  'h2':  'Chapter Heading',
                  'h3':  'Section Heading',
                  'h4':  'Subsection Heading',
                  'pre': 'Code',
                  'bdo': 'Right-to-Left'
                }
            },
            format: {
                config : ['b', 'i', 'u', 'p', 'sub', 'sup', 'h2', 'h3', 'h4', 'pre', 'bdo']
            },
            table: {
                editables: {
                    '#canvas': { enabled: true },
                    '.title-editor': {enabled: false},
                }
            },
            block: {
                defaults : {
                    '.default-block': {
                    },
                    'figure': {
                        'aloha-block-type': 'FigureBlock'
                    }
                },
                rootTags: ['span', 'div', 'figure'],
                dragdrop: "1"
            },
			'dom-to-xhtml': {
		    },
            zotero: {
            	referenceContainer: '#citationsList'
            }
        },
        bundles: {
            // Path for custom bundle relative from require.js path
            oer: '/Aloha-Editor/src/plugins/oer',
            dh: '/Aloha-Editor/src/plugins/dh'
        }
    };

    Aloha.settings.contentHandler = {
        insertHtml: [ 'word', 'generic', 'oembed'],
        initEditable: [],
        getContents: []
    }


})(window);
