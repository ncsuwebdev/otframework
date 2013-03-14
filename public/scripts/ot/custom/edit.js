$('document').ready(function() {
               
    $('#addElement').click(function() {        
                
        var num = parseInt($('#rowCt').val());
        
        var newRow = $('#optionElement div.controls')
                       .first()
                       .clone()
                       .html()
                       .replace(/value=\"[^"]*\"/ig, 'value=""')
                       .replace(/options\[\d\]/ig, 'options[' + num + ']')
                       .replace(/options\-\d/ig, 'options[' + num + ']')
                       ;
                
        num++;
        
        newRow = $('<div class="controls">'+ newRow + '</div>');
        
        $('#rowCt').val(num);
        
        $('#optionElement').append(newRow);
    });
    
    $(document).on("click", ".removeButton", function(event) {
      
        if ($('#optionElement div.controls').length < 2) {
            alert('You must have at least one option');
        } else {
            $(this).parent().parent().remove();
        }
    });    
});