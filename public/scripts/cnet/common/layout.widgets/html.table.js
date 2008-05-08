/*	Script: html.table.js
		Builds table elements with methods to add rows quickly.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools - <Moo.js>, <Utilities.js>, <Common.js>, <Array.js>, <String.js>, <Element.js>, <Function.js>

		Class: HtmlTable
		Builds table elements with methods to add rows quickly.
		
		Arguments: 
		options - (object) a key/value set of options
		
		Options:
		properties - a set of properties for the Table element (defaults to cellpadding: 0, cellspacing: 0, border: 0)
		rows - (array) an array of row objects (see <HtmlTable.push>)
		
		Properties:
		table - the table DOM element (which you would inject into your document somewhere)
	*/
var HtmlTable = new Class({
	options: {
		properties: {
			cellpadding: 0,
			cellspacing: 0,
			border: 0
		},
		rows: []
	},
	initialize: function(options) {
		this.setOptions(options);
		if(this.options.properties.className){
			this.options.properties['class'] = this.options.properties.className;
			delete this.options.properties.className;
		}
		this.table = new Element('table').setProperties(this.options.properties);
		this.tbody = new Element('tbody').injectInside(this.table);
		this.options.rows.each(this.push.bind(this));
	},
	//row = [{content: <content>, properties: {colspan: 2, rowspan: 3, class: "cssClass", style: "border: 1px solid blue"}]
	//OR
	//row = [<content>,<content>,etc.]

/*	Property: row
		Inserts a new table row.
		
		Arguments:
		row - (array) the data for the row.
		
		Row data:
		Row data can be in either of two formats.
		
		simple - an array of strings that will be inserted into each table data
		detailed - an array of objects with definitions for content and properties for each td
		
		Example:
(start code)
var myTable = new HtmlTable();
myTable.push(['value 1','value 2', 'value 3']); //new row
myTable.push([
	{
		content: 'value 4',
		properties: {
			colspan: 2,
			className: 'doubleWide',
			style: '1px solid blue'
	},
	{
		content: 'value 5'
	}
]);
myTable.injectInside(document.body);

RESULT:
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>value 1</td>
		<td>value 2</td>
		<td>value 3</td>
	</tr>
	<tr>
		<td colspan="2" class="doubleWide" style="1px solid blue">value 4</td>
		<td>value 5</td>
	</tr>
</table>
(end)
	
	Returns:
	An object containing the tr and td tags. Looks like this:
	> {tr: theTableRow, tds: [td, td, td]}
	*/
	push: function(row) {
		var tr = new Element('tr').injectInside(this.tbody);
		var tds = row.map(function (tdata) {
			var td = new Element('td').injectInside(tr);
			if(tdata.properties) {
				if(tdata.properties.className){
					tdata.properties['class'] = tdata.properties.className;
					delete tdata.properties.className;
				}
				td.setProperties(tdata.properties);
			}
			function setContent(content){
				if($(content)) td.adopt($(content));
				else td.setHTML(content);
			};
			if(tdata.content) setContent(tdata.content);
			else setContent(tdata);
			return td;
		}, this);
		return {tr: tr, tds: tds};
	}
});
HtmlTable.implement(new Options);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/layout.widgets/html.table.js,v $
$Log: html.table.js,v $
Revision 1.5  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.4  2007/08/20 18:11:49  newtona
Iframeshim: better handling for methods when the shim isn't ready
stickyWin: fixed a bug for the cssClass option in stickyWinHTML
html.table: push now returns the dom elements it creates
jsonp: fixed a bug; request no longer requires a url argument (my bad)
Fx.SmoothMove: just tidying up some syntax.
element.dimensions: updated getDimensions method for computing size of elements that are hidden; no longer clones element

Revision 1.3  2007/06/12 20:46:20  newtona
added tbody to html.table.js
added legacy argument support to Fx.SmoothShow

Revision 1.2  2007/05/17 19:45:43  newtona
product picker: hide() now hides tooltips; onPick passes in a 3rd argument that is the picker
stickyWinHTML: fixed a bug with className options for buttons
html.table: fixed a bug with className options for buttons

Revision 1.1  2007/05/16 20:09:41  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)


*/
