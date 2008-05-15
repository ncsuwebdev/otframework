/*	Script: Overview

This document doesn't contain any code (except one function, dbugScripts, which is used in compressed files and is included here only for documentation); it just outlines the CNET Global Framework and how to use it and maintain it.

Credits:
Documentation and original framework authored by Aaron Newton (aaron [dot] newton [at] cnet [dot] com), with the exception of Mootools (http://mootools.net).


Section: Libraries
Each individual *file* in this collection contains a small amount of functionality. Files should be broken up as much as possible for two reasons: so that their functionality can be included only when required and to make the documentation easier to use.

Individual files are then concatenated and compressed into *libraries* that are actually released.

These libraries are the result of combining numerous other scripts to create an environment that engineers and tech producers can depend on. 

Section: cnet.global.framework.js
Across all redball properties we include a base javascript file that includes all shared functionality across all sites. This file is *cnet.global.framework.js* and it contains the following files.

Mootools:
 - <Moo.js>
 - <Utility.js>
 - <Array.js>
 - <String.js>
 - <Function.js>
 - <Element.js>
 - <Event.js>
 - <Common.js>
 - <Dom.js>
 - <Hash.js>
 - <Color.js>
 - <Window.Base.js>
 - <Window.Size.js>
 - <Fx.Base.js>
 - <Fx.CSS.js>
 - <Fx.Style.js>
 - <Fx.Styles.js>
 - <Fx.Elements.js>
 - <XHR.js>
 - <Ajax.js>
 - <Cookie.js>
 - <Json.js>
 - <Json.Remote.js>
 - <Accordion.js>


CNET files:
  - <prototype.compatability.js>
	- <window.cnet.js>
	- <string.cnet.js>
	- <element.shortcuts.js>
	- <element.legacy.js>
	- <element.position.js>
	- <element.dimensions.js>
	- <Fx.SmoothShow.js>
	- <ajax.cnet.js>
	- <fixpng.js>
	- <IframeShim.js>
	- <mouseovers.js>
	- <tabswapper.js>
	- <local.vars.js>
	- <login.status.js>
	- <search.functions.js>

Section: Global (common) files not in cnet.global.framework.js
The collection here contains numerous other widgets and helper functions that are not in the global file included on all pages. These files are still considered global because they are not specific to any single site. For example, a class that validates forms doesn't care if the form is on Download.com or CNET.com.

Individual files from the library are meant to be included only in the environments that need them. These can all be found in the *cnet.global.framework/common* directory.

Section: utility/common vs. implemenation code
Code that is written to be reused elsewhere, typically in the form of a <Class> goes into the *common* directory of the Global Framework files. Code that implements those classes or contains functions and variables that are specific to a single environment goes into the *implementations* directory.

Common code:
In general, you should try and write as much of your code as general as you can, then, seperately, implement that code for your environment. For instance, if you wanted to make a popup alert DHTML window in the page, write the popup code first, then implement an instance of it for your error window. All common code should go into the global framework *common* directory.

Section: Global implementations
Located in the *cnet.global.implemenations* directory are a few implementation scripts (for searching, logging in, etc.) that are included on all pages.

Section: Site implementations
Each site on the cnet network (downloads, news, etc) has a folder of its own. In this folder is one for concatenating files, another for compressed files, and an *implementations* directory. This directory should contain all the code that is specific to that site. Each file should be broken up as much as is practical and each should contain executions and instances of common code in the *global/common* directory.
	
Section: concatenation
	The files in the global framework are broken up into funciton specific documents and then
	concatenated before being compressed. We then compress (see: <compression>) these concatenated
	libraries together into the actual published files.
	
	In this way we can have useful documentation rendered and easily maintain the code, but the file
	delievered to the user is compressed and requires as few hits to the server as possible.
	
	To concatenate the files into those that are to be compressed, you can execute the batch files in
	the cat directory. These contain an ordered list of the files in them so that the files are 
	concatenated in the proper order. You can edit these files to include more libraries, but care
	should be taken to ensure that files that have dependencies are included after those on which
	they depend, as well as to ensure that functionality that is not needed on most of our pages
	don't weigh down the global library.
	
	Example of a concatenation batch file:
(start code)
echo off
echo building download.global.framework.js

echo ADDING GLOBAL FRAMEWORK FILES
echo adding cnet.global.framework\addons\carousel.js
cat "..\..\global\cnet.global.framework\addons\carousel.js" > download.doors.js

echo ADDING DL.COM IMPLEMENTATION FILES
echo adding download.implementations\tabs\tabSet.tabs.js
cat "..\download.implementations\tabs\tabSet.tabs.js" >> download.doors.js

pause
(end)

	The first *cat* command *must* have only one right quote (>) so that it creates a new file, 
	overwriting what was there before. All subsequent cat commands must have double quotes (>>) 
	so that their contents will be appended. The last line (pause) will let the window remain 
	open until the user hits a key.

	These batch files are clumsy and antiquated, but until we have a build system in place for
	these files they are the fastest way to create the concatenated libraries.

Section: compression
	As stated in the <concatenation> section, our javascript is maintained in numerous smaller files
	to make maintenance easier and also to make documentation more useful, then concatenated to collect
	the functionality into files meant for compression, and then finally compressed into releaseable code.
	
	The compression for these documents is handled using Dean Edwards /packer/ program, which can be
	found here: <http://dean.edwards.name/packer/>. We use the compression level of <b>none</b>, which just
	strips tabs, comments, extra spaces, and line breaks. While the other schemes produce smaller files, 
	the browser has to decompress the file every time it loads it, even if it's cached. We choose to pay
	the price once (on the first page load); for example, the redball.global.framework when using the default
	compression in this compressor takes around 400ms to decompress.
	
	Each library generated in the /cat directory is compressed and dropped into the /compressed directory.
	
	Note:
	A copy of the /packer/ website is included in cvs at /flatfile/html/rb/js/js.util/packer/.
	
	See also:
	<dbugScripts>
	
Section: debugging
	The global framework has some pretty slick debugging tools that I hope to add to with time. The first is the
	<dbug> object, which lets you put debugging lines in your code but only print them out to the firebug 
	(<http://getfirebug.com>) console when you turn debugging on. If you don't have firebug installed or 
	are on a browser other than firefox, the library comes with <Debugger.js> which will emulate firebug (crudely).
  This <dbug> wrapper ensures that your console commands don't execute if firebug isn't installed or 
	the user is not using firefox, so you	don't get js errors or crash safari.
	
	It also means that even if you do have those things you won't see our messy debug statements unless you
	explicitely turn them on.
	
	There are three ways to turn debugging on so that your <dbug> log commands are executed:
	
	- include "jsdebug=true" in the url query string (http://cnet.com/......?jsdebug=true or &jsdebug=true if there
		is already a query string)
	- type dbug.enable() into the console
	- put dbug.enable() in your code (but don't publish it that way!)
	
	See also: <dbug>, <dbug.enable>
	
	Since all the script we deliver is compressed (see <compression>) debugging it is tricky because the 
	code is abstracted and there are no line breaks. including jsdebug=true in the header of your document will also
	include the uncompressed scripts and discard the compressed ones so you can actually find errors and fix them.
	This command will also include <Debugger.js> as well.
	
	The files included depend on how the script is configured, but typically it includes the files on c18 in
	the cat directory.
	
	See also: <concatenation>, <dbugScripts>

Function: dbugScripts
	This function is included at the top of any compressed file and allows the user to instruct the browser
	to discard the compressed file and instead include an uncompressed version of the library.
	
	Arguments:
	baseurl - the url directory in which the uncompressed libraries exist
	libs - array with the remainder of the url not expressed in the base url for each script to be
				included when debugging
	
	Examples:
	(start code)
	if(!dbugScripts("http://www.somewhere.com/js/", ["myScript.js"]
		eval(...compressed version of myScript.js from <http://dean.edwards.name/packer/>);
	}
	(end)
		If the url for the page includes jsdebug=true, we'll throw out the compressed stuff and instead include the file at
		http://www.somewhere.com/js/myScript.js
	(start code)
	if(!dbugScripts("http://www.somewhere.com/js/",["one.js","two.js","three.js"])){
		eval(...compressed code from /packer/ for one.js...);
		eval(...compressed code from /packer/ for two.js...);
		eval(...compressed code from /packer/ for three.js...);
	});
	(end)
	 Here we're including several files in place of the compressed stuff.
	(start code)
	if(!dbugScripts("http://www.somewhere.com/js/",["one/something.js","two/another.js"])){
		eval(...compressed code from /packer/ for one/something.js...);
		eval(...compressed code from /packer/ for two/another.js...);
	});
	(end)
	Here the included debugging scripts are in different directories.
	
	Additional Functionality:
	You can also include "basePath=this" and instead of pointing to the url configured in the compressed script,
	the script included will be at the host and port that you're on.
	
	For example, let's say this is where you're working:
	
	http://dev.cnet.com:8006/blah/blah.html
	
	If you change your url to this:
	
	http://dev.cnet.com:8006/blah/blah.html?jsdebug=true
	
	You'll turn on dbug and you'll include the decompressed scripts on the servers specified in them (typically
	our back-end publish server). For example you might point at this file:
	
	/html/rb/js/global/cat/cnet.base.js
	
	If you change the url to this:
	
	http://dev.cnet.com:8006/blah/blah.html?jsdebug=true&basePath=this
	
	You'll change the host and port to be the one of the page you're looking at:
	
	http://dev.cnet.com:8006/html/rb/js/global/cat/cnet.base.js
	*/
function dbugScripts(baseurl,libs){
	var value = document.cookie.match('(?:^|;)\\s*jsdebug=([^;]*)');
	var debugCookie = value ? unescape(value[1]) : false;
	if(window.location.href.indexOf("basePath=this")>0){
		var path=baseurl.substring(baseurl.substring(7,baseurl.length).indexOf("/")+8,baseurl.length);
		var href=window.location.href;
		baseurl=href.substring(href.substring(7,href.length).indexOf("/")+8,href.length);
	}
	if(window.location.href.indexOf("jsdebug=true")>0 || window.location.href.indexOf("jsdebugCookie=true")>0 || debugCookie == 'true'){ 
		for(var i=0;i<libs.length;i++){
			document.write("<scri"+"pt src=\""+baseurl+libs[i]+"\" type=\"text/javascript\"></script>");
		}
		return true;
	}
	return false;
};
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/Overview.js,v $
$Log: Overview.js,v $
Revision 1.14  2007/04/13 19:06:11  newtona
dependency update in the docs

Revision 1.13  2007/03/09 20:20:24  newtona
strict javascript warnings cleaned up

Revision 1.12  2007/02/08 19:18:12  newtona
updating debugScripts to use cookies

Revision 1.11  2007/01/26 06:19:01  newtona
docs update

Revision 1.10  2007/01/23 00:28:51  newtona
docs update

Revision 1.9  2007/01/22 22:49:05  newtona
docs update

Revision 1.8  2007/01/11 21:08:16  newtona
docs update, cnet.functions -> local.vars.js and login.status.js

Revision 1.7  2007/01/10 00:06:32  newtona
updated docs for syntax error

Revision 1.6  2007/01/09 23:06:54  newtona
*** empty log message ***

Revision 1.5  2007/01/09 22:46:36  newtona
updated docs

Revision 1.4  2006/11/22 00:58:52  newtona
docs update

Revision 1.3  2006/11/22 00:45:17  newtona
docs update

Revision 1.2  2006/11/04 00:51:21  newtona
updated dbugScripts to add the basePath=this option

Revision 1.1  2006/11/02 21:30:13  newtona
first check in, added cvs footer


*/
