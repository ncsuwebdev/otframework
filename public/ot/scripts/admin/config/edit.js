window.addEvent('domready', function() {

    /* Grid Tool Tips */
    var tips = new Tips($$('.description'), {

        className: 'descriptionTab',

        initialize:function(){
            this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 500, wait: false}).set(0);
        },

        onShow: function(toolTip) {
            this.fx.start(1);
        },

        onHide: function(toolTip) {
            this.fx.start(0);
        }
    });
});