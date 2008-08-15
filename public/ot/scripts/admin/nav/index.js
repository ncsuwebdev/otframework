var sitePrefix = "";
var navTree = null;
var alertFx = null;

window.addEvent('domready', function() {
    
    sitePrefix = $('sitePrefix').value;
    var saveUrl = sitePrefix + "/admin/nav/save";
    
    alertFx = new Fx.Styles($('alert'), {
            duration: 3000,
            wait: false,
            transition: Fx.Transitions.Quad.easeOut
        });
    
    navTree = new Nested('navMenuContainer',
                {
                    onComplete: function(el) {
                        var depth = this.getDepth(el);
                        if (depth == 0) {
                            el.removeClass('subNavItem');
                            el.addClass('navItem');
                        } else {
                            el.removeClass('navItem');
                            el.addClass('subNavItem');
                        }
                        
                    }
                });
                
    $('addElementButton').addEvent('click', function(e) {
        var li = new Element('li');
        li.title = '{"module":"","controller":"","action":"","display":"New Element","link":""}';
        li.addClass('navItem');
        var span = new Element('span');
        span.setText('New Element');
        li.adopt(span);
        li.injectTop($('navMenuContainer'));
    });
    
    $('resetNavButton').addEvent('click', function(e) {
        window.location = window.location;
    });
   
    $('saveNavButton').addEvent('click', function(e) {
        var jsonStr = Json.toString(navTree.serialize());
        var varStr = Object.toQueryString({data: jsonStr});
        
        new Ajax(saveUrl,
                {
                    method: 'post',
                    data: varStr,
                    onComplete: function(txt, xml) {
                        var result = Json.evaluate(txt);
                        if (result.rc == "0") {
                            alert(result.msg);
                        } else {
                            window.location = window.location;
                        }
                    }
                
                }).request();
    });
    
    $('saveElementButton').addEvent('click', function(e) {
        var data = {};
        data.module = $('moduleBox').value;
        data.controller = $('controllerBox').value;
        data.action = $('actionBox').value;
        data.display = $('displayBox').value;
        data.link = $('linkBox').value;
        
        if (data.module == "") {
        	data.module = "default";
        }
        
        if (data.controller == "") {
        	data.controller = "index";
        }
        
        if (data.action == "") {
        	data.action = "index";
        }
        
        var jsonStr = Json.toString(data);
        navTree.lastEl.title = jsonStr;
        navTree.lastEl.getFirst().setText(data.display);
        
        navTree.fx.start({
            'opacity': [1, 0]
        });
        
        $('alert').setText('Element Details Stored!');
        alertFx.start({
            'background-color': ['#fff36f', '#fff'],
            'opacity': [1, 0]
        });
        
    });
    
    $('cancelElementButton').addEvent('click', function(e) {
        navTree.fx.start({
            'opacity': [1, 0]
        });
    });
    
    $('deleteElementButton').addEvent('click', function(e) {
        if (confirm("Are you sure you want to delete this element?")) {
            if (confirm("Are you really sure you want to delete this element?\n\nAny children of this element will be removed as well.\n\nYou cannot undo this action.")) {
                navTree.lastEl.remove();
                
                navTree.fx.start({
                    'opacity': [1, 0]
                });
                
                $('alert').setText('Element Removed Successfully!');
                alertFx.start({
                    'background-color': ['#fff36f', '#fff'],
                    'opacity': [1, 0]
                });
                        
            }
        }
    });
    
});

var Nested = new Class({

    fx: null,
    lastEl: null,

    getOptions: function() {
        return {
            childTag: 'li',
            ghost: true,
            childStep: 30, // attempts to become a child if the mouse is moved this number of pixels right
            handleClass: null, 
            onStart: Class.empty,
            onComplete: Class.empty,
            collapse: false, // true/false
            collapseClass: 'nCollapse', // Class added to collapsed items
            expandKey: 'shift', // control | shift
            lock: null, // parent || depth || class
            lockClass: 'unlocked'
        };
    },

    initialize: function(list, options) {
        this.setOptions(this.getOptions(), options);
        if (!this.options.expandKey.match(/^(control|shift)$/)) {
            this.options.expandKey = 'shift';
        }
        
        this.fx = new Fx.Styles($('navEditor'), {
            duration: 1000,
            wait: false,
            transition: Fx.Transitions.Quad.easeOut
        });
        
        this.list = $(list);
        this.options.parentTag = this.list.nodeName.toLowerCase();
        this.bound = {};
        this.bound.start = this.start.bindWithEvent(this);
        
        this.list.addEvent('dblclick', this.edit.bindWithEvent(this));
        
        this.list.addEvent('mousedown', this.bound.start);
        if (this.options.collapse) {
            this.bound.collapse = this.collapse.bindWithEvent(this);
            //this.list.addEvent('click', this.bound.collapse);
        }
        
        if (this.options.initialize) {
            this.options.initialize.call(this);
        }
    },
    
    edit: function(event) {
        var el = $(event.target);
        
        if (el.getTag() == "span") {
            el = el.getParent();
        }
        
        this.lastEl = el;
        
        if (el.getTag() == 'li') {
        
            var data = Json.evaluate(el.title);
            $('moduleBox').value = data.module;
            $('controllerBox').value = data.controller;
            $('actionBox').value = data.action;
            $('displayBox').value = data.display;
            $('linkBox').value = data.link;
            
            $('navEditor').setPosition({
            relativeTo: el,
            position: 'topRight'
            });
            
            $('navEditor').setStyle('display', 'block');
            this.fx.start({
                'opacity': [0, 1]
            });
        }
    },

    start: function(event) {
        var el = $(event.target);
        
        navTree.fx.start({
            //'background-color': ['#fff36f', '#fff'],
            'opacity': [0, 1]
        });
        $('navEditor').setStyle('display', 'none');
        
        if (this.options.handleClass) {
            while (el.nodeName != this.options.childTag.toLowerCase() && !el.hasClass(this.options.handleClass) && el != this.list) {
                el = el.getParent();
            }
            if (!el.hasClass(this.options.handleClass)) return true;
        } 
        while (el.nodeName.toLowerCase() != this.options.childTag.toLowerCase() && el != this.list) {
            el = el.parentNode;
        }
        if (el.nodeName.toLowerCase() != this.options.childTag.toLowerCase()) return true;
        el = $(el);
        if (this.options.lock == 'class' && !el.hasClass(this.options.lockClass)) return;
        if (this.options.ghost) { // Create the ghost
            this.ghost = el.clone().setStyles({
                'list-style-type': 'none',
                'opacity': 0.5,
                'position': 'absolute',
                'visibility': 'hidden',
                'width': el.getStyle('width'),
                'top': event.page.y+'px',
                'left': (event.page.x)+'px'
            }).injectInside(document.body);
        }
        el.depth = this.getDepth(el);
        el.moved = false;
        this.bound.movement = this.movement.bindWithEvent(this, el);
        this.bound.end = this.end.bind(this, el);
        this.list.removeEvent('mousedown', this.bound.start);
        this.list.addEvent('mousedown', this.bound.end);
        this.list.addEvent('mousemove', this.bound.movement);
        document.addEvent('mouseup', this.bound.end);
        if (window.ie) { // IE fix to stop selection of text when dragging
            this.bound.stop = this.stop.bindWithEvent(this);
            $(document.body).addEvent('drag', this.bound.stop).addEvent('selectstart', this.bound.stop);
        }
        this.fireEvent('onStart', el);
        event.stop();
    },

    collapse: function(event) {
        var el = $(event.target);
        if (this.options.handleClass) {
            while (el.nodeName.toLowerCase() != this.options.childTag.toLowerCase() && !el.hasClass(this.options.handleClass) && el != this.list) {
                el = el.getParent();
            }
            if (!el.hasClass(this.options.handleClass)) return true;
        } 
        while (el.nodeName.toLowerCase() != this.options.childTag.toLowerCase() && el != this.list) {
            el = el.parentNode;
        }
        if (el == this.list) return;
        el = $(el);
        if (!el.moved) {
            var sub = $E(this.options.parentTag.toLowerCase(), el);
            if (sub) {
                if (sub.getStyle('display') == 'none') {
                    sub.setStyle('display', 'block');
                    el.removeClass(this.options.collapseClass);
                } else {
                    sub.setStyle('display', 'none');
                    el.addClass(this.options.collapseClass);
                }
            }
        }
        event.stop();
    },
    
    stop: function(event) {
        event.stop();
        return false;
    },
    
    getDepth: function(el, add) {
        var counter = (add) ? 1 : 0;
        while (el != this.list) {
            if (el.nodeName.toLowerCase() == this.options.parentTag.toLowerCase()) counter += 1;
            el = el.parentNode;
        }
        return counter;
    },
    
    movement: function(event, el) {
        var dir, over, check, items;
        var dest, move, prev, prevParent;
        var abort = false;
        if (this.options.ghost && el.moved) { // Position the ghost
            this.ghost.setStyles({
                'position': 'absolute',
                'visibility': 'visible',
                'top': event.page.y+'px',
                'left': (event.page.x)+'px'
            });
        }
        
        if (this.getDepth(el) == 0) {
            el.removeClass('subNavItem');
            el.addClass('navItem');
        } else {
            el.removeClass('navItem');
            el.addClass('subNavItem');
        }
        
        over = event.target;
        while (over.nodeName.toLowerCase() != this.options.childTag.toLowerCase() && over != this.list) {
            over = over.parentNode;
        }
        if (over == this.list) return;
        if (event[this.options.expandKey] && over != el && over.hasClass(this.options.collapseClass)) {
            check = $E(this.options.parentTag.toLowerCase(), over);
            over.removeClass(this.options.collapseClass);
            check.setStyle('display', 'block');
        }
        // Check if it's actually inline with a child element of the event firer
        orig = over;
        if (el != over) {
            items = $ES(this.options.childTag.toLowerCase(), over);
            items.each(function(item) {
                if (event.page.y > item.getTop() && item.offsetHeight > 0) over = item;
            });
        }
        // Make sure we end up with a childTag element
        if (over.nodeName.toLowerCase() != this.options.childTag.toLowerCase()) return;
            
        // store the previous parent 'ol' to remove it if a move makes it empty
        prevParent = el.getParent();
        dir = (event.page.y < el.getTop()) ? 'up' : 'down';
        move = 'before';
        dest = el;

        if (el != over) {
            check = over;
            while (check != null && check != el) {
                check = check.parentNode;
            } // Make sure we're not trying to move something below itself
            if (check == el) return;
            if (dir == 'up') {
                move = 'before'; dest = over;
            } else {
                sub = $E(this.options.childTag.toLowerCase(), over);
                if (sub && sub.offsetHeight > 0) {
                    move = 'before'; dest = sub;
                } else {
                    move = 'after'; dest = over;
                }
            }
        }

        // Check if we're trying to go deeper -->>
        prev = (move == 'before') ? dest.getPrevious() : dest;
        if (prev) {
            move = 'after';
            dest = prev;
            check = $E(this.options.parentTag.toLowerCase(), dest);
            while (check && event.page.x > check.getLeft() && check.offsetHeight > 0) {
                dest = check.getLast();
                check = $E(this.options.parentTag.toLowerCase(), dest);
            }
            if (!check && event.page.x > dest.getLeft()+this.options.childStep) {
                move = 'inside';
            }
        }

        last = dest.getParent().getLast();
        while (((move == 'after' && last == dest) || last == el) && dest.getParent() != this.list && event.page.x < dest.getLeft()) {
            move = 'after';
            dest = $(dest.parentNode.parentNode);
            last = dest.getParent().getLast();
        }
        
        abort = false;
        if (move != '') {
            abort += ((el.getElements('ul').length > 0) && (this.getDepth(dest, (move == 'inside')) > 0));
            abort += (this.getDepth(dest, (move == 'inside')) > 1);
            abort += (dest == el);
            abort += (move == 'after' && dest.getNext() == el);
            abort += (move == 'before' && dest.getPrevious() == el);
            abort += (this.options.lock == 'depth' && el.depth != this.getDepth(dest, (move == 'inside')));
            abort += (this.options.lock == 'parent' && (move == 'inside' || dest.parentNode != el.parentNode));
            abort += (dest.offsetHeight == 0);
            sub = $E(this.options.parentTag.toLowerCase(), over);
            sub = (sub) ? sub.getTop() : 0;
            sub = (sub > 0) ? sub-over.getTop() : over.offsetHeight;
            abort += (event.page.y < (sub-el.offsetHeight)+over.getTop());
            if (!abort) {
                if (move == 'inside') dest = new Element(this.options.parentTag.toLowerCase()).injectInside(dest);
                $(el).inject(dest, move);
                el.moved = true;
                if (!prevParent.getFirst()) prevParent.remove();
            }
        }
        event.stop();
    },

    detach: function() {
        this.list.removeEvent('mousedown', this.start.bindWithEvent(this));
        if (this.options.collapse) this.list.removeEvent('click', this.bound.collapse);
    },

    serialize: function(listEl) {
        var serial = [];
        var kids;
        if (!listEl) listEl = this.list;
        $$(listEl.getChildren()).each(function(node, i) {
            kids = $E(this.options.parentTag.toLowerCase(), node);
            serial[i] = {
                id: node.id,
                title: (node.title) ? node.title : "",
                children: (kids) ? this.serialize(kids) : []
            };
        }.bind(this));
        return serial;
    },

    end: function(el) {
        if (this.options.ghost) this.ghost.remove();
        this.list.removeEvent('mousemove', this.bound.movement);
        document.removeEvent('mouseup', this.bound.end);
        this.list.removeEvent('mousedown', this.bound.end);
        this.list.addEvent('mousedown', this.bound.start);
        this.fireEvent('onComplete', el);
        if (window.ie) $(document.body).removeEvent('drag', this.bound.stop).removeEvent('selectstart', this.bound.stop);
    }
});

Nested.implement(new Events);
Nested.implement(new Options);