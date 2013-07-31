var $permissions = null;
var $controllers = new Array();

/**
 * Begins the app. Sets the $permissions global variable and calls the init() function
 */
$('document').ready(function() {
    
    $permissions = $.parseJSON($('#permissionList').val());
    
    init();
});

/**
 * Sets up the $controllers data structure. Also binds a change handler to the role checkboxes and
 * the tipsy hover handler to the table cells 
 */
function init() {
    //$('td.description').tipsy({gravity: 'e', fade: true});
    
    createControllers();
    
    $('#role').change(function() {
        var roles = new Array();
        
        $.each($('#role option:selected'), function() {
            roles.push($(this).val());
        });           
                
        updateTables(roles);
    }).change();
}

/**
 * Populates the controller data structure with the passed controllers
 */
function createControllers() {
    $.each($permissions, function(moduleKey, module){
        $.each(module, function(controllerKey, controller){
            var newController = new Controller(moduleKey, controllerKey);
            
            var accessCount = 0;
            $.each(controller['part'], function(actionKey, action){
                accessCount += action['access'];
                var newAction = new Action(moduleKey, controllerKey, actionKey, action['access'] ? 'access' : 'noAccess');
                newController.addAction(newAction);
            });
            
            if(accessCount == 0) {
                newController.disableTooltip();
            }
            
            $controllers.push(newController);
        });
    });
}

/**
 * Makes an ajax call with the selected roles and updates the controllers and actions
 * 
 * @param roles The new roles to merge together
 */
function updateTables(roles) {
        
    $.getJSON($('#baseUrl').val() + '/ot/account/get-permissions', {'roles' : roles}, function(data) {
        $.each($controllers, function(i, controller){
            controller.update(data[controller.module][controller.name]['part']);
        });
    }).error(function(data){
    	error = JSON.parse(data.responseText);
    	alert('An error occurred' + (error.message ? ': ' + error.message : '.'))
    });
}

/**
 * The Controller class. Keeps track of a given controller's action and status.
 *   
 * @param module The name of the module the controller is apart of. Since controller 
 *             names aren't unique, we must use the module name to keep track
 *             of the controllers
 * @param name The name of the controller
 */
function Controller (module, name) {
    
    this.module = module;
    this.name = name;
    this.status = 'noAccess';
    this.actions = new Array();
    this.$object = $('#' + this.module + '-' + this.name);
    this.tooltip = this.$object.tooltip({
        cancelDefault : false,
        tip : '#wrapper-' + this.module + '-' + this.name,
        position : 'center, right',
        offset : [15,15],
        predelay : 250,
        delay : 250
    });
    
    /**
     * The main function of the Controller. Updates the Controller's actions' statuses
     * and maintains the Controller's own status accordingly.
     */
    this.update = function(actions) {
        
        var changeCount = 0;
        var accessCount = 0;
        
        $.each(this.actions, function(i, action){
            changeCount += action.update(actions[action.name]['access']);
            accessCount += action.getStatus() == 'access';
        });
        
        // if there are changes, redraw() must be called
        if(changeCount > 0) {
            
            /*
             * The status of the Controller is determnined by the number 
             * of accessible actions enclosed in the Controller
             */
            if(accessCount > 0) {
                this.status = accessCount == this.actions.length ? 'access' : 'someAccess';
                this.enableTooltip();
            } else {
                this.status = 'noAccess';
                this.disableTooltip();
            }
            
            this.redraw();
        }
    }
    
    this.redraw = function() {
        $(this.$object).removeClass('access noAccess someAccess').addClass(this.status);
    }
    
    this.addAction = function(action) {
        this.actions.push(action);
    }
    
    this.enableTooltip = function() {
        $('#tooltip-' + this.module + '-' + this.name).show();
    }
    
    this.disableTooltip = function() {
        $('#tooltip-' + this.module + '-' + this.name).hide();
    }
}

/**
 * The Action class that represents an atomic unit in the Controller Class.
 * Keeps track of the user's access to a given action in the controller.
 * 
 * @param module The name of the Module 
 *         - Since controller and action names are not unique, we must keep track 
 *           of the module to be able to reference a given action.
 * @param controller The name of the Controller
 * @param name The name of the Action
 * @param status The access attributed to the action
 * @return
 */
function Action (module, controller, name, status) {
    this.name = name;
    this.$object = $('#' + module + '-' + controller + '-' + name );  
    this.status = status;
    
    this.update = function(status) {
        
        var newStatus = status ? 'access' : 'noAccess';
        
        if(this.status != newStatus) {
            this.$object.removeClass(this.status);
            this.status = newStatus;
            this.$object.addClass(this.status);
            return true;
            
        } else {
            return false;
        }
    }
    
    this.getStatus = function() {
        return this.status;
    }
    
}