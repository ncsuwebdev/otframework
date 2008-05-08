/*	Script: cnet.product.picklet.js
		This is a <Picklet> for the <ProductPicker> class that returns CNET Products for a given keyword.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Everything listed in <product.picker.js>

		Note:
		Add the className to any input and then create a new <FormPickers> and these 
		will automatically be applied. See <ProductPicker.add> on how to add your own.
		
		Property: CNETProductPicker
		A simple query search for CNET Products (electronics, computers, software, etc.).
	*/
var CNETProductPickerBase = {
	previewWidth: 150,
	descriptiveName: 'CNET Product Picker',
	url: 'http://api.cnet.com/restApi/v1.0/techProductSearch',
	callBackKey: 'callback', //see <JsonP> options
	data: {
		partKey: '19926949750937665684988687810562', //this is my code - aaron newton
		iod: 'hlPrice',
		viewType: 'json'
	}, //static data
	getQuery: function(data){ //return <Ajax> or <JsonP>
		//clean any url encoding from the data, as JsonP encodes it again
		$each(data, function(val, key) { data[key] = unescape(val); });
		return new JsonP(this.options.url, {
			callBackKey: this.options.callBackKey,
			data: $merge(this.options.data, data)
		});
	},
	inputs: {
		query: {
			tagName: 'input',
			type: 'text',
			instructions: '',
			tip: 'cnet product search::input a product name and hit &lt;enter&gt; to get results',
			value: '',
			style: {
				width: '100%'
			}
		}
	}, //form builder
	previewHtml: function(data){
		var editors = "";
		var html = '<div class="dataId" style="color: #999; font-weight:bold; margin: 0px; padding: 0px;">id: '+data['@id'] +'</div>'+
						'<div class="dataDetails" style="font-size: 10px;"><a href="'+ data.ReviewURL.$ +'"><img height="45" width="'+data.ImageURL[0]["@width"]+'" style="margin-left: 10px" src="'
							+data.ImageURL[1].$+'"/></a><br /><b><a href="'+ data.ReviewURL.$ +'">' + data.Name.$ + '</a></b>';
		if(data.EditorsRating && data.EditorsRating.$) 
			html += "<br/>editors' rating: "+data.EditorsRating.$;
		html += "<div>";
		if(data.LowPrice && data.LowPrice.$) html += 
			"<span class='productPickerPrices'>"+data.LowPrice.$ +"</span>";
		if(data.HighPrice && data.HighPrice.$ && (data.LowPrice.$ != data.HighPrice.$))
				html += " to <span class='productPickerPrices'>"+data.HighPrice.$ +"</span>";
		html += "</div></div>";
		html += "<div>";
		if(data.Offers && data.Offers['@numFound'] > 0) 
			html += "resellers: " + data.Offers["@numFound"];
		html += "</div>";
		return html;
	}, //html template for returned json data
	resultsList: function(results){
		if(results.CNETResponse.TechProducts && results.CNETResponse.TechProducts["@numFound"] > 0) {
			if(results.CNETResponse.TechProducts["@numFound"] > 1) return results.CNETResponse.TechProducts.TechProduct;
			else return [results.CNETResponse.TechProducts.TechProduct];
		}
		return false;
	},
	listItemName: function(data){
		return data.Name.$
	}, //line item name for the selection list
	listItemValue: function(data){
		return data['@id'];
	},
	//handle the click event; user chooses an item, and this function updates the input 
	//(or does something else)
	updateInput: function(input, data) {
		input.value = data['@id'];
		input.fireEvent('change');
	}	
};
	
var CNETProductPicker = new Picklet('CNETProductPicker',CNETProductPickerBase);
ProductPicker.add(CNETProductPicker);

/*	Class: CNETProductPicker_ReviewPath 
		Extends <CNETProductPicker> to return a path to the review instead of the id.
 */
var CNETProductPicker_ReviewPath = new Picklet('CNETProductPicker_ReviewPath', $merge(CNETProductPickerBase, {
		descriptiveName: 'CNET Product Picker: Review URL',
		updateInput: function(input, data) {
			var url = data.ReviewURL.$;
			if (url.indexOf("?")>=0) url = url.substring(0,url.indexOf("?"));
			input.value = url;
			input.fireEvent('change');
		}
	})
);
ProductPicker.add(CNETProductPicker_ReviewPath);
/*	Class: CNETProductPicker_PricePath 
		Extends <CNETProductPicker> to return a path to the price page instead of the id.
 */
var CNETProductPicker_PricePath = new Picklet('CNETProductPicker_ReviewPath', $merge(CNETProductPickerBase, {
		descriptiveName: 'CNET Product Picker: Price URL',
		updateInput: function(input, data) {
			var url = data.PriceURL.$;
			if (url.indexOf("?")>=0) url = url.substring(0,url.indexOf("?"));
			input.value = url;
			input.fireEvent('change');
		}
	})
);
ProductPicker.add(CNETProductPicker_PricePath);
/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.implementations/picklets/cnet.product.picklet.js,v $
$Log: cnet.product.picklet.js,v $
Revision 1.2  2007/05/16 20:09:45  newtona
adding new js files to redball.common.full
product.picker.js now has no picklets; these are in the implementations/picklets directory
ProductPicker now detects if there is no doctyp and, if not, sets the position of the picker to be fixed (no IE6 support)
small docs update in element.cnet.js
added new picklet: CNETProductPicker_PricePath
added new picklet: NewsStoryPicker_Path
new file: clipboard.js (allows you to insert text into the OS clipboard)
new file: html.table.js (automates building html tables)
new file: element.forms.js (for managing text inputs - get selected text information, insert content around selection, etc.)

Revision 1.1  2007/05/10 00:21:05  newtona
moved from product.picker.js


*/
