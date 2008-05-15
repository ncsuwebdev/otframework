/*	
	Script: form.validator.js
	A css-class based form validation system.
	
	Dependencies:
	Mootools - <Moo.js>, <Utility.js>, <Common.js>, <Element.js>, <Function.js>, <Event.js>, <String.js>, <Fx.Base.js>, 
			<Window.Base.js>, <Fx.Style.js>, <Fx.Styles.js>, <Dom.js>
			
	CNET - optional: <Fx.SmoothShow.js>
			
	Authors:
		Aaron Newton, <aaron [dot] newton [at] cnet [dot] com>
		Based on validation.js by Andrew Tetlaw (http://tetlaw.id.au/view/blog/really-easy-field-validation-with-prototype)

	Class: InputValidator
	This class contains functionality to test a field for various criteria and also to generate 
	an error message when that test fails.
	
	Arguments:
	className - a className that this field will be related to (see example below);
	options - an object with name/value pairs.
	
	Options:
	errorMsg - a message to display; see section below for details.
	test - a function that returns true or false
	
	errorMsg:
	The errorMsg option can be any of the following
	
		string - the message to display if the field fails validation
		boolean false - do not display a message at all
		function - a function to evaluate that returns either a string or false.
			This function will be passed two parameters: the field being evaluated and
			any properties defined for the validator as a className (see examples below)
	
	test:
	The test option is a function that will be passed the field being evaluated and
	any properties defined for the validator as a className (see example below); this
	function must return true or false.

	Examples:
(start code)
//html code
<input type="text" name="firstName" class="required" id="firstName">
//simple validator
var isEmpty = new InputValidator('required', {
	errorMsg: 'This field is required.',
	test: function(field){
		return ((element.getValue() == null) || (element.getValue().length == 0));
	}
});
isEmpty.test($("firstName")); //true if empty
isEmpty.getError($("firstName")) //returns "This field is required."

//two complex validators
<input type="text" name="username" class="minLength maxLength" validatorProps="{minLength:10, maxLength:100}" id="username">

var minLength = new InputValidator ('minLength', {
	errorMsg: function(element, props){
		//props is {minLength:10, maxLength:100}
		if($type(props.minLength))
			return 'Please enter at least ' + props.minLength + ' characters (you entered ' + element.value.length + ' characters).';
		else return '';
	}, 
	test: function(element, props) {
		//if the value is >= than the minLength value, element passes test
		return (element.value.length >= $pick(props.minLength, 0));
		else return false;
	}
});

minLength.test($('username'));

var maxLength = new InputValidator ('maxLength', {
	errorMsg: function(element, props){
		//props is {minLength:10, maxLength:100}
		if($type(props.maxLength))
			return 'Please enter no more than ' + props.maxLength + ' characters (you entered ' + element.value.length + ' characters).';
		else return '';
	}, 
	test: function(element, props) {
		//if the value is <= than the maxLength value, element passes test
		return (element.value.length <= $pick(props.maxLength, 10000));
	}
});(end)
	*/

var InputValidator = new Class({
	initialize: function(className, options){
		this.setOptions({
			errorMsg: 'Validation failed.',
			test: function(field){return true}
		}, options);
		this.className = className;
	},
/*	Property: test
		Tests a field against the validator's rule(s).
		
		Arguments:
		field - the form input to test
		
		Returns:
		true - the field passes the test
		false - it does not pass the test
	*/
	test: function(field){
		if($(field)) return this.options.test($(field), this.getProps(field));
		else return false;
	},
/*	Property: getError
		Retrieves the error message for the validator.
		
		Arguments:
		field - the form input to test
		
		Returns:
		The error message or the boolean false if no message is meant to be returned.
	*/
	getError: function(field){
		var err = this.options.errorMsg;
		if($type(err) == "function") err = err($(field), this.getProps(field));
		return err;
	},
	getProps: function(field){
		if($(field) && $(field).getProperty('validatorProps')){
			try {
				return Json.evaluate($(field).getProperty('validatorProps'));
			}catch(e){ return {}}
		} else {
			return {}
		}
	}
});
InputValidator.implement(new Options);


/*	Class: FormValidator
		Evalutes an entire form against all the validators that are set up, displaying messages
		and returning a true/false response for the evaluation of the entire form.
		
		An instance of the FormValidator class will test each field and then behave according to
		the options passed in.
		
		Arguments:
		form - the form to evaluate
		options - an object with name/value pairs
		
		Options:
		fieldSelectors - the selector for fields to include in the validation;
				defaults to: "input, select, textarea"
		useTitles - use the titles of inputs for the error message; overrides
				the messages defined in the InputValidators (see <InputValidator>); defaults to false
		evaluateOnSubmit - validate the form when the user submits it; defaults to true
		evaluateFieldsOnBlur - validate the fields when the blur event fires; defaults to true
		evaluateFieldsOnChange - validate the fields when the change event fires; defaults to true
		serial - (boolean) if one field fails validation, do not validate other fields unless 
					their contents actually change (instead of on blur); defaults to true
		warningPrefix - (string) prefix to be added to every warning; defaults to "Warning: "
		errorPrefix - (string) prefix to be added to every error; defaults to "Error: "
		onFormValidate - function to execute when the form validation completes; this function
			is passed three arguments: a boolean (true if the form passed validation), the form element, 
			and the onsubmit event object if there was one (else, passed undefined)
		onElementValidate - function to execute when an input element is tested; this function
			is passed two arguments: a boolean (true if the form passed validation) and the input element
		
		Example:
(start code)var myFormValidator = new FormValidator($('myForm'), {
	onFormValidate: myFormHandler,
	useTitles: true
});(end)

		Note: 
		FormValidator must be configured with <Validator> objects; see below for details as well as a list of built-in validators. Each <Validator> will be applied to any input that matches its className within the elements of the form that match the fieldSelectors option.

		Using Warnings:
		Each <Validator> can also be used to generate warnings. Warnings still show error messages, but do not prevent the form from being submitted. Warnings can be applied in two ways.
		warn per validator - You can specify any validator as a warning by prefixing "warn-" to the class name. So, for example, if you have a validator called "validate-numbers" you can add the class "warn-validate-numbers" and a warning will be offered rather than an error. The validator will not prevent the form from submitting.
		warn per field - You can also ignore all the validators for a given field. You can add the class "warnOnly" to set all it's validators to present warnings only or you can add the class "ignoreValidation" to the field to turn all the validators off. Note that the FormValidator class has methods do this for you: see <FormValidator.ignoreField> and <FormValidator.enforceField>.
	*/
var FormValidator = new Class({
	options: {
		fieldSelectors:"input, select, textarea",
		useTitles:false,
		evaluateOnSubmit:true,
		evaluateFieldsOnBlur: true,
		evaluateFieldsOnChange: true,
		serial: true,
		warningPrefix: "Warning: ",
		errorPrefix: "Error: ",
		onFormValidate: function(isValid, form){},
		onElementValidate: function(isValid, field){}
	},
	initialize: function(form, options){
		this.setOptions(options);
		try {
			this.form = $(form);
			if(this.options.evaluateOnSubmit) this.form.addEvent('submit', this.onSubmit.bind(this));
			if(this.options.evaluateFieldsOnBlur) this.watchFields();
		}catch(e){//console.log('error: %s', e);
		}
	},
	getFields: function(){
		return this.fields = this.form.getElementsBySelector(this.options.fieldSelectors)
	},
	watchFields: function(){
		try{
			this.getFields().each(function(el){
					el.addEvent('blur', this.validateField.pass([el, false], this));
				if(this.options.evaluateFieldsOnChange)
					el.addEvent('change', this.validateField.pass([el, true], this));
			}, this);
		}catch(e){//console.log('error: %s', e);
		}
	},
	onSubmit: function(event){
		if(!this.validate(event)) new Event(event).stop();
		else {
			this.stop();
			this.reset();
		}
	},
/*	Property: reset
		Removes all the error messages from the form.
	*/
	reset: function() {
		this.getFields().each(this.resetField, this);
	}, 
/*	Property: validate
		Validates all the inputs in the form; note that this function is called on submit unless
		you specify otherwise in the options.
		
		Arguments:
		event - (optional) the submit event
	*/
	validate : function(event) {
		var result = this.getFields().map(function(field) { return this.validateField(field, true); }, this);
		result = result.every(function(val){
			return val;
		});
		this.fireEvent('onFormValidate', [result, this.form, event]);
		return result;
	},
/*	Property: validateField
		Validates the value of a field against all the validators.
		
		Arguments:
		field - the input element to evaluate
		force - (boolean; optional) if false (or undefined) and options.serial==true, the validation does not occur
	*/
	validateField: function(field, force){
		if(this.paused) return true;
		field = $(field);
		var result = true;
		var failed = this.form.getElement('.validation-failed');
		var warned = this.form.getElement('.warning');
		//if the field is defined
		//if there aren't any failed
		//or if there are failed and it's not serial
		//or force
		//then validate
		if(field && (!failed || force || field == failed || (failed && !this.options.serial))){
			var validators = field.className.split(" ").some(function(cn){
				return this.getValidator(cn);
			}, this);
			result = field.className.split(" ").map(function(className){
				return this.test(className,field);
			}, this);
			result = result.every(function(val){
				return val;
			});
			if (validators && !field.hasClass('warnOnly')){
				if(result) field.addClass('validation-passed').removeClass('validation-failed');
				else field.addClass('validation-failed').removeClass('validation-passed');
			}
			if(!warned || force || (warned && !this.options.serial)) {
				var warnings = field.className.split(" ").some(function(cn){
					if(cn.test('^warn-') || field.hasClass('warnOnly')) return this.getValidator(cn.replace(/^warn-/,""));
					return null;
				}, this);
				field.removeClass('warning');
				var warnResult = field.className.split(" ").map(function(cn){
					if(cn.test('^warn-') || field.hasClass('warnOnly')) return this.test(cn.replace(/^warn-/,""), field, true);
					return null;
				}, this);
			}
		}
		return result;
	},
	getPropName: function(className){
		return '__advice'+className;
	},
/*	Property: test
		Tests a field against a specific validator.
		
		Arguments:
		className - the className associated with the validator
		field - the input element
		warn - (boolean; optional) if set to true, test will add a warning advice message if 
				the validator fails, but will always return valid regardless of the input.
	*/
	test: function(className, field, warn){
		if(field.hasClass('ignoreValidation')) return true;
		warn = $pick(warn, false);
		if(field.hasClass('warnOnly')) warn = true;
		field = $(field);
		var isValid = true;
		if(field) {
			var validator = this.getValidator(className);
			if(validator && this.isVisible(field)) {
				isValid = validator.test(field);
				//if the element is visible and it failes to validate
				if(!isValid && validator.getError(field)){
					if(warn) field.addClass('warning');
					var advice = this.makeAdvice(className, field, validator.getError(field), warn);
					this.insertAdvice(advice, field);
					this.showAdvice(className, field);
				} else this.hideAdvice(className, field);
				this.fireEvent('onElementValidate', [isValid, field]);
			}
		}
		if(warn) return true;
		return isValid;
	},
	showAdvice: function(className, field){
		var advice = this.getAdvice(className, field);
		if(advice && !field[this.getPropName(className)] && (advice.getStyle('display') == "none" || advice.getStyle('visiblity') == "hidden" || advice.getStyle('opacity')==0)){
			field[this.getPropName(className)] = true;
			//if element.cnet.js is present, transition the advice in
			if(advice.smoothShow) advice.smoothShow();
			else advice.setStyle('display','block');
		}
	},
	hideAdvice: function(className, field){
		var advice = this.getAdvice(className, field);
		if(advice && field[this.getPropName(className)]) {
			field[this.getPropName(className)] = false;
			//if element.cnet.js is present, transition the advice out
			if(advice.smoothHide) advice.smoothHide();
			else advice.setStyle('display','none');
		}
	},
	isVisible : function(field) {
		while(field.tagName != 'BODY') {
			if($(field).getStyle('display') == "none") return false;
			field = field.getParent();
		}
		return true;
	},
	getAdvice: function(className, field) {
		return $('advice-' + className + '-' + this.getFieldId(field))
	},
	makeAdvice: function(className, field, error, warn){
		var errorMsg = (warn)?this.options.warningPrefix:this.options.errorPrefix;
				errorMsg += (this.options.useTitles) ? $pick(field.title, error):error;
		var advice = this.getAdvice(className, field);
		if(!advice){
			var cssClass = (warn)?'warning-advice':'validation-advice';
			advice = new Element('div').addClass(cssClass).setProperty(
				'id','advice-'+className+'-'+this.getFieldId(field)).setStyle('display','none').appendText(errorMsg);
		} else{
			advice.setHTML(errorMsg);
		}
		return advice;
	},
	insertAdvice: function(advice, field){
		switch (field.type.toLowerCase()) {
			case 'radio':
				var p = $(field.parentNode);
				if(p) {
					p.adopt(advice);
					break;
				}
			default: advice.injectAfter($(field));
	  };
	},
	getFieldId : function(field) {
		return field.id ? field.id : field.id = "input_"+field.name;
	},
/*	Property: resetField
		Removes all the error messages for a specific field.
		
		Arguments:
		field - the field to reset.
	*/
	resetField: function(field) {
		field = $(field);
		if(field) {
			var cn = field.className.split(" ");
			cn.each(function(className) {
				if(className.test('^warn-')) className = className.replace(/^warn-/,"");
				var prop = this.getPropName(className);
				if(field[prop]) this.hideAdvice(className, field);
				field.removeClass('validation-failed');
				field.removeClass('warning');
				field.removeClass('validation-passed');
			}, this);
		}
	},
/*	Property: stop
		Stops validating the form; form will submit even if there are values that do not pass validation;
	*/
	stop: function(){
		this.paused = true;
	},
/*	Property: start
		Resumes validating the form.
	*/
	start: function(){
		this.paused = false;
	},
/*	Property: ignoreField
		Stops validating a particular field.
		
		Arguments:
		field - the field to ignore
		warn - (boolean, optional) don't require the validator to pass, but do produce a warning.
	*/
	ignoreField: function(field, warn){
		field = $(field);
		if(field){
			this.enforceField(field);
			if(warn) field.addClass('warnOnly');
			else field.addClass('ignoreValidation');
		}
	},
/*	Property: enforceField
		Resumes validating a particular field
		
		Arguments:
		field - the field to resume validating
	*/
	enforceField: function(field){
		field = $(field);
		if(field){
			field.removeClass('warnOnly');
			field.removeClass('ignoreValidation');
		}
	}
});
FormValidator.implement(new Options);
FormValidator.implement(new Events);

FormValidator.adders = {
/*	Property: validators
		An array of <Validator> objects.
	*/
	validators:{},
/*	Property: add
		Adds a new form validator to the FormValidator object. 
		
		Arguments:
		className - the className associated with the validator
		options - the <Validator> options (errorMsg and test)


		Note:
		This method is a property of every instance of FormValidator as well as the 
		FormValidator object itself. That is to say that you can add validators to
		the FormValidator object or to an instance of it. Adding validators to an instance
		of FormValidator will make those validators apply only to that instance, while
		adding them to the Class will make them available to all instances.
		
		Examples:
(start code)
//add a validator for ALL instances
FormValidator.add('isEmpty', {
	errorMsg: 'This field is required',
	test: function(element){
		if(element.value.length ==0) return false;
		else return true;
	}
});

//this validator is only available to this single instance
var myFormValidatorInstance = new FormValidator('myform');
myFormValidatorInstance.add('doesNotContainTheLetterQ', {
	errorMsg: 'This field cannot contain the letter Q!',
	test: function(element){
		return !element.getValue().test('q','i');
	}
});

//Extend FormValidator, add a global validator for all instances of that version
var NewFormValidator = FormValidator.extend({
	//...some code
});
NewFormValidator.add('doesNotContainTheLetterZ', {
	errorMsg: 'This field cannot contain the letter Z!',
	test: function(element){
		return !element.getValue().test('z','i');
	}
});
(end)

	*/
	add : function(className, options) {
		this.validators[className] = new InputValidator(className, options);
		//if this is a class
		//extend these validators into it
		if(!this.initialize){
			this.implement({
				validators: this.validators
			});
		}
	},
/*	Property: addAllThese
		An array of InputValidator configurations (see <FormValidator.add> above).
		
		Example:
(start code)
FormValidator.addAllThese([
	['className1', {errorMsg: ..., test: ...}],
	['className2', {errorMsg: ..., test: ...}],
	['className3', {errorMsg: ..., test: ...}],
]);
(end)
	*/
	addAllThese : function(validators) {
		$A(validators).each(function(validator) {
			this.add(validator[0], validator[1]);
		}, this);
	},
	getValidator: function(className){
		return this.validators[className];
	}
};
Object.extend(FormValidator, FormValidator.adders);
FormValidator.implement(FormValidator.adders);

/*	Section: Included InputValidators
		Here are the validators that are included in this libary. Add the className to
		any input and then create a new <FormValidator> and these will automatically be
		applied. See <FormValidator.add> on how to add your own.

		Property: IsEmpty
		Evalutes if the input is empty; this is a utility validator, see <FormValidator.required>.
		
		Error Msg - returns false (no message)
			*/
FormValidator.add('IsEmpty', {
	errorMsg: false,
	test: function(element) { 
		if(element.type == "select-one"||element.type == "select")
			return !(element.selectedIndex >= 0 && element.options[element.selectedIndex].value != "");
		else
			return ((element.getValue() == null) || (element.getValue().length == 0));
	}
});


FormValidator.addAllThese([
/*	Property: required
		Displays an error if the field is empty.
		
		Error Msg - "This field is required"			
	*/
	['required', {
		errorMsg: function(element){return 'This field is required.'}, 
		test: function(element) { 
			return !FormValidator.getValidator('IsEmpty').test(element); 
		}
	}],
/*	Property: minLength
		Displays a message if the input value is less than the supplied length.
		
		Error Msg - Please enter at least [defined minLength] characters (you entered [input length] characters)
		
		Note:
		You must add this className AND properties for it to your input.
	
		Example:
		><input type="text" name="username" class="minLength props{minLength:10}" id="username">
	*/
	['minLength', {
		errorMsg: function(element, props){
			if($type(props.minLength))
				return 'Please enter at least ' + props.minLength + ' characters (you entered ' + element.getValue().length + ' characters).';
			else return '';
		}, 
		test: function(element, props) {
			if($type(props.minLength)) return (element.getValue().length >= $pick(props.minLength, 0));
			else return true;
		}
	}],
/*	Property: maxLength
		Displays a message if the input value is less than the supplied length.
		
		Error Msg - Please enter no more than [defined maxLength] characters (you entered [input length] characters)
		
		Note:
		You must add this className AND properties for it to your input.
		
		Example:
		><input type="text" name="username" class="maxLength props{maxLength:100}" id="username">
	*/
	['maxLength', {
		errorMsg: function(element, props){
			//props is {maxLength:10}
			if($type(props.maxLength))
				return 'Please enter no more than ' + props.maxLength + ' characters (you entered ' + element.getValue().length + ' characters).';
			else return '';
		}, 
		test: function(element, props) {
			//if the value is <= than the maxLength value, element passes test
			return (element.getValue().length <= $pick(props.maxLength, 10000));
		}
	}],
/*	Property: validate-number
		Validates that the entry is a number.
		
		Error Msg - 'Please enter a valid number in this field.'
	*/	
	['validate-number', {
		errorMsg: 'Please enter a valid number in this field.',
		test: function(element) {
				return FormValidator.getValidator('IsEmpty').test(element) || !/[^\d+$]/.test(element.getValue());
		}
	}],
/*	Property: validate-digits
		Validates that the entry contains only numbers

		Error Msg - 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'
	*/
	['validate-digits', {
		errorMsg: 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.', 
		test: function(element) {
			return FormValidator.getValidator('IsEmpty').test(element) || 
				(/[^a-zA-Z]/.test(element.getValue()) && /[\d]/.test(element.getValue()));
		}
	}],
/*	Property: validate-alpha
		Validates that the entry contains only letters 

		Error Msg - 'Please use letters only (a-z) in this field.'
	*/
	['validate-alpha', {
		errorMsg: 'Please use letters only (a-z) in this field.', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) ||  /^[a-zA-Z]+$/.test(element.getValue())
		}
	}],
/*	Property: validate-alphanum
		Validates that the entry is letters and numbers only

		Error Msg - 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'
	*/
	['validate-alphanum', {
		errorMsg: 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', 
		test: function(element) {
			return FormValidator.getValidator('IsEmpty').test(element) || !/\W/.test(element.getValue())
		}
	}],
/*	Property: validate-date
		Validates that the entry parses to a date.

		Error Msg - 'Please use this date format: mm/dd/yyyy. For example 03/17/2006 for the 17th of March, 2006.'
	*/
	['validate-date', {
		errorMsg: 'Please use this date format: mm/dd/yyyy. For example 03/17/2006 for the 17th of March, 2006.',
		test: function(element) {
			if(FormValidator.getValidator('IsEmpty').test(element)) return true;
	    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	    if(!regex.test(element.getValue())) return false;
	    var d = new Date(element.getValue().replace(regex, '$1/$2/$3'));
	    return (parseInt(RegExp.$1, 10) == (1+d.getMonth())) && 
        (parseInt(RegExp.$2, 10) == d.getDate()) && 
        (parseInt(RegExp.$3, 10) == d.getFullYear() );
		}
	}],
/*	Property: validate-email
		Validates that the entry is a valid email address.

		Error Msg - 'Please enter a valid email address. For example fred@domain.com .'
	*/
	['validate-email', {
		errorMsg: 'Please enter a valid email address. For example fred@domain.com .', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) || /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(element.getValue());
		}
	}],
/*	Property: validate-url
		Validates that the entry is a valid url

		Error Msg - 'Please enter a valid URL.'
	*/
	['validate-url', {
		errorMsg: 'Please enter a valid URL.', 
		test: function (element) {
			return FormValidator.getValidator('IsEmpty').test(element) || /^(http|https|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(element.getValue());
		}
	}],
/*	Property: validate-date-au
		Validates that the entry matches dd/mm/yyyy.

		Error Msg - 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'
	*/
	

	['validate-date-au', {
		errorMsg: 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.',
		test: function(element) {
			if(FormValidator.getValidator('IsEmpty').test(element)) return true;
	    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	    if(!regex.test(element.getValue())) return false;
	    var d = new Date(element.getValue().replace(regex, '$2/$1/$3'));
	    return (parseInt(RegExp.$2, 10) == (1+d.getMonth())) && 
        (parseInt(RegExp.$1, 10) == d.getDate()) && 
        (parseInt(RegExp.$3, 10) == d.getFullYear() );
		}
	}],
/*	Property: validate-currency-dollar
		Validates that the entry matches any of the following:
			- [$]1[##][,###]+[.##]
			- [$]1###+[.##]
			- [$]0.##
			- [$].##
		
		Error Msg - 'Please enter a valid $ amount. For example $100.00 .'
	*/
	['validate-currency-dollar', {
		errorMsg: 'Please enter a valid $ amount. For example $100.00 .', 
		test: function(element) {
			// [$]1[##][,###]+[.##]
			// [$]1###+[.##]
			// [$]0.##
			// [$].##
			return FormValidator.getValidator('IsEmpty').test(element) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(element.getValue());
		}
	}],
/*	Property: validate-one-required
		Validates that all the entries within the same node are not empty.

		Error Msg - 'Please enter something for at least one of the above options.'
		
		Note:
		This validator will get the parent element for the input and then check all its children.
		To use this validator, enclose all the inputs you want to group in another element (doesn't
		matter which); you only need apply this class to *one* of the elements.
		
		Example:
(start code)
<div>
	<input ....>
	<input ....>
	<input .... className="validate-one-required">
</div>(end)
	*/
	['validate-one-required', {
		errorMsg: 'Please enter something for at least one of the above options.', 
		test: function (element) {
			var p = element.parentNode;
			var options = p.getElements('input');
			return $A(options).some(function(el) {
				return el.getValue();
			});
		}
	}]
]);

/* do not edit below this line */   
/* Section: Change Log 

$Source: /cvs/main/flatfile/html/rb/js/global/cnet.global.framework/common/js.widgets/form.validator.js,v $
$Log: form.validator.js,v $
Revision 1.24  2007/11/29 19:00:57  newtona
- fixed an issue with StickyWin that could create inheritance issues because of a defect in $merge. this fixed an actual issue with DatePicker, specifically, having more than one on a page
- fixed an issue with the basehref in simple.error.popup

Revision 1.23  2007/11/02 18:15:40  newtona
fixing an issue with the image path in setAssetHref for the date picker
adding mms to url validator in form validator

Revision 1.22  2007/10/27 00:06:19  newtona
adding rtmp support in the form validator that validates urls

Revision 1.21  2007/10/02 18:50:51  newtona
doc fix

Revision 1.20  2007/10/02 00:58:14  newtona
form.validator: fixed a bug where validation errors weren't removed under certain situations
tabswapper: .recall() no longer fails if no cookie name is set
jsonp: adding a 'return this'

Revision 1.19  2007/09/24 20:55:49  newtona
new file: StickyWin.Ajax - adds ajax support to all stickywin classes (creates new classes, just append .Ajax to any of the existing ones)
updated redball common full to include StickyWin.Ajax
date.picker, product.picker - updated syntax to use Element.empty
form.validator - now passes along the event object to the onFormValidate event so that the form submit event can be stopped if you like
popupdetails - added html response support; you can now return the html you wish to display rather than a json object; only applies to ajax. Also added a cache so that multiple requests are not made for the same url.
stickyWinHTML - ractored so that options are now, you know, *optional*
MooScroller - added support for width option for horizontal scrolling

Revision 1.18  2007/09/15 00:07:08  newtona
collission in last check in; merged and re-doing.
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
fixed a bug with validate-digits in form validator
new class: TagMaker
implemented TagMaker into simple.editor
updated caption (which is the drag handle for stickyWinHTML) to be the entire width of the caption area
html.table: docs update
tabswapper: docs update

Revision 1.17  2007/09/05 20:17:34  newtona
frakking semi-colons

Revision 1.16  2007/09/05 18:37:03  newtona
fixing all js warnings in the code base; they weren't breaking anything, but they can create performance issues and it's good practice...

Revision 1.15  2007/06/02 01:35:56  newtona
*** empty log message ***

Revision 1.14  2007/05/29 22:01:53  newtona
Split element.cnet.js into seperate files; updated docs in files to note this
Changed element.visible to element.isVisible (left old namespace for legacy support)
Fixed Element.empty in prototype.compatibility.js
Removed as many dependencies in common code to element.*.js as possible (espeically element.shortcuts.js)

Revision 1.13  2007/04/13 00:22:57  newtona
fixed a typo in FormValidator.hideAdvice (display: none instead of display: block)

Revision 1.12  2007/04/06 00:43:51  newtona
slight syntax update

Revision 1.11  2007/04/06 00:37:40  newtona
tweaked the way serial works

Revision 1.10  2007/04/05 23:48:55  newtona
FormValidator now has numerous new features: instance-level validators, .stop, .start, .ignoreField, .enforceField, and warnings

Revision 1.9  2007/04/05 23:01:26  newtona
FormValidator now has numerous new features: instance-level validators, .stop, .start, .ignoreField, .enforceField, and warnings

Revision 1.8  2007/03/02 00:28:37  newtona
advice is now inserted into the DOM in it's own method so it can be easily overriden
makeAdvice no longer inserts the advice.

Revision 1.7  2007/02/22 18:18:42  newtona
typo in the docs

Revision 1.6  2007/02/07 20:51:41  newtona
implemented Options class
implemented Events class
StickyWin now uses Element.position

Revision 1.5  2007/02/06 18:10:36  newtona
updated the error displays to use the new element.smoothshow function

Revision 1.4  2007/02/03 01:36:17  newtona
added multi-select support
shortened validate-number
updated validate-date essage and fixed a bug in it

Revision 1.3  2007/01/26 05:48:03  newtona
docs update

Revision 1.2  2007/01/22 22:00:15  newtona
numerous bug fixes to modalizer, stickywin, and popupdetails
updated for mootools 1.0
fixed date validation in form.validator

Revision 1.1  2007/01/19 01:22:05  newtona
*** empty log message ***


*/
