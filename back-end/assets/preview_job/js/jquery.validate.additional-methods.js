// The HTML5 standard, w/c the latest jQuery validation uses for email validation, considers an email without tlds as valid
// The method overrides the built-in email validation to use the ones in the link below
// https://github.com/jquery-validation/jquery-validation/blob/1.11.1/jquery.validate.js#L1015
jQuery.validator.addMethod("email", function (value, element) {
    return this.optional(element) || /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(value);
}, jQuery.validator.messages.email);


// Older "accept" file extension method. Old docs: http://docs.jquery.com/Plugins/Validation/Methods/accept
$.validator.addMethod( "extension", function( value, element, param ) {
	param = typeof param === "string" ? param.replace( /,/g, "|" ) : "png|jpe?g|gif";
	return this.optional( element ) || value.match( new RegExp( "\\.(" + param + ")$", "i" ) );
}, $.validator.format( "Please enter a value with a valid extension." ) );


jQuery.validator.addMethod("hasatleastoneletter", function(value, element) {
    if (this.optional(element)) {
        return true;
    }

    // don't allow if there's no letters
    if (!/[a-z]/i.test(value)) {
        return false;
    }

    return true;

}, 'Value must has at least one letter');


jQuery.validator.addMethod("valid_name", function(value, element) {
    if (this.optional(element)) {
        return true;
    }

    var trimmedValue = ''.concat(value).trim();

    // don't allow if there's no letters
    if (!/[a-z]/i.test(trimmedValue)) {
        return false;
    }

    // Allow these chars only
    if (!/^[a-z ,\.'-]+$/i.test(trimmedValue)) {
        return false;
    }

    // special chars at the end, except dot
    if (/[',-]$/gi.test(trimmedValue)) {
        return false;
    }

    // special chars at the beiginning (except .)
    if (/^[',-]/gi.test(trimmedValue)) {
        return false;
    }

    // special chars at the beiginning (including .)
    if (/^[',-\.]/gi.test(trimmedValue)) {
        return false;
    }

    // special chars that were allowed previously are sitting 
    // next to each other are not allowed
    if (/([-',\. ])([-',\.])/gi.test(trimmedValue)) {
        return false
    }

    // double spaces not allowed
    if (/\s\s/gi.test(trimmedValue)) {
        return false;
    }

    return true;

}, 'Invalid name');







$.validator.addMethod("phonenumber", function (value, element) {
    var trimmedValue = !!value ? jQuery.trim(value) : '';
    return this.optional(element) || /^(?=.{8,18}$)(\(?\+?[0-9]{1,3}\)?)([ ]{0,1})?[0-9_\- \(\)]{8,18}$/.test(trimmedValue);
}, "Please enter valid phone number");