Element.extend({
    disableSelection: function(){
        if (window.ie) this.onselectstart = function(){ return false };
        this.style.MozUserSelect = "none";
        return this;
    },

    removeChildren: function() {
        while (this.lastChild) this.removeChild(this.lastChild);
    }

});

Sortables.implement({

    serialize: function(){
        var serial = [];
        this.list.getChildren().each(function(el, i){
            serial[i] = el.id;
        });
        return serial;
    }
});


window.onload = function() {

    if ($('list')) {

        $('listStatus').setStyle('opacity', 0);

        var sortList = new Sortables('list', {
            handles: $$('#list td.order'),
            onStart: function(element) {
                $$('#list table.elm').each(function(el){
                    if (el.hasClass('activeDrag')) {
                        el.removeClass('activeDrag');
                    }
                });

                element.addClass('activeDrag');
            },
            onComplete: function(element){
                $$('#list table.elm').each(function(el,i){

                    el.getFirst().getFirst().getFirst().setHTML(i+1);

                    if (el.hasClass('activeDrag')) {
                        el.removeClass('activeDrag');
                    }
                })

                var queryString = Object.toQueryString({objectId: $('parentIdValue').innerHTML, order: sortList.serialize()})

                new Ajax($('sortUrl').innerHTML, {
                    method: 'post',
                    postBody: queryString,
                    update: $('listStatus'),
                    onComplete: function(){
                        var effect = new Fx.Style('listStatus', 'opacity', {duration:500});
                        effect.start(0, 1);
                        (function() { effect.start(1, 0); }).delay(1500);
                    }
                }).request();
            },

            ghost: true
        });
        $$('#list td.order').each(function(el){
            el.disableSelection();
        });
    }
}