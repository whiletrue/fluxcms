/* ***** BEGIN LICENSE BLOCK *****
 * Licensed under Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Bitflux code.
 *
 * The Initial Developer of the Original Code is Bitflux
 * Portions created by the Initial Developer are Copyright (C) 2002-2003
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 * - Christian Stocker (Bitflux)
 *
 * ***** END LICENSE BLOCK ***** */

/************************************************************************************
 * mozDataTransport v0.52
 * 
 * Generic File Transport Driver infrastructure first seen in Bitflux, a multi-purpose,
 * in-browser, XML editor.
 *
 * This infrastructure supports a variety of ways to publish data in Mozilla.
 * Particular drivers for webdav or http specialize this class
 *
 * POST05:
 * - add support for CVS, ftp and other ways to post files to CMSs
 * - save to file: see mozedit and jslib projects - http://www.bokil.com/downloads/
 *
 ************************************************************************************/

function mozTransportDriver (driverName) {
	this.container = eval(" new mozTransportDriver_"+driverName);
}

mozTransportDriver.prototype.load = function (filename, callback) {
	var id = "mozTransportDriver.load";
	this.callback = this.loadCallback;
	this.userLoadCallback = callback;
	this.filename = filename;
	debug ("load " + filename, { "evalArguments":true});
	this.container.load(filename, this);
}

mozTransportDriver.prototype.save = function (filename, content, callback) {
	this.callback = this.saveCallback;
	this.userSaveCallback = callback;
	this.filename = filename;
	debug ("save " + filename,  { "evalArguments":true});
	this.container.save(filename, content, this);
}

mozTransportDriver.prototype.loadCallback = function (reqObj) {
	reqObj.td = this;
	reqObj.filename = this.filename;
	if (this.userLoadCallback) { 
		this.userLoadCallback(reqObj);
	}
}

mozTransportDriver.prototype.saveCallback = function (reqObj) {
	reqObj.td = this;
	reqObj.filename = this.filename;

	if (this.userSaveCallback) {
		this.userSaveCallback(reqObj);
	}
}

mozTransportDriver.prototype.parseResponseXML = function(responseXML, status) {
		var reqObj = new Object();

	// try to find parserror
		var parserErrorNode = responseXML.getElementsByTagNameNS("http://www.mozilla.org/newlayout/xml/parsererror.xml","parsererror")[0];
		if (parserErrorNode)
		{
			alerttext = parserErrorNode.firstChild.data;
			var sourceNode = parserErrorNode.getElementsByTagName("sourcetext")[0];
			if (sourceNode) {
				alerttext += "\n" + sourceNode.firstChild.data;
			}
			alerttext+= strXML;
		}
		else
		{
			alerttext="Something went wrong:\n\n" + status + "\n\n";
			var objXMLSerializer = new XMLSerializer;
			var strXML = objXMLSerializer.serializeToString(responseXML.documentElement);
			alerttext+= strXML;
		}
		reqObj.isError = true;
		reqObj.statusText = alerttext;
		reqObj.document = responseXML;
		if (status === 0) {
			reqObj.status = 400;
		} else {
			reqObj.status = status;
		}
		return reqObj;
	
}

mozTransportDriver.prototype.parseResponseText = function(responseText, status) {

	var reqObj = new Object();

	alerttext="Something went wrong:\n\n";
	alerttext += responseText ;
	reqObj.isError = true;
	reqObj.statusText = alerttext;
	if (status === 0) {
		reqObj.status = 400;
	} else {
		reqObj.status = status;
	}
	return reqObj;
}

/******************************** webDAV driver ***********************************
 *
 * Depends on external implementation of webDAV: for example, jsdav from twingle
 * 
 **********************************************************************************/

function mozTransportDriver_webdav() {
	this.p = new DavClient();
}

mozTransportDriver_webdav.prototype.load = function (filename, td) {
	this.p.request.td = td;
	//backup Massnahme (sometimes td get's lost with the request...)
	bxe_config.td = td;
	this.p.request.onload = this.loadCallback;
	this.p.GET(filename);
}

mozTransportDriver_webdav.prototype.save = function (filename, content, td) {
	this.p.request.td = td;
	this.p.request.onload = this.saveCallback;
	this.p.PUT(filename, content );
}

mozTransportDriver_webdav.prototype.loadCallback = function (e) {
	
	var p = e.currentTarget;
	var td = p.td;
	//backup Massnahme (sometimes td get's lost with the request...)
	if (!td) {
		td = bxe_config.td;
	}
	
	var reqObj = new Object();
	// if there's no element called parsererror...
	if (p.responseXML.getElementsByTagNameNS("http://www.mozilla.org/newlayout/xml/parsererror.xml","parsererror").length == 0) {
		reqObj.document = p.responseXML;
		reqObj.isError = false;
		reqObj.status = 200;
		reqObj.statusText = "OK";
	} else if (p.responseXML) {
		reqObj =  td.container.parseResponseXML(p.responseXML);
	}
	else {
		reqObj = td.container.parseResponseText(p.responseText);
	}
	td.loadCallback(reqObj);
}

mozTransportDriver_webdav.prototype.saveCallback = function (e) {
	var p = e.currentTarget;
	var td = p.td;
	var reqObj = new Object();
	// status code = 204, then it's ok
	if (p.status == 204) {
		reqObj.document = p.responseXML;
		reqObj.isError = false;
		reqObj.status = 200;
		reqObj.statusText = "OK";
	} 
	else if (p.status == 201) {
		reqObj.document = p.responseXML;
		reqObj.isError = false;
		reqObj.status = 201;
		reqObj.statusText = "Created";
	}
	else if (p.responseXML) {
		reqObj = td.container.parseResponseXML(p.responseXML, p.status);
	} else {
		reqObj = td.container.parseResponseText(p.responseText, p.status);
	}
	reqObj.originalStatus = p.status;
	reqObj.originalStatusTest = p.statusText;
	td.saveCallback(reqObj);
}

mozTransportDriver_webdav.prototype.parseResponseXML = function(responseXML, status) {
		var reqObj = new Object();

	// try to find parserror
		var parserErrorNode = responseXML.getElementsByTagNameNS("http://www.mozilla.org/newlayout/xml/parsererror.xml","parsererror")[0];
		if (parserErrorNode)
		{
			alerttext = parserErrorNode.firstChild.data;
			var sourceNode = parserErrorNode.getElementsByTagName("sourcetext")[0];
			if (sourceNode) {
				alerttext += "\n" + sourceNode.firstChild.data;
			}
			alerttext+= strXML;
		}
		else
		{
			alerttext="Something went wrong:\n\n" + status + "\n\n";
			var objXMLSerializer = new XMLSerializer;
			var strXML = objXMLSerializer.serializeToString(responseXML.documentElement);
			alerttext+= strXML;
		}
		reqObj.isError = true;
		reqObj.statusText = alerttext;
		reqObj.document = responseXML;
		if (status === 0) {
			reqObj.status = 400;
		} else {
			reqObj.status = status;
		}
		return reqObj;
	
}

mozTransportDriver_webdav.prototype.parseResponseText = function(responseText, status) {

	var reqObj = new Object();

	alerttext="Something went wrong:\n\n";
	alerttext += responseText ;
	reqObj.isError = true;
	reqObj.statusText = alerttext;
	if (status === 0) {
		reqObj.status = 400;
	} else {
		reqObj.status = status;
	}
	return reqObj;
}

/********************************* http driver *******************************/

function mozTransportDriver_http () {

	/**
	* XMLHttpRequest Object
	*
	* We use the same XMLHttpRequest in the whole instance
	* @type Object
	*/
	
}

/*
* Loads a file over http get
* @tparam String filename the filename (can be http://... or just a relative path
*/

mozTransportDriver_http.prototype.load = function(filename, td) {
	var docu = document.implementation.createDocument("","",null);
	docu.loader = this.parent;
	docu.td = td;
	docu.onload = this.loadCallback;
	try {
		docu.load(filename);
	}
	catch (e) {
		var reqObj = new Object();
		reqObj.document = docu;
		reqObj.isError = true;
		reqObj.status = 404;
		reqObj.statusText = filename + " could not be loaded\n" + e.message;
		td.loadCallback(reqObj);
	}
	return docu;
}

mozTransportDriver_http.prototype.loadCallback = function (e) {
	var reqObj = new Object();
	reqObj.document = e.currentTarget;
	reqObj.isError = false;
	reqObj.status = 200;
	reqObj.statusText = "OK";
	var td = e.currentTarget.td;
	if (!td) {
		debug("td was not in e.currentTarget!!! Get it from global var");
	}
	td.loadCallback(reqObj);
}

/**
* Save a file over http post. It just posts the whole xml file without variable
* assignment (in PHP you have to use $HTTP_RAW_POST_DATA or php://input for getting the content)
*/

mozTransportDriver_http.prototype.save = function(filename, content, td)
{
	this.p = new XMLHttpRequest();
	this.p.onload = this.saveCallback;
	this.p.td = td;
	this.p.open("POST",filename );
	this.p.send(content,true);
}

mozTransportDriver_http.prototype.saveCallback = function (e) {
	var p = e.currentTarget;
	var td = p.td;
	var reqObj = new Object();
	// status code = 204, then it's ok
	if (p.status == 204) {
		reqObj.document = p.responseXML;
		reqObj.isError = false;
		reqObj.status = 200;
		reqObj.statusText = "OK";
	} 
	else if (p.status == 201) {
		reqObj.document = p.responseXML;
		reqObj.isError = false;
		reqObj.status = 201;
		reqObj.statusText = "Created";
	}
	else if (p.responseXML) {
		reqObj = td.parseResponseXML(p.responseXML, p.status);
	} else {
		reqObj = td.parseResponseText(p.responseText, p.status);
	}
	reqObj.originalStatus = p.status;
	reqObj.originalStatusTest = p.statusText;
	td.saveCallback(reqObj);
}

/******************************** local file driver ***************************/

function mozTransportDriver_file() {
	
}

//filename is not supported yet..
mozTransportDriver_file.prototype.save = function (filename, content, td) {
	
	
	var mfp = mozilla.createFilePicker(MozFilePicker.MODE_SAVE, "save to local file");
	var reqObj  = new Object();

	if(mfp)
	{
		mfp.addFilter(MozFilePicker.FILTER_HTML);
		if(mfp.promptUser())
		{
			var mf = mfp.file;
			mf.write(content); 
			reqObj.isError = false;
			reqObj.status = 200;
			reqObj.statusText = "OK";
			
		} else {
		}
		
	}
	else
	{	
		reqObj.isError = true;
		reqObj.status = 404;
		reqObj.statusText = "mozSave: can't save-to-file because Mozilla doesn't allow remote scripts to launch its native file picker dialog. Either run moz locally or wait until it is packaged as an extension. For more information, see http://moz.mozdev.org/use.html."; 
	}
	td.saveCallback(reqObj);
	
}


/******************************** dummy or alert file driver ***************************/

function mozTransportDriver_alert() {
	
}

mozTransportDriver_alert.prototype.save = function (filename, content, td) 
{

	var message =
		" ** Mozile Alert ** \n" +
		"The following could be posted to a file or remote CMS:" +
		"\n-------------------------------------------------\n" +
		content +
		"\n-------------------------------------------------\n" +
		"Would you like to save this to a local file?"

		// Show the message and offer to save to a local file.
	var yes=confirm(message);
	if(yes) {
		var td = new mozTransportDriver("file");
		td.save("", content, __mozileSaved);
	}


	var reqObj  = new Object();
	reqObj.isError = false;
	reqObj.status = 200;
	reqObj.statusText = "OK";
	td.saveCallback(reqObj);
}

/********************************* utilities *************************************/

/**
 * Dummy debug 
 */
function debug(value)
{
}
