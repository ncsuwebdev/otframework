/*	Script: simple.editor.js
		A simple html editor for wrapping text with links and whatnot.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <String.js>, <Array.js>, <Function.js>, <Element.js>, <Dom.js>
		CNET - <Element.forms.js>, <Element.shortcuts.js>
		Optional - <Trinket.Base.js>, <Trinket.contexts.js>, <Trinket.LinkBuilder.js>, <StickyWinModal>, <stickyWinHTML>

		Class: SimpleEditor
		A simple html editor for wrapping text with links and whatnot.
		
		Arguments:
		input - (DOM element or id) the input this editor modifies
		buttons - (css selector or <Elements> collection) all the links 
							and/or buttons/images that make changes to the text when clicked.
		commands - (optional, object) a commands object (see below) for this editor.
		
		Commands:
		<SimpleEditor> comes with a handful of common commands to wrap text with bold tags or italics, etc. You can define your own and add them to all SimpleEditors or to a specific instance.

		A command id made up of a shortcut key and a function that is passed the input.
		
		Example:
(start code)
bold: {
	shortcut: 'b',
	command: function(input){
		input.insertAroundCursor({before:'<b>',after:'</b>'});
	}
}
(end)

		When the user clicks the button or hits ctrl+b, the tag will be inserted around the selected text.
		
		See <SimpleEditor.addCommand> and <SimpleEditor.addCommands> on how to add your own.
		
		Buttons/Links:
		The buttons passed in must have a property "rel" equal to the key of the command they execute.
		
		Example:
(start code)
<img src="bold.gif" alt="Bold (ctrl+b)" title="Bold (ctrl+b)" rel="bold">
(end)
		
		In the example above, the rel="bold" will map this image to the bold command.
	*/
var SimpleEditor = new Class({
	initialize: function(input, buttons, commands){
		this.commands = new Hash($merge(SimpleEditor.commands, commands||{}));
		this.input = $(input);
		this.buttons = $$(buttons);
		this.buttons.each(function(button){
			button.addEvent('click', function() {
				this.exec(button.getProperty('rel'));
			}.bind(this));
		}.bind(this));
		this.input.addEvent('keydown', function(e){
			e = new Event(e);
			if (e.control) {
				var key = this.shortCutToKey(e.key);
				if(key) {
					e.stop();
					this.exec(key);
				}
			}
		}.bind(this));
	},
	shortCutToKey: function(shortcut){
		var returnKey = false;
		this.commands.each(function(value, key){
			if(value.shortcut == shortcut) returnKey = key;
		});
		return returnKey;
	},
/*	Property: addCommand
		Inserts a single command to the SimpleEditor.
		
		*Note*: You can use this method on your instance of this class to add the command to that instance, or you can execute it on the class namespace and all <SimpleEditor> instances created after this will get these commands.

		Arguments:
		key - (string) the unique identifier for this command ("bold", "italics", etc.)
		command - (function) funciton to execute on the input; the function is passed the input as an argument
		shortcut - (character, optional) a shortcut key that, when pressed in conjunction with ctrl, will execute
								the function

		Example:
(start code)
//all instances will get bold tags as <strong></strong>
SimpleEditor.addCommand('bold', function(input) {
	input.insertAroundCursor({before:'<strong>',after:'</strong>'});
}, 'b')

//but this instance will get bold tags as <b></b>
var myEditor = new SimpleEditor(input, $$('img.editbuttons'));
myEditor.addCommand('bold', function(input){
	input.insertAroundCursor({before:'<b>',after:'</b>'});
}, 'b');
(end)
	*/
	addCommand: function(key, command, shortcut){
		this.commands.set(key, {
			command: command,
			shortcut: shortcut
		});
	},

/*	Property: addCommand
		Inserts a collection of commands to the SimpleEditor.
		
		*Note*: You can use this method on your instance of this class to add the command to that instance, or you can execute it on the class namespace and all <SimpleEditor> instances created after this will get these commands.

		Arguments:
		commands - (object) a key/value set of commands (see below)
		
		Commands:
		This is an object whose key is the command key. Its members are key/values for the shortcut value and the command function. The example below should illustrate this more clearly.

		Example:
(start code)
//all instances will get bold tags as <strong></strong> and italics as <em></em>
SimpleEditor.addCommands(SimpleEditor.addCommands({
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<strong>',after:'</strong>'});
		}
	},
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<em>',after:'</em>'});
		}
	}
));

//but this instance will get bold tags as <b></b> and italics as <i></i>
var myEditor = new SimpleEditor(input, $$('img.editbuttons'));
myEditor.addCommands(SimpleEditor.addCommands({
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<b>',after:'</b>'});
		}
	},
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<i>',after:'</i>'});
		}
	}
});
(end)
	*/
	addCommands: function(commands){
		this.commands.extend(commands);
	},
	exec: function(key){
		var currentScrollPos; 
		if (this.input.scrollTop || this.input.scrollLeft) {
			currentScrollPos = {
				scrollTop: this.input.scrollTop,
				scrollLeft: this.input.scrollLeft
			};
		}
		if(this.commands.hasKey(key)) this.commands.get(key).command(this.input);
		if(currentScrollPos) {
			this.input.scrollTop = currentScrollPos.scrollTop;
			this.input.scrollLeft = currentScrollPos.scrollLeft;
		}
	}
});
$extend(SimpleEditor, {
	commands: {},
	addCommand: function(key, command, shortcut){
		SimpleEditor.commands[key] = {
			command: command,
			shortcut: shortcut
		}
	},
	addCommands: function(commands){
		$extend(SimpleEditor.commands, commands);
	}
});
/*	Default commands:	*/
SimpleEditor.addCommands({
	/*	bold - <b></b>	*/
	bold: {
		shortcut: 'b',
		command: function(input){
			input.insertAroundCursor({before:'<b>',after:'</b>'});
		}
	},
	/*	underline - <u></u>	*/
	underline: {
		shortcut: 'u',
		command: function(input){
			input.insertAroundCursor({before:'<u>',after:'</u>'});
		}
	},
	/*	anchor - uses <Trinket.LinkBuilder> if present	*/
	anchor: {
		shortcut: 'l',
		command: function(input){
			function simpleLinker(){
				if(window.TagMaker){
					if(!this.linkBuilder) this.linkBuilder = new TagMaker.anchor();
					this.linkBuilder.prompt(input);
				} else {
					var href = window.prompt('The URL for the link');
					var opts = {before: '<a href="'+href+'">', after:'</a>'};
					if (!input.getSelectedText()) opts.defaultMiddle = window.prompt('The link text');
					input.insertAroundCursor(opts);
				}
			}
			try {
				if(Trinket) {
					if(!this.linkBulder){
						var lb = Trinket.available.filter(function(trinket){
							return trinket.name == 'Link Builder';
						});
						this.linkBuilder = (lb.length)?lb[0]:new Trinket.LinkBuilder({
							context: 'default'
						});
						this.linkBuilder.clickPrompt(input);
					}
				} else simpleLinker();
			} catch(e){ simpleLinker(); }
		}
	},
	/*	copy - if <Clipboard.js> is present	*/
	copy: {
		shortcut: false,
		command: function(input){
			if(Clipboard) Clipboard.copyFromElement(input);
			else simpleErrorPopup('Woops', 'Sorry, this function doesn\'t work here; use ctrl+c.');
			input.focus();
		}
	},
	/*	cut - if <Clipboard.js> is present	*/
	cut: {
		shortcut: false,
		command: function(input){
			if(Clipboard) {
				Clipboard.copyFromElement(input);
				input.insertAtCursor('');
			} else simpleErrorPopup('Woops', 'Sorry, this function doesn\'t work here; use ctrl+x.');
		}
	},
	/*	hr - <hr/>	*/
	hr: {
		shortcut: '-',
		command: function(input){
			input.insertAtCursor('\n<hr/>\n');
		}
	},
	/*	img - <img src="">	*/
	img: {
		shortcut: 'g',
		command: function(input){
			if(window.TagMaker) {
				if(!this.anchorBuilder) this.anchorBuilder = new TagMaker.image();
				this.anchorBuilder.prompt(input);
			} else {
				input.insertAtCursor('<img src="'+window.prompt('The url to the image')+'" />');
			}
		}
	},
	/*	stripTags - removes all tags from the selection	*/
	stripTags: {
		shortcut: '\\',
		command: function(input){
			input.insertAtCursor(input.getSelectedText().stripTags());
		}
	},
	/*	supertext - <sup></sup>	*/
	sup: {
		shortcut: false,
		command: function(input){
			input.insertAroundCursor({before:'<sup>', after: '</sup>'});
		}
	},
	/*	subtext - <sub></sub>	*/
	sub: {
		shortcut: false,
		command: function(input){
			input.insertAroundCursor({before:'<sub>', after: '</sub>'});
		}
	},
	/*	paragraph - <p></p>	*/
	paragraph: {
		shortcut: 'enter',
		command: function(input){
			input.insertAroundCursor({before:'\n<p>\n', after: '\n</p>\n'});
		}
	},
	/*	strike - <strike></strike>	*/
	strike: {
		shortcut: 'k',
		command: function(input){
			input.insertAroundCursor({before:'<strike>',after:'</strike>'});
		}
	},
	/*	italics - <i></i>	*/
	italics: {
		shortcut: 'i',
		command: function(input){
			input.insertAroundCursor({before:'<i>',after:'</i>'});
		}
	},
	/*	bullets - <ul><li></li></ul>	*/
	bullets: {
		shortcut: '8',
		command: function(input){
			input.insertAroundCursor({before:'<ul>\n	<li>',after:'</li>\n</ul>'});
		}
	},
	/*	numberList - <ol><li></li></ol>	*/
	numberList: {
		shortcut: '=',
		command: function(input){
			input.insertAroundCursor({before:'<ol>\n	<li>',after:'</li>\n</ol>'});
		}
	},
	/*	clean - removes non-asci MSword style characters with <Element.tidy>	*/
	clean: {
		shortcut: false,
		command: function(input){
			input.tidy();
		}
	},
	/*	preview - uses <StickyWinModal>	to display a preview */
	preview: {
		shortcut: false,
		command: function(input){
			try {
				if(!this.container){
					this.container = new Element('div', {
						styles: {
							border: '1px solid black',
							padding: 8,
							height: 300,
							overflow: 'auto'
						}
					});
					this.preview = new StickyWinModal({
						content: stickyWinHTML("preview", this.container, {
							width: 600,
							buttons: [{
								text: 'close',
								onClick: function(){
									this.container.empty();
								}.bind(this)
							}]
						}),
						showNow: false
					});
				}
				this.container.setHTML(input.getValue());
				this.preview.show();
			} catch(e){dbug.log('you need StickyWinModal and stickyWinHTML')}
		}
	}
});
/* do not edit below this line */	 
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/simple.editor.js,v $
$Log: simple.editor.js,v $
Revision 1.6  2007/09/18 18:20:29  newtona
fixing improper input reference in simple editor

Revision 1.5  2007/09/18 00:38:57  newtona
removing trailing comma in simple editor.

Revision 1.4  2007/09/15 00:16:51  newtona
fixing a syntax error

Revision 1.3  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.2	2007/09/07 00:50:03	tierneyc
adding scroll position get / set functionality to the simple editor command exec function.

Revision 1.1	2007/06/02 01:35:46	newtona
*** empty log message ***


*/