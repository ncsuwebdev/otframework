var sitePrefix = "";

var alertBox = null;
var mceAreas = [];
window.addEvent('domready', function() {
    
    sitePrefix = $('sitePrefix').value;
    
    tree = new MooTreeControl({
            div: 'fileTree',
            mode: 'folders',
            theme: sitePrefix + "/public/ot/images/mootree.gif",
            grid: true,
            onSelect: function(node, state) {
            
                if (state && node.data.target == 'file') {
                    
                    if (mceAreas.length > 0) {
                        mceAreas.each(function (mceId) {
                            tinyMCE.execCommand('mceRemoveControl', false, mceId);
                        });
                        mceAreas = [];
                    }
                
                    loadFile(node.data.url);
                }
            }
        },
        {
            text: 'Views',
            open: true
        }
    );
    
    tree.adopt('fileTreeData');
    
    $('treeExpandButton').addEvent('click', tree.expand.bind(tree));
    $('treeCollapseButton').addEvent('click', tree.collapse.bind(tree));
    
    $('customTextSaveButton').addEvent('click', function (e) {
         
        tinyMCE.triggerSave();
         
        var varStr = $('customTextForm').toQueryString();
                
        var url = sitePrefix + "/admin/customtext/save-file";
    
        new Ajax(
            url,
            {
                method: 'post',
                data: varStr,
                onComplete: function(txt, xml) {
                    var result = Json.evaluate(txt);
                    $('alertText').setText(result.msg);
                    alertBox.start({'opacity': [0, 1]});                    
                }
            }
        ).request();
    });
    
    alertBox = $('alertText').effects({duration: 200});
    
    $('replacementsDiv').setStyle('display', 'none');

});


function loadFile(path)
{
    var url = sitePrefix + "/admin/customtext/get-file";
    
    $('replacementsContent').empty();
    
    $('replacementsDiv').setStyle('display', 'none');
    $('alertText').setStyle('visibility', 'hidden');
    
    $('path').value = path;
    
    var varStr = Object.toQueryString({path: path});
    new Ajax(
        url,
        {
            method: 'post',
            data: varStr,
            onComplete: function(txt, xml) {
                var editableData = Json.evaluate(txt);
                
                if (editableData == 0) {
                    $('alertText').setText('No editable text found');
                    alertBox.start({'opacity': [0, 1]});
                    return true;
                } else if (editableData == -1) {
                    $('alertText').setText('Error: Multiple files found.  This should not be!');
                    alertBox.start({'opacity': [0, 1]});
                    return true;
                }
                
                var replacementsContent = $('replacementsContent');
                
                var dropDownP = new Element('p');
                dropDownP.addClass('editableDropDownContainer');
                
                var dropDown = new Element('select');
                dropDown.addClass('editableDropDown');
                dropDown.id = 'editableDropDown';
                
                dropDown.addEvent('change', function(e) {
                
                    $('replacementsContent').getElements('div').each(function (el) {
                        el.setStyle('display', 'none');
                    });
                    
                    $(this.options[this.options.selectedIndex].value).setStyle('display', 'block');
                });
                
                var dropDownLabel = new Element('label');
                dropDownLabel.setText('Select section to edit: ');
                
                dropDownP.adopt(dropDownLabel);
                dropDownP.adopt(dropDown);
                
                dropDownP.injectTop(replacementsContent);
                
                
                $each(editableData, (function(item, index) {
                
                    var opt = new Element('option');
                    opt.setText(index);
                    opt.value = index + "Div";
                    opt.injectInside(dropDown);
                 
                    var editDiv = new Element('div');
                    editDiv.addClass('editDiv');
                    editDiv.id = index + "Div";
                    
                    titleP = new Element('p');
                    titleP.addClass('title');
                    titleP.setText(index);
                    editDiv.adopt(titleP);
                    
                    var resetP = new Element('p');
                    resetP.addClass('reset');
                    var resetCheckbox = new Element('input');
                    resetCheckbox.name = 'varsToReset[]';
                    resetCheckbox.value = index;
                    resetCheckbox.id = index;
                    resetCheckbox.type = 'checkbox';
                    resetCheckbox.addClass('resetCheckbox');
                    
                    if (!$defined(item.custom)) {
                        resetCheckbox.checked = true;
                    }
                    
                    resetP.adopt(resetCheckbox);
                    resetP.appendText('Use original text');
                                        
                    editDiv.adopt(resetP);
                    
                    var pOriginal = new Element('p');
                    pOriginal.addClass('original');
                    
                    var textArea = new Element('textarea');
                    textArea.value = item.original;
                    textArea.setProperty('wrap', 'off');
                    textArea.setProperty('readonly', 'true');
                    pOriginal.adopt(textArea);
                    editDiv.adopt(pOriginal);
                    
                    var br = new Element('br');
                    editDiv.adopt(br);
                    
                    var pCustom = new Element('p');
                    pCustom.addClass('custom');
                    var textArea = new Element('textarea');
                    textArea.id = index + "Custom";
                    textArea.name = index;
                    
                    textArea.setStyle('width', '100%');
                    textArea.setStyle('height', '300px');
                    
                    if ($defined(item.custom)) {
                        textArea.value = item.custom;
                    } else {
                        textArea.value = " ";
                    }
                                                              
                                        
                    textArea.setProperty('wrap', 'off');
                    pCustom.adopt(textArea);
                    editDiv.adopt(pCustom);
                    
                    editDiv.setStyle('display', 'none');
                    
                    replacementsContent.adopt(editDiv);   
                    
                    mceAreas.include(textArea.id);
                    
                    tinyMCE.execCommand('mceAddControl', true, textArea.id);
                                   
                }));
                
                $('replacementsDiv').setStyle('display', 'block');
                
                $('editableDropDown').fireEvent('change');
            }
        }
    ).request();
}