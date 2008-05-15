/*	Script: TagMaker.js
		Prompts the user to fill in the gaps to create an html tag output.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - <Core.js>, <Class.js>, <Class.Extras.js>, <Array.js>, <Function.js>, <Number.js>, <String.js>, <Element.js>
					<Window.Size.js>, <Element.Dimensions.js>, <Element.Event.js>, <Element.Selectors.js>, <Element.Form.js>,
					<Fx.Base.js>, <Fx.Css.js>, <Fx.Style.js>, <Fx.Styles.js>, <Tips.js>
		Optional - <Drag.Base.js>, <Drag.Move.js>
		CNET - <IframeShim.js>, <clipboard.js>, <form.validator.js>, <stickyWin.js>, <stickyWinFx.js>, <stickyWin.default.layout.js>,
					<html.table.js>, <dbug.js>, <simple.error.popup.js>, <element.dimensions.js>, <element.forms.js>, <element.shortcuts.js>,
					<element.position.js>
		CNET Optional - <product.picker.js>, <stickyWinFx.Drag.js>
		
		Class: TagMaker
		Prompts the user to fill in the gaps to create an html tag output.
		
		Arguments:
		options - a key/value set of options
		
		Options:
		name - (string) the name displayed in the caption area of the popup
		output - (string) the html tag with tokens for the areas the user is to fill in (see example below)
		picklets - (object) a key/value set where the keys are the tokens in the output and the values are arrays of picklets; see <Picklet> and <ProductPicker>;
		help - (object) a key/value set where the keys are the tokens in the output and the values are help text for tooltips; see example.
		example - (object) a key/value set where the keys are the tokens in the output and the values are examples of valid inputs.
		class - (object) (object) a key/value set where the keys are the tokens in the output and the values are css classes; use these
			to pass along validators for <FormValidator> if you want the fields validated.
		selectLists - (object) (object) a key/value set where the keys are the tokens in the output and the values are arrays of objects. These sub-objects have keys for "key" and "value" that correspond to the innerText of the option and the value of the option respectively. Additionally, one of them can have the key/value set of "selected:true" to have that option be selected. See example.
		width - (integer) the width for the prompt; defaults to 400
		masHeight - (integer) the maximum height for the prompt; defaults to 500
		showResult - (boolean) if true (the default) an input is shown with the resulting output in it
		clearOnPrompt - (boolean) if true (the default) the prompt is emptied every time it is displayed
		css - (string) css rules to be injected into the page to style the prompt; defaults to the default style included in this class.

		Events:
		onPrompt - (function) callback executed when the prompt is displayed to the user
		onChoose - (function) callback executed when the user clicks "paste" or "copy", which closes the prompt
	*/
var TagMaker = new Class({
	options: {
		name: "Tag Builder",
		output: '',
		picklets: {},
		help: {},
		example: {},
		'class': {},
		selectLists: {},
		width: 400,
		maxHeight: 500,
		showResult: true,
		clearOnPrompt: true,
		baseHref: "http://www.cnet.com/html/rb/assets/global/tips/", 
		css: "table.trinket {	width: 98%;	margin: 0px auto;	font-size: 10px; }\
					table.trinket td {	vertical-align: top;	padding: 4px;}\
					table.trinket td a.button {	position: relative;	top: -2px;}\
					table.trinket td.example {	font-size: 9px;	color: #666;	text-align: right;	border-bottom: 1px solid #ddd;\
						padding-bottom: 6px;}\
					table.trinket div.validation-advice {	background-color: #a36565;	font-weight: bold;	color: #fff;	padding: 4px;\
						margin-top: 3px;}\
					table.trinket input.text {width: 100%;}\
					.tagMakerTipElement { 	cursor: help; }\
					.tagMaker-tip {	color: #fff;	width: 172px;	z-index: 13000; }\
					.tagMaker-title {	font-weight: bold;	font-size: 11px;	margin: 0;	padding: 8px 8px 4px;\
							background: url(%baseHref%/bubble.png) top left;}\
					.tagMaker-text { font-size: 11px; 	padding: 4px 8px 8px; \
							background: url(%baseHref%/bubble.png) bottom right; }",
		onPrompt: Class.empty,
		onChoose: Class.empty
	},
	initialize: function(options){
		this.setOptions(options);
		this.buttons = [
			{
				text: 'Copy',
				onClick: this.copyToClipboard.bind(this),
				properties: {
					'class': 'closeSticky tip',
					title: 'Copy::Copy the html to your OS clipboard (like hitting Ctrl+C)'
				}
			},
			{
				text: 'Paste',
				onClick: function(){
					if(this.validator.validate()) this.insert();
				}.bind(this),
				properties: {
					'class': 'tip',
					title: 'Paste::Insert the html into the field you are editing'
				}
			},
			{
				text: 'Close',
				properties: {
					'class': 'closeSticky tip',
					title: 'Close::Close this popup'
				}
			}
		];
		this.writeCss();
	},
	writeCss: function(){
		window.addEvent('domready', function(){
			try {
				if(!$('defaultTagBuilderStyle')) {
					var css = this.options.css.replace("%baseHref%", this.options.baseHref, "g");
					var styler = new Element('style').setProperty('id','defaultTagBuilderStyle').injectInside($$('head')[0]);
					if (!styler.setText.attempt(css, styler)) styler.appendText(css);
				}
			}catch(e){dbug.log('error: %s',e);}
		}.bind(this));
	},

	
/*	Property: prompt
		Prompts the user to interact with the builder.
		
		Arguments:
		target - (DOM reference or id) the input/ui element that the trinket is associated with per prompt. This allows you to have on instance that creates, say, links, but show the same one for different inputs.
	*/
	prompt: function(target){
		this.target = $(target);
		var content = this.getContent();
		if (this.options.clearOnPrompt) this.clear();
		if(content) {
				var relativeTo = (document.compatMode == "BackCompat" && this.target)?this.target:document.body;
				if(!this.win) this.win = new StickyWinFx({
					content: content,
					draggable: true,
					relativeTo: relativeTo,
					onClose: function(){
						$$('.tagMaker-tip').hide();
					}
				});
				if(!this.win.visible) this.win.show();
		}
		var innerText = this.getInnerTextInput();
		this.range = target.getSelectedRange();
		if(innerText) innerText.value = target.getTextInRange(this.range.start, this.range.end)||"";
		
		this.fireEvent('onPrompt');
	},
	clear: function(){
		this.body.getElements('input').each(function(input){
			input.value = '';
		});
	},
	getKeys: function(text) {
		return text.split('%').filter(function(inputKey, index){
				return index%2;
		});
	},
	getInnerTextInput: function(){
		return this.body.getElement('input[name=Inner-Text]');
	},
	getContent: function(){
		var opt = this.options; //save some bytes
		if(!this.form) { //if the body hasn't been created, create it
			this.form = new Element('form'); //the form
			
				var table = new HtmlTable({properties: {'class':'trinket'}});
				this.getKeys(opt.output).each(function(inputKey) {
					if(this.options.selectLists[inputKey]){
						var input = new Element('select').setProperties({
							name: inputKey.replaceAll(' ', '-')
						}).addEvent('change', this.createOutput.bind(this));
						this.options.selectLists[inputKey].each(function(opt){
							var option = new Element('option').injectInside(input);
							if(opt.selected) option.selected = true;
							option.value = opt.value;
							option.text = opt.key;
						}, this);
						table.push([inputKey, input]);
					} else {
						var input = new Element('input').setProperties({
							type: 'text',
							name: inputKey.replaceAll(' ', '-'),
							title: inputKey+'::'+opt.help[inputKey],
							'class': 'text tip ' + ((opt['class'])?opt['class'][inputKey]||'':'')
						}).addEvent('keyup', this.createOutput.bind(this)).addEvent('focus', function(){this.select()});
						if(opt.picklets[inputKey]) {
							var a = new Element('a').addClass('button').setHTML('choose');
							var div = new Element('div').adopt(input.setStyle('width','160px')).adopt(a);
							var picklets = ($type(opt.picklets[inputKey]) == "array")?opt.picklets[inputKey]:[opt.picklets[inputKey]];
							new ProductPicker(input, picklets, {
								showOnFocus: false, 
								additionalShowLinks: [a],
								onPick: function(input, data, picker){
									try {
										var ltInput = this.getInnerTextInput();
										if(ltInput && !ltInput.value) {
											try {
												ltInput.value = picker.currentPicklet.options.listItemName(data);
											}catch (e){dbug.log('set value error: ', e);}
										}
										var val = input.value;
										if(inputKey == "Full Path" && val.indexOf('http://')==0)
												input.value = val.substring(val.indexOf('/', 7), val.length);
										this.createOutput();
									} catch(e){dbug.log(e)}
								}.bind(this)
							});
							table.push([inputKey, div]);
						} else table.push([inputKey, input]);
					}
					//[{content: <content>, properties: {colspan: 2, rowspan: 3, 'class': "cssClass", style: "border: 1px solid blue"}]
					if(this.options.example[inputKey]) 
						table.push([{content: 'eg. '+this.options.example[inputKey], properties: {colspan: 2, 'class': 'example'}}]);
				}, this);
				this.resultInput = new Element('input').setProperties({
						type: 'text',
						title: 'HTML::This is the resulting tag html.',
						'class': 'text result tip'
					}).addEvent('focus', function(){this.select()});
				table.push(['HTML', this.resultInput]).tr.setStyle('display', this.options.showResult?'':'none');

			this.form = table.table;
			this.body = new Element('div').adopt(this.form).setStyles({
				overflow:'auto',
				maxHeight: this.options.maxHeight
			});
			this.validator = new FormValidator(this.form);
			this.validator.insertAdvice = function(advice, field){
				var p = $(field.parentNode);
				if(p) p.adopt(advice);
			};
		}

		if(!this.content) {
			this.content = stickyWinHTML(this.options.name, this.body, {
				buttons: this.buttons,
				width: this.options.width.toInt()+'px'
			});
			new Tips(this.content.getElements('.tip'), {
				showDelay: 700,
				maxTitleChars: 50, 
				maxOpacity: .9,
				className: 'tagMaker'
			});
		}
		return this.content;

	},
	createOutput: function(){
		var inputs = this.form.getElementsBySelector('input, select');
		var html = this.options.output;
		inputs.each(function(input) {
			if(!input.hasClass('result')) {
				html = html.replaceAll('%'+input.getProperty('name').replaceAll('-', ' ').toLowerCase()+'%',
					input.getValue(), 'i');
			}
		});
		return this.resultInput.value = html;
	},
	copyToClipboard: function(){
		var inputs = this.form.getElements('input');
		var result = inputs[inputs.length-1];
		result.select();
		Clipboard.copy(result);
		$$('.tagMaker-tip').hide();
		this.win.hide();
		this.fireEvent('onChoose');
	},
	insert: function(){
		if(!this.target) {
			simpleErrorPopup('Cannot Paste','This tag builder was not launched with a target input specified; you\'ll have to copy the tag yourself. Sorry!');
			return;
		}
		var value = (this.target)?this.target.value:this.target;
		var output = this.body.getElement("input.result");
		
		var currentScrollPos; 
		if (this.target.scrollTop || this.target.scrollLeft) {
			currentScrollPos = {
				scrollTop: this.target.scrollTop,
				scrollLeft: this.target.scrollLeft
			};
		}
		this.target.value = value.substring(0, this.range.start) + output.value + value.substring((this.range.end-this.range.start) + this.range.start, value.length);
		if(currentScrollPos) {
			this.target.scrollTop = currentScrollPos.scrollTop;
			this.target.scrollLeft = currentScrollPos.scrollLeft;
		}

		this.target.selectRange(this.range.start, output.value.length + this.range.start);
		this.fireEvent('onChoose');
		$$('.tagMaker-tip').hide();
		this.win.hide();
		return;
	}
});
TagMaker.implement(new Options, new Events);


/*	Class: TagMaker.image
		Default image tag maker.	*/
TagMaker.image = TagMaker.extend({
	options: {
		name: "Image Builder",
		output: '<img src="%Full Url%" width="%Width%" height="%Height%" alt="%Alt Text%" style="%Alignment%"/>',
		help: {
			'Full Url':'Enter the external URL (http://...) to the image',
			'Width':'Enter the width in pixels.',
			'Height':'Enter the height in pixels.',
			'Alt Text':'Enter the alternate text for the image.',
			'Alignment':'Choose how to float the image.'
		},
		example: {
			'Full Url':'http://i.i.com.com/cnwk.1d/i/hdft/redball.gif'
		},
		'class': {
			'Full Url':'validate-url required',
			'Width':'validate-digits required',
			'Height':'validate-digits required',
			'Alt Text':'required'
		},
		selectLists: {
			Alignment: [
				{
					key: 'left',
					value: 'float: left'
				},
				{
					key: 'right',
					value: 'float: right'
				},
				{
					key: 'none',
					value: 'float: none',
					selected: true
				},
				{
					key: 'center',
					value: 'margin-left: auto; margin-right: auto;'
				}
			]		
		},
		showResult: false
	}
});

/*	Class: TagMaker.anchor
		Default TagMaker for links.	*/

var TMPicklets = [];
if(typeof CNETProductPicker_ReviewPath != "undefined") TMPicklets.push(CNETProductPicker_ReviewPath);
if(typeof CNETProductPicker_PricePath != "undefined") TMPicklets.push(CNETProductPicker_PricePath);
if(typeof NewsStoryPicker_Path != "undefined") TMPicklets.push(NewsStoryPicker_Path);
TagMaker.anchor = TagMaker.extend({
	options: {
		name: "Anchor Builder",
		output: '<a href="%Full Url%">%Inner Text%</a>',
		picklets: {
			'Full Url': (TMPicklets.length)?TMPicklets:false
		},
		help: {
			'Full Url':'Enter the external URL (http://...)',
			'Inner Text':'Enter the text for the link body'
		},
		example: {
			'Full Url':'http://www.microsoft.com',
			'Inner Text':'Microsoft'
		},
		'class': {
			'Full Url':'validate-url'
		}
	}
});

/*	Class: TagMaker.cnetVideo
		CNET Internal; Default tag maker for the &lt;cnet:video/&gt; tag	*/

TagMaker.cnetVideo = TagMaker.extend({
	options: {
		name: "CNET Video Embed Tag",
		output: '<cnet:video ssaVideoId="%Video Id%" float="%Alignment%"/>',
		help: {
			'Video Id':'The id of the video to embed'
		},
		'class':{
			'Video Id':'validate-digits required'
		},
		selectLists: {
			Alignment: [
				{
					key: 'left',
					value: 'left'
				},
				{
					key: 'right',
					value: 'right'
				},
				{
					key: 'none',
					value: '',
					selected: true
				}
			]		
		}
	}
});
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/TagMaker.js,v $
$Log: TagMaker.js,v $
Revision 1.9  2007/10/18 21:43:35  newtona
updating components that reference images and assets at www.cnet.com to allow for easy over-writing for that source url

Revision 1.8  2007/09/28 00:21:19  newtona
bad reference to this.input (should have been this.target)

Revision 1.7  2007/09/27 23:24:09  newtona
adding scrollback method to tagmaker

Revision 1.6  2007/09/27 21:50:51  newtona
tagmaker: hide tooltips when the popup is hidden
jsonp: return the data AND the instance of jsonp oncomplete.

Revision 1.5  2007/09/18 18:41:04  newtona
tweaking the layout a bit in tagmaker

Revision 1.4  2007/09/18 18:16:24  newtona
ok. now I'm just adding semi-colons where they don't belong...

Revision 1.3  2007/09/18 00:44:40  newtona
removing unchecked picklet references in TagMaker so that the script isn't dependent on them.

Revision 1.2  2007/09/18 00:33:22  newtona
damned semicolons

Revision 1.1  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update


*/
