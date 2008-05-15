/*	Script: news.story.picklet.js
		This is a <Picklet> for the <ProductPicker> class that returns News.com Stories for a given keyword.
		
		Author:
		Aaron Newton <aaron [dot] newton [at] cnet [dot] com>
		
		Dependencies:
		Everything listed in <product.picker.js>

		Note:
		Add the className to any input and then create a new <FormPickers> and these 
		will automatically be applied. See <ProductPicker.add> on how to add your own.
		
		Property: NewsStoryPicker
		A simple query search for News.com stories.
	*/
var NewsStoryPickerBase = {
	descriptiveName: 'News.com Story Picker',
	url: 'http://internal-api.cnet.com/restApi/v1.0/newsStorySearch',
	callBackKey: 'callback', //see <JsonP> options
	previewWidth: 300,
	data: {
		partKey: '19926949750937665684988687810562', //this is my code - aaron newton
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
			tip: 'news story search::input a keyword and hit &lt;enter&gt; to get results',
			value: '',
			style: {
				width: '100%'
			}
		}/*	,
		orderBy: {
			tagName: 'select',
			type: 'select',
			tip: 'order by the date of the story or by most relevent',
			value: ['PublishDate','default'],
			optionNames: ['date', 'most relevant']
		}	*/
	}, //form builder
	previewHtml: function(data){
		var editors = "";
		var page = data.StoryText.Page;
		var html = '<div class="dataId" style="color: #999; font-weight:bold; margin: 0px; padding: 0px;">id: '+data['@id'] +'</div>'+
			'<div class="dataDetails" style="font-size: 10px;"><b style="font-size: 12px"><a href="http://news.com.com/'+ data.StoryURL.$ +'">' + page.Headline.$ + '</a></b><br/>('+data.PublishDate.$+')';
			html += "<br/><p class=\"description\">" + page.Description.$ + "</p>";
		html += "</div></div>";
		return html;
	}, //html template for returned json data
	resultsList: function(results){
		try {
			if(results.CNETResponse.NewsStories && results.CNETResponse.NewsStories.NewsStory) {
				if($type(results.CNETResponse.NewsStories.NewsStory) == "array") 
					return results.CNETResponse.NewsStories.NewsStory;
				else return [results.CNETResponse.NewsStories.NewsStory];
			}
		} catch(e){ dbug.log('news search error: ', e) }
		return false;
	},
	listItemName: function(data){
		if(data.PublishDate.$){
			var page = ($type(data.StoryText.Page) == "array")?data.StoryText.Page[0]:data.StoryText.Page;
			var date = data.PublishDate.$.split(' ')[0].split('-');
			date = date[1]+'.'+date[2]+'.'+date[0].substring(2);
			return  page.Headline.$ + ' (' + date +')';
		}
		try {
			return data.StoryText.Page.Headline.$;
		}catch(e){
			dbug.log('error returning name: ', e);
			return;
		}
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
var NewsStoryPicker = new Picklet('NewsStoryPicker',NewsStoryPickerBase);
ProductPicker.add(NewsStoryPicker);

/*	Class: NewsStoryPicker_Path
		Extends <NewsStoryPicker> to return a path to the story instead of the id.
	*/
var NewsStoryPicker_Path = new Picklet('NewsStoryPicker_Path',$merge(NewsStoryPickerBase, {
		descriptiveName: 'News.com Story Picker: Story URL',
		updateInput: function(input, data) {
			var url = data.StoryURL.$;
			if (url.indexOf("?")>=0) url = url.substring(0,url.indexOf("?"));
			input.value = 'http://news.com.com/'+url;
			input.fireEvent('change');
		}
	}));
ProductPicker.add(NewsStoryPicker_Path);


/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.implementations/picklets/news.story.picklet.js,v $
$Log: news.story.picklet.js,v $
Revision 1.3  2007/05/17 19:46:35  newtona
fixed name selection for changes in the API

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
