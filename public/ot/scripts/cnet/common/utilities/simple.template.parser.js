/*	Script: simple.template.parser.js
		Provides functionality for very simple template parsing; for more complex template parsing, use TrimPath's excellent Javascript Templates (JST): http://trimpath.com/project/wiki/JavaScriptTemplates.

		Dependencies:
		Moo - <Moo.js>, <Utility.js>, <Function.js>, <String.js>
	
		Author:
		Aaron Newton (aaron [dot] newton [at] cnet [dot] com)
		
		Object: simpleTemplateParser
		This object provides functionality for very simple template parsing; for more complex template parsing, use TrimPath's excellent Javascript Templates (JST): http://trimpath.com/project/wiki/JavaScriptTemplates. It can be used on its own or implemented into a class.
	*/

var simpleTemplateParser = {
		STP: {},
/*	Property: parseTemplate
		Parses a template with the values of an object, substituting those values for all instances of the keys in the object found within the template.

		Arguments: 
		template - a string to parse
		object - the object with your key/value pairs
		regexOptions - the options for the regex replace; defaults to 'ig' (ignore case, global replace)
		wrappers - an object with the before and after strings that are on either side of your keys (see example);
			defaults to {before: "%", after: "%"}

		Example:
(start code)
<textarea id="myTemplate">
	<p>This is some html that lets me subsitute things.</p>
	<ul>
		<li>%firstThing%</li>
		<li>%secondThing%</li>
		<li>%thirdThing%</li>
	</ul>
</textarea>
<script>
	var myTemplate = $('myTemplate').innerHTML;
	var myObject = {
		firstThing: 'hi there',
		secondThing: 'howzit goin?',
		thirdThing: 'really? me too!'
	}
	var parsed = simpleTemplateParser.parseTemplate(myTemplate, myObject);
</script>(end)
	*/
		parseTemplate: function(template, object, regexOptions, wrappers) {
			var STP = this.STP;
			STP.template = template;
			STP.object = object;
			STP.regexOptions = $pick(regexOptions, 'ig');
			STP.wrappers = $pick(wrappers, {before:'%', after:'%'});
			return STP.result = this.runParser(STP.object, STP.template, STP.regexOptions);
		},
		runParser: function(object, string, regexOptions){
			for(value in object){
				switch($type(object[value])){
					case 'string':
						string = this.tmplSubst(value, object[value], string, regexOptions);
						break;
					case 'number':
						string = this.tmplSubst(value, object[value], string, regexOptions);
						break;
					case 'object':
						string = this.runParser(object[value]);
						break;
					case 'array':
						string = this.tmplSubst(value, object[value].toString(), string, regexOptions);
						break;
				}
			}
			return string;
		},
		tmplSubst: function(key, value, string, regexOptions){
			return string.replace(new RegExp(this.STP.wrappers.before+key+this.STP.wrappers.after, 'gi'), value);
		}
	};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/utilities/simple.template.parser.js,v $
$Log: simple.template.parser.js,v $
Revision 1.4  2007/06/07 18:43:37  newtona
added CSS to autocompleter.js
removed string.cnet.js dependencies from template parser and stickyWin.default.layout.js

Revision 1.3  2007/03/02 01:32:52  newtona
swapped out string.replace with string.replaceAll

Revision 1.2  2007/01/26 05:56:03  newtona
syntax update for mootools 1.0
docs update

Revision 1.1  2007/01/09 02:39:35  newtona
renamed addons directory to "common" directory

Revision 1.2  2007/01/09 01:25:47  newtona
docs syntax fix

Revision 1.1  2007/01/05 18:55:02  newtona
first check in


*/
