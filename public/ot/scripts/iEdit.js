var iEdit = new Class({
    element: null,
    
    target: null,
    
    params: null,
    
    bTextareaWasTinyfied: false,
    
	initialize: function(el) {
	   
	    this.element = el;
	    this.target  = $(el.getProperty('target'));
	    this.params = this.parseQuery(this.target.getProperty('rel'));
        this.inlineEdit(this.target);	
	},
	    
    cleanup: function() {
    },
    
    onSaveComplete: function() {
    },
    
    save: function(modifiedEl) {

        this.target.style.display = '';
        this.target.innerHTML = modifiedEl.value;    
        
        var pars = modifiedEl.getProperty('name') + '=' + escape(modifiedEl.value);
        var url = this.params['url'];

        $ES('.' + modifiedEl.getProperty('name'), '.postArgs').each( function(arg) {
            if (pars != '') {
                pars += '&';
            }
            pars += arg.getProperty('name') + '=' + escape(arg.getProperty('value'));
        });  
        
        var updateEl = $(this.params['response']);
                
        new Ajax( url, {
            method: 'post',
            postBody: pars,
            update: updateEl,
            onComplete: function(){   
                var responseFade = new Fx.Style(updateEl.getProperty('id'), 'opacity', {
                    duration:2000
                });
                responseFade.start.pass([1,0],responseFade).delay(1000);
            }
        }).request();                
        
        this.onSaveComplete();
    },
    
	inlineEdit: function(el) {
	    if (this.params['type'] == 'textarea') {                
	        var rel = el.getProperty('rel');
	                            
	        el.style.display = 'none';
	        var content = el.innerHTML;
	        var container = new Element('div');
            container.injectAfter(el);
	
	        //check params are suitable or set base attributes
	        if (this.params['rows'] == undefined) {
	            this.params['rows'] = '15';
	        }
	        
	        if (this.params['cols'] == undefined) {
	            this.params['cols'] = '65';
	        }
	
	        var txtID = 'mce_' + el.getProperty('id');
	                                    
	        //textarea
	        var textarea = new Element('textarea');
	        textarea.value = content;
	        textarea.setProperty('class', 'mceEditor');
	        textarea.setProperty('id', txtID);
	        textarea.setProperty('name', el.getProperty('id'));
	        textarea.setStyle('position','relative');                                       
	        textarea.setProperties({
	            rows: this.params['rows'],
	            cols: this.params['cols']
	        });
            
            textarea.injectInside(container);
	                                              
	        this.setTextareaToTinyMCE(txtID);
	                                                
	        //save + cancel div
	        var saveDiv = new Element('div');
	        saveDiv.setProperty('class', 'txtAreaSave');
            saveDiv.injectInside(container);
	                                                                       
            //cancel
            var cancel = new Element('input');
            cancel.setProperty('type', 'button');
            cancel.setProperty('value', 'Cancel');
            cancel.injectInside(saveDiv);
            
            var br = new Element('br');
            br.injectBefore(cancel);
                         
            var span = new Element('span');
            span.innerHTML = '  ';
            span.injectBefore(cancel);
            
            //save
            var save = new Element('input');
            save.setProperty('type', 'button');
            save.setProperty('value', 'Save');
            save.injectInside(saveDiv);
	                  
	        obj = this;
	                      
	        save.addEvent('click', function() {
	            obj.unsetTextareaToTinyMCE(txtID);                 
	            textarea.remove();
	            this.remove();
	            cancel.remove();
	            span.remove();
	            saveDiv.remove();
	            obj.save(textarea);
	            obj.cleanup();
	        });
	                                
	        cancel.addEvent('click', function() {
	            obj.unsetTextareaToTinyMCE(txtID);
	            el.style.display = 'block';
	            textarea.remove();
	            this.remove();
	            save.remove();
	            span.remove();
	            saveDiv.remove();
	            obj.cleanup();
	        }); 
	    }
	    
	    if(this.params['type'] == 'input'){  
            el.style.display = 'none';
    
            var content = el.innerHTML;
            var container = el.parentNode;
                                        
            //input
            var textarea = new Element('input');
            textarea.value = content;
            textarea.setProperty('name', el.getProperty('id'));
            
            if (this.params['size'] == undefined) {
                textarea.setProperty('size', 20);
            } else {
                textarea.setProperty('size', this.params['size']);
            }
            
            textarea.injectInside(container);
    
            //save + cancel div
            var saveDiv = new Element('span');
            saveDiv.setProperty('class', 'txtAreaSave');
            saveDiv.injectInside(container);
            
            //cancel
            var cancel = new Element('input');
            cancel.setProperty('type', 'button');
            cancel.setProperty('value', 'Cancel');
            cancel.injectInside(saveDiv);
            
            var span = new Element('span');
            span.innerHTML = '  ';
            span.injectBefore(cancel);
            
            //save
            var save = new Element('input');
            save.setProperty('type', 'button');
            save.setProperty('value', 'Save');
            save.injectInside(saveDiv);
            
            obj = this;
            
            save.addEvent('click', function() {
                el.style.display = '';
                el.innerHTML = textarea.value;                                   
                textarea.remove();
                this.remove();
                cancel.remove();
                span.remove();
                saveDiv.remove();
                obj.save(textarea);
                obj.cleanup();
            });
            
            cancel.addEvent('click', function() {
                el.style.display = '';
                textarea.remove();
                this.remove();
                save.remove();
                span.remove();
                saveDiv.remove();
                obj.cleanup();
            });     
	    } 
	    
	    if (this.params['type'] == 'tags') {
           el.style.display = 'none';
    
            var content = el.innerHTML;
            content = content.replace(/^\s+|\s+$/img, '');
            content = content.replace(/<\/a>[^<]*/img, ', ').replace(/(<([^>]+)>)/img,"");

            if (content == 'None') {
                content = '';
            } else if (content[content.length - 2] != ',') {
                content += ', ';
            }            
            
            var container = el.parentNode;
                                        
            //input
            var textarea = new Element('input');
            textarea.value = content;
            textarea.setProperty('name', el.getProperty('id'));
            
            if (this.params['size'] == undefined) {
                textarea.setProperty('size', 20);
            } else {
                textarea.setProperty('size', this.params['size']);
            }
            
            textarea.injectInside(container);
    
            //save + cancel div
            var saveDiv = new Element('span');
            saveDiv.setProperty('class', 'txtAreaSave');
            saveDiv.injectInside(container);
            
            //cancel
            var cancel = new Element('input');
            cancel.setProperty('type', 'button');
            cancel.setProperty('value', 'Cancel');
            cancel.injectInside(saveDiv);
            
            var span = new Element('span');
            span.innerHTML = '  ';
            span.injectBefore(cancel);
            
            //save
            var save = new Element('input');
            save.setProperty('type', 'button');
            save.setProperty('value', 'Save');
            save.injectInside(saveDiv);
            
            obj = this;
            
		    var sitePrefix = $('sitePrefix').value;
		    
		    var myCompleter = new Autocompleter.Ajax.Json(textarea, sitePrefix + '/index/autoSuggest/', {
		        'postVar' : 'search',		    
		        'multi' : true
		    });            
            
            save.addEvent('click', function() {
                el.style.display = '';
                var tags = textarea.value.split(',');
                el.innerHTML = textarea.value;                                   
                textarea.remove();
                this.remove();
                cancel.remove();
                span.remove();
                saveDiv.remove();
                obj.save(textarea);
                obj.cleanup();
                
                obj.target.innerHTML = '';
                for (var i = 0; i < tags.length; i++) {
                    if (tags[i].replace(/^\s+|\s+$/g, '') != '') {
	                    var a = new Element('a');
	                    a.setProperty('href', sitePrefix + '/index/search/?search=' + tags[i].replace(/^\s+|\s+$/g, ''));
	                    a.innerHTML = tags[i].replace(/^\s+|\s+$/g, '');
                        a.injectInside(obj.target);
	                }
                }
                
            });
            
            cancel.addEvent('click', function() {
                el.style.display = '';
                textarea.remove();
                this.remove();
                save.remove();
                span.remove();
                saveDiv.remove();
                obj.cleanup();
            });     	    
	    }
	    
        if (this.params['type'] == 'link') {
            el.style.display = 'none';
            
            var a = $E('a', el);
            
            var link = a.getAttribute('href');
            var name = a.innerHTML;
            
            var container = new Element('div');
            container.addClass('linkForm');
            container.injectAfter(el);
            
            var form = new Element('form');
            form.method = 'POST';
            form.action = this.params['url'];
            form.id = 'newLinkForm';
            form.injectInside(container);
                                        
            var hidden = new Element('input');
            hidden.addClass('postArgs');
            hidden.addClass(el.id);
            hidden.type = 'hidden';
            hidden.name = 'workshopLinkId';
            hidden.value = el.id.replace(/^[^_]*\_/, '');
            hidden.injectInside(form);
            
            //input
            var label1 = new Element('label');
            label1.innerHTML = 'URL: ';
            label1.injectInside(form);
            
            var textarea1 = new Element('input');
            textarea1.value = link;
            textarea1.addClass('postArgs');
            textarea1.addClass(el.id);
            textarea1.setProperty('name', 'url');
            textarea1.id = 'linkUrl';
            textarea1.injectInside(form);
            
            var br = new Element('br');
            br.injectInside(form);
            
            var label2 = new Element('label');
            label2.innerHTML = 'Name: ';
            label2.injectInside(form);
            
            var textarea2 = new Element('input');
            textarea2.value = name;
            textarea2.addClass('postArgs');
            textarea2.addClass(el.id);
            textarea2.setProperty('name', 'name');
            textarea2.id = 'linkName';
            textarea2.injectInside(form);
            
            var br = new Element('br');
            br.injectInside(form);
            
            if (this.params['size'] == undefined) {
                textarea1.setProperty('size', 20);
                textarea2.setProperty('size', 20);
            } else {
                textarea1.setProperty('size', this.params['size']);
                textarea2.setProperty('size', this.params['size']);
            }
    
            //save + cancel div
            var saveDiv = new Element('span');
            saveDiv.setProperty('class', 'txtAreaSave');
            saveDiv.injectInside(form);
            
            //cancel
            var cancel = new Element('input');
            cancel.setProperty('type', 'button');
            cancel.setProperty('value', 'Cancel');
            cancel.injectInside(saveDiv);
            
            var span = new Element('span');
            span.innerHTML = '  ';
            span.injectBefore(cancel);
            
            //save
            var save = new Element('input');
            save.setProperty('type', 'button');
            save.setProperty('value', 'Save');
            save.injectInside(saveDiv);
            
            obj = this;
            
            var sitePrefix = $('sitePrefix').value;

            save.addEvent('click', function() {
                
                el.style.display = '';
                if (!textarea1.value.match(/:\/\//)) {
                    textarea1.value = 'http://' + textarea1.value;
                }
                
                if (textarea2.value == '') {
                    textarea2.value = textarea1.value;
                }
                
                el.innerHTML = '<a href="' + textarea1.value + '" target="_blank">' + textarea2.value + '</a>';
                
                var input = new Element('input');
                input.value = el.id;
                input.name = el.id;
                
                obj.save(input);
                obj.cleanup();
                
                el.innerHTML = '<a href="' + textarea1.value + '" target="_blank">' + textarea2.value + '</a>';
                
                container.remove();                
            });
            
            cancel.addEvent('click', function() {
                container.remove();
                el.style.display = '';
                this.remove();
                obj.cleanup();
            });             
        }	    
	},

    parseQuery: function(query) {
        var Params = new Object ();
        if (!query) { 
            return Params;
        } 
                
        var Pairs = query.split(/[;&]/);
        for (var i = 0; i < Pairs.length; i++) {
            var KeyVal = Pairs[i].split('=');
            if (!KeyVal || KeyVal.length != 2) {
                continue;
            }
            
            var key = unescape( KeyVal[0] );
            var val = unescape( KeyVal[1] );
            val = val.replace(/\+/g, ' ');
            Params[key] = val;
        }           
        
        return Params;
    },	
    
    setTextareaToTinyMCE: function(sEditorID) {
        var oEditor = document.getElementById(sEditorID);
        if(oEditor && !this.bTextareaWasTinyfied) {
            tinyMCE.execCommand('mceAddControl', true, sEditorID);
            this.bTextareaWasTinyfied = true;
        }
        return;
    },
    
    unsetTextareaToTinyMCE: function (sEditorID) {
        var oEditor = document.getElementById(sEditorID);
        if(oEditor && this.bTextareaWasTinyfied) {
            tinyMCE.execCommand('mceRemoveControl', true, sEditorID);
            this.bTextareaWasTinyfied = false;
        }
        return;
    }    
});