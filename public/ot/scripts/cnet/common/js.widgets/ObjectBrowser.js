/*	Script: ObjectBrowser.js
		Creates a tree view of any javascript object.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Mootools 1.11 - Core.js, Class.js, Class.Extras.js, Array.js, Function.js, String.js, Number.js, Element.js, Hash.js
		
		Class: ObjectBrowser
		Creates a tree view of any javascript object.
		
		Arguments:
		container - (DOM element or id) the container where to build the html elements for the browser.
		options - (object) a key/value set of options
		
		Options:
		data - (object) the object to explore
		initPath - (string) the path in the object where the tree display starts; defaults to "", which is the top of the options data object
		buildOnInit - (boolean) if true, builds the interface with the data in the options; defaults to true
		excludeKeys - (array of strings) list of key names that should not be displayed in the object tree;
									defaults to none (an empty array)
		includeKeys - (array of strings) list of key names to explicitly include in the object tree;
									defaults to none (an empty array)
		
		Events:
		onLeafClick - (function) function called when a leaf (a node with no children) is clicked; see data section below for arguments passed to event.
		onBranchClick - (function) function called when a branch (a node with children) is clicked; see data section below for arguments passed to event.
		
		Data passed to events:
		li - (element) the dom element that was clicked
		key - (string) the key of the value in the tree
		value - (mixed) the corresponding value of the item clicked in the tree
		path - (string) the path to the parent node of the item clicked; the path + "." + key will give you the full path to the value.
		nodePath - (string) the path to the node in the tree; computed as path + "." + key + "NODE"; used to find the injection parent for the new list items.
		event - (event) the event object for the clicked so it can be manipulated; it has already been stopped.		
	*/
var ObjectBrowser = new Class({
	options: {
		onLeafClick: Class.empty,
		onBranchClick: function(data){
			this.showLevel(data.path?data.path+'.'+data.key:data.key, data.nodePath);
		},
		initPath: '',
		buildOnInit: true,
		data: {},
		excludeKeys: [],
		includeKeys: []
	},
	initialize: function(container, options){
		this.container = $(container);
		this.setOptions(options);
		this.data = $H(this.options.data);
		this.levels = {};
		this.elements = {};
		if(this.options.buildOnInit) this.showLevel(this.options.initPath, this.container);
	},
	//gets a member of the object by path; eg "fruits.apples.green" will return the value at that path.
	//path - the string path
	//parent - (boolean) if true, will return the parent of the item found ( in the example above, fruits.apples)
	getMemberByPath: function(path, parent){
		if (path === "" || path == "top") return this.data.obj;
		var member = this.data.obj;
		var steps = path.split(".");
		if (parent) steps.pop();
		steps.each(function(p){
			if (p === "") return;
			if (member[p]) member = member[p];
			else if ($chk(Number(p)) && member[Number(p)]) member = member[Number(p)];
			else member = this.data.obj;
		}, this);
		return (member == this.data)?false:member;
	},
	//replaceMemberByPath will set the location at the path to the value passed in
	replaceMemberByPath: function(path, value){
		if (path === "" || path == "top") return this.data = $H(value);
		var parentObj = this.getMemberByPath( path, true );
		parentObj[path.split(".").pop()] = value;
		return this.data;
	},
	//gets the path for a given dom node.
	getPathByNode: function(el) {
		var elements = $H(this.elements);
		return elements.keys()[elements.values.indexOf(el)];
	},
	//validates that a key is a valid node value
	//against options.includeKeys and options.excludeKeys
	validLevel: function(key){
		return (!this.options.excludeKeys.contains(key) && 
			 (!this.options.includeKeys.length || this.options.includeKeys.contains(key)));
	},
	//builds a level into the interface given a path
	buildLevel:function(path) {
		//if the path ends in a dot, remove it
		if (path.test(".$")) path = path.substring(0, path.length);
		//get the corresponding level for the path
		var level = this.getMemberByPath(path);
		//if the path already has been built, return
		if (this.levels[path]) return this.levels[path];
		//create the section
		var section = new Element('ul');
		switch($type(level)) {
			case "function":
					this.buildNode(level, "function()", section, path, true);
				break;
			case "string": case "number":
					this.buildNode(level, null, section, path, true);
				break;
			case "array":
				level.each(function(node, index){
					this.buildNode(node, index, section, path, ["string", "function"].contains($type(node)));
				}.bind(this));
				break;
			default:
				$H(level).each(function(value, key){
					var db = false;
					if (key == "element_dimensions") db = true;
					if (db) dbug.log(key);
					if (this.validLevel(key)) {
						if (db) dbug.log('is valid level');
						var isLeaf;
						if ($type(value) == "object") {
							isLeaf = false;
							$each(value, function(v, k){
								if (this.validLevel(k)) {
									if (db) dbug.log('not a leaf!');
									isLeaf = false;
								} else {
									isLeaf = true;
								}
							}, this);
							if (isLeaf) value = false;
						}
						if (db) dbug.log(value, key, section, path, $chk(isLeaf)?isLeaf:null);
						this.buildNode(value, key, section, path, $chk(isLeaf)?isLeaf:null);
					}
				}, this);
		}
		//set the resulting DOM element to the levels map
		this.levels[path] = section;
		//return the section
		return section;
	},
	//gets the parent node for an element
	getParentFromPath: function(path){
		return this.elements[(path || "top")+'NODE'];
	},
	//displays a level given a path
	//if the level hasn't been built yet,
	//the level is built and then injected
	//into the target using the given method
	//example:
	//showLevel("fruit.apples", "fruit", "injectInside");
	//note that target and method are set to the parent path and injectInside by default
	showLevel: function(path, target, method){
		target = target || path;
		if (! this.elements[path]) 
			this.elements[path] = this.buildLevel(path)[method||"injectInside"](this.elements[target]||this.container);
		else this.elements[path].toggle();
		this.elements[path].getParent().toggleClass('collapsed');
	},
	//builds a node given the arguments:
	//value - the value of the node
	//key - the key of the node
	//section - the container where this node goes; typically a section generated by buildLevel
	//path - the path to this node
	//leaf - boolean; true if this is a leaf node
	//note: if the key or the value is an empty string, leaf will be set to true.
	buildNode: function(value, key, section, path, leaf){
		if (key==="" || value==="") leaf = true;
		if(!this.validLevel(key)) return null;
		var nodePath = (path?path+'.'+key:key)+'NODE';
		var lnk = this.buildLink((leaf)?value||key:$chk(key)?key:value, leaf);
		var li = new Element('li').addClass((leaf)?'leaf':'branch collapsed').adopt(lnk).injectInside(section);
		lnk.addEvent('click', function(e){
			new Event(e).stopPropagation();
			if (leaf) {
				this.fireEvent('onLeafClick', {
					li: li, 
					key: key, 
					value: value, 
					path: path,
					nodePath: nodePath,
					event: e
				});
			} else {
				this.fireEvent('onBranchClick', {
					li: li, 
					key: key, 
					value: value, 
					path: path,
					nodePath: nodePath,
					event: e
				});
			}							
		}.bind(this));
		this.elements[nodePath] = li;
		return li;
	},
	//builds a link for a given key
	buildLink: function(key) {
		if($type(key) == "function") {
			key = key.toString();
			key = key.substring(0, key.indexOf("{")+1)+"...";
		}
		return new Element('a', {
			href: "javascript: void(0);"
		}).setHTML(key);
	}
});
ObjectBrowser.implement(new Options, new Events);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/ObjectBrowser.js,v $
$Log: ObjectBrowser.js,v $
Revision 1.5  2007/11/29 19:13:36  newtona
missed a semi-colon in ObjectBrowser...

Revision 1.4  2007/11/19 23:23:05  newtona
CNETAPI: added method "getMany" to all the CNETAPI.Utils.* classes so that you can get numerous items in one request.
ObjectBrowser: improved exclusion handling for child elements
jlogger.js, element.position.js: docs update
Fx.Sort: cleaning up tabbing
string.cnet: just reformatting the logic a little.

Revision 1.3  2007/10/15 22:10:24  newtona
adding number as an object type in objectbrowser

Revision 1.2  2007/10/09 22:39:26  newtona
documented CNETAPI.Category.Browser, ObjectBrowser
doc tweaks on other files
rebuilding docs to javascript.cnet.com/docs


*/
