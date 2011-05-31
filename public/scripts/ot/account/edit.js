var $permissions = null;
var $controllers = new Array();

$('document').ready(function() {
	
	$permissions = $.parseJSON($('#permissionList').val());
	
	init();
	createControllers();
});

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
		delay : 250,
	});
	
	this.update = function(actions) {
		
		var changeCount = 0;
		
		var accessCount = 0;
		$.each(this.actions, function(i, action){
			
			changeCount += action.update(actions[action.name]['access']);
			accessCount += action.getStatus() == 'access';
		});
		
		if(changeCount > 0) {
			if(accessCount == this.actions.length) {
				this.status = 'access';
				this.enableTooltip();
				this.redraw();
			} else if(accessCount > 0) {
				this.status = 'someAccess';
				this.enableTooltip();
				this.redraw();
			} else {
				this.status = 'noAccess';
				this.disableTooltip();
				this.redraw();
			}
		}
	}
	
	this.redraw = function() {
		var newStatus = this.status;
		$('td', this.$object)
			.fadeOut(500)
			.removeClass('access noAccess someAccess')
			.addClass(newStatus)
			.fadeIn(500);
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

function Action (module, controller, name, status) {
	this.name = name;
	this.$object = $('#' + module + '-' + controller + '-' + name );  
	this.status = status;
	
	this.update = function(status) {
		var newStatus = status ? 'access' : 'noAccess';
		if(this.status != newStatus) {
			this.status = newStatus;
			this.$object.removeClass('access noAccess');
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

function updateTables(roles) {
	$.getJSON($('#baseUrl').val() + '/ot/account/get-permissions', {'roles' : roles}, function(data) {
		$.each($controllers, function(i, controller){
			controller.update(data[controller.module][controller.name]['part']);
		});
	});
}

function init() {
	$('.description').tipsy({gravity: 'e', fade: true});
    $("#access").tabs({
    	selected:0
    });
    
    $('input.roleSelect').change(function() {
    	var roles = new Array();
    	
    	$.each($('input.roleSelect:checked'), function() {
    		roles.push($(this).val());
    	});
    	
    	updateTables(roles);
    });
}