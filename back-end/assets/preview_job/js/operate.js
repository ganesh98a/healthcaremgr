$(window).on("unload", function(e){
	localStorage.clear();
});

 var reffererDomain = '';
 var file_ary = [];
 $(document).ready(function() {

  var referrerDomain = '';
 
  // get domain name if document.referrer
  reffererDomain = document.referrer;
  if (document.referrer) {
    referrerDomain = getHostName(document.referrer);
	
	var previousLocalStorage = localStorage.getItem('referrerDomain');
	var previousReferrer = "";
	if(previousLocalStorage != null) {
	    previousReferrer = JSON.parse(previousLocalStorage).referrer;
	}
	
	if(previousReferrer == "" ) {
		localStorage.setItem('referrerDomain', JSON.stringify({
			referrer : document.referrer
		}));
	} else {
		previousDomain = getHostName(previousReferrer);
		if(previousDomain != referrerDomain) {
            reffererDomain = previousReferrer;
		}
	}
	
  }
  
  /*
  if(referrerDomain === "") {
      referrerDomain = JSON.parse(localStorage.getItem('referrerDomain')).referrer;
  }
  */

  // Hide the apply with seek button if not redirected from seek
  if (referrerDomain != 'seek.com.au' || referrerDomain != 'www.seek.com.au' || referrerDomain != 'seek') {
    $('.apply_cus_seek').css("display", "none");
  }

  // Show the apply with seek button if redirected from seek
  if (referrerDomain == 'seek.com.au' || referrerDomain == 'www.seek.com.au' || referrerDomain == 'seek') {
    $('.apply_cus_seek').css("display", "");
  }

  // Get the url with param
  var urlParams = new URLSearchParams(window.location.search);
  var prefilled = urlParams.get('prefilled');
  var src = urlParams.get('src');
  var error = urlParams.get('error');

  // show error msg if user denied the seek permission
  if (error == 'access_denied') {
      error_msg('error', 'Seek access permission denied. Please try again Apply with Seek');
  }

  // show error msg if user denied the seek permission
  if (error == 'something_went_wrong') {
      error_msg('error', 'Seek - something went wrong. Please try again Apply with Seek');
  }

  // Open the modal if redirected from seek
  if (prefilled == 'true') {
    $("#jpbAppliedModalCenter").modal("show");
  }

  // Show the apply with seek btn if url contain src param with value seek
  if(src == 'seek') {
    $('.apply_cus_seek').css("display", "");
  }

  // remove seek resume attachment if file upload by manually
  $(".resume_input_required").on('change',function(){
      $("#seek_resume_active").val(0);
      $(".resume_link").remove('');
  });

  if (seek_timeout_err == '1') {
      error_msg('error', 'Seek Access Token Expired. Please try again Apply with Seek');
  }

  $(".add").click(function(){
    // Finding total number of elements added
    var total_element = $(".element").length;
    // last <div> with element class id
    var lastid = $(".element:last").attr("id");
    var split_id = lastid.split("_");
    var nextindex = Number(split_id[1]) + 1;
    var nextindex_a = Number(split_id[1]);

    var max = 3;
    // Check total number elements
    if(total_element < max ){
    // Adding new div dev_container after last occurance of element class
    $(".element:last").after("<div class='element' id='div_"+ nextindex +"'></div>");

    // Adding element to <div>
    $("#div_" + nextindex).append("<div class='row' id='txt_"+ nextindex +"'><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Full Name:</label><input type='text' class='form-control input_box' name='reference["+nextindex_a+"][name]' placeholder='Full Name' required></div></div><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Phone:</label><input type='text' class='form-control input_box' name='reference["+nextindex_a+"][phone]' placeholder='Phone' required data-rule-phonenumber maxLength='18'></div></div><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Email:</label><input type='email' class='form-control input_box' name='reference["+nextindex_a+"][email]' placeholder='Email' required></div></div><div class='col-lg-3 align-self-end'><div class='form-group add_btn_i'><span class='icon icon-minus-icon remove' id='remove_" + nextindex + "'></span></div></div></div>");
}

});

// Remove element
$('.dev_container').on('click','.remove',function(){
  var id = this.id;
  var split_id = id.split("_");
  var deleteindex = split_id[1];
  $("#div_" + deleteindex).remove();
});




});

function initAutocomplete(){
  console.log("---")
  var options = {
    types: ['geocode'],
    componentRestrictions: {country: "au"}
};

var input_auto = document.getElementById('pac-input');
autocomplete = new google.maps.places.Autocomplete(input_auto, options);


google.maps.event.addListener(autocomplete, 'place_changed', function() {
    adr_comp = autocomplete.getPlace().address_components;

});
};

 function clear_form_inputs()
 {
  $("#jpbAppliedModalCenter").modal("hide");

  var $form = $('#job_applied');
  $form.find(".tooltip ").remove();

  $form.trigger('reset');

  if (window.validator) {
    window.validator.destroy()
  }


  // TODO: Maybe remove the selected attachments
  // and clear the attachment upload progress bar

//   $(':input','#job_applied')
//   .not(':button, :submit, :reset, :hidden,:checkbox')
//   .val('')
//   .removeAttr('checked')
//   .removeAttr('selected');
}


$('.save_data').click(function(e)
{
  e.preventDefault();
  var form = $("#job_applied");

  validator = $("#job_applied").validate({
    //validateHiddenInputs: true,
    //excluded: ':disabled, :hidden, :not(:visible)',
});
$("#referred_phone,#referred_email").removeAttr("required");

  $.validator.setDefaults({ ignore: [] });

  if ($('input[type=file]').length > 0) {
    validator.element('input[type=file]');
  }

  if ($("#job_applied").valid())
  {
    // Get the url with param
    var urlParams = new URLSearchParams(window.location.search);
    var src = urlParams.get('src');
    //(autocomplete.getPlace())?JSON.stringify(autocomplete.getPlace().address_components)
    $(this).prop("disabled", true);
    $(this).text("Submitting...");
    $('#jpbAppliedModalCenter .close').prop("disabled", true);

    var hdn_param =  get_hdn_id();
    var data_1 = $('#job_applied').serializeArray();
    data_1.push({name:'referrer_url',value:reffererDomain},{name:'file_ary',value:JSON.stringify(file_ary)},{name: 'hdn_param', value: hdn_param},{name:'address_component',value:[]},{name: 'source', value: src});

    var formElement = $('#job_applied')[0];
    var formData = new FormData(formElement);
    formData.append('referrer_url', reffererDomain);
    formData.append('file_ary', JSON.stringify(file_ary));
    formData.append('hdn_param', hdn_param);
    formData.append('address_component', JSON.stringify([]));
    formData.append('files', formElement.files)
    formData.append('source', src);
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      processData: false,
      contentType: false,
      data: formData,
      success:function(results) {
        var myObj = $.parseJSON(results);
        if(myObj.status){
          $("#jpbAppliedModalCenter").modal("hide");
          window.dataLayer = window.dataLayer || [];
          window.dataLayer.push({'event': 'hcmFormSubmission'});
          
          //Trigger Customer survey
          triggerSurvey("submit", myObj.application_id, myObj.source);
          //Set localstorage value to avoid to hit the survey api after reload the application
          localStorage.setItem("isSubmit", true);
          error_msg('success','Registration is completed successfully. We will get back to you soon.');

          setTimeout(function(){
            var pathname = window.location.pathname; // Returns path only (/path/example.html)
            var url      = window.location.href;     // Returns full URL (https://example.com/path/example.html)
            var origin   = window.location.origin;
            if (src == 'seek') {
              location.href = origin+pathname+'?src=seek';
              window.location.href = origin+pathname+'?src=seek';
            } else {
              location.reload();
            }
          }, 5000);

      }else{
          error_msg('error',myObj.error);
          setTimeout(function(){ $('.save_data').prop("disabled", false).text("Submit");}, 5000);
      }
  }
});
}
});

function get_hdn_id()
{
  var pathArray = window.location.pathname.split('/');
  var last_node = pathArray[pathArray.length-1];
  if(last_node){
    var temp = last_node.split('.html');
    var hdn_id =  temp[0];
    if(hdn_id){
      hdn_id = hdn_id.split('-');
      return hdn_id[1];
    }
}
return '';
}

function error_msg(error_type,msg)
{
    //error_type = info,warning,error,success
    //position = top-center,mid-center
    $.toast({
      heading: msg,
      showHideTransition: 'slide',
      icon: error_type,
      position: 'mid-center',
      stack: false,
      hideAfter: 5000

  })
}

//http://simpleupload.michaelcbrook.com/#examples
$(document).ready(function(){

 function setupAutomaticUploadOnFileAttach() {
    $('input[type=file]').change(function(e){
      let testname = e.target.name;
      $(this).simpleUpload(imgUpload, {

        start: function(file){
          var $file = $(e.currentTarget);
          var tgtSelector = $file.attr("data-target");
          var $tgtInputEl = $(tgtSelector).first();
          if ($tgtInputEl.length > 0) {
            $tgtInputEl.val(file.name);
          }

          let das = $('#upload_'+testname).parents().parents().parents()
          das.siblings('.filename').html(file.name);
          das.siblings('.progress').html('');
          das.siblings('.progressBar_1').width(0);
      },
      progress: function(progress){
        //received progress
        let das = $('#upload_'+testname).parents().parents().parents()
        das.siblings('.progress').html("Progress: " + Math.round(progress) + "%");
        das.siblings('.progressBar_1').width(progress + "%");
    },

    success: function(data){
        var myObj = $.parseJSON(data);
        let das = $('#upload_'+testname).parents().parents().parents()
        if(myObj.status){
            file_ary.push({ selected_file_name: myObj.data.selected_name,copied_file_name:myObj.data.upload_data.file_name});
            das.siblings('.progress').html("uploaded!");
            $('#hdn_'+testname).val(myObj.data.upload_data.file_name);
        }else{
             das.siblings('.filename').html("<span class='danger'>"+myObj.data.error+"</span>");
        }
    },

    error: function(error){
        //upload failed
        let das = $('#upload_'+testname).parents().parents().parents()
        das.siblings('.progress').html("Failure!");
    }
});
  });
 } // end setupAutomaticUploadOnFileAttach
 // setupAutomaticUploadOnFileAttach()
});

function getHostName(url) {
    var match = url.match(/:\/\/(www[0-9]?\.)?(.[^/:]+)/i);
    if (match != null && match.length > 2 && typeof match[2] === 'string' && match[2].length > 0) {
    return match[2];
    }
    else {
        return null;
    }
}
//Trigger customer survey
function triggerSurvey(action, app_id = null, source = null) {
  var ref = window.location.href;
  var head = document.getElementsByTagName("head")[0];
  var img = document.createElement("img");
  var application_id = app_id ? "&app_id=" + app_id : '';
  var source_name = source ? "&source=" + source : '';
  var event = action ? action : 'open';  
  img.src = "https://smaudience.com/smapi/cj_report.gif?job=1070&action=" + event + application_id + source_name + "&channel_code=web&utm_source=web&utm_medium=web&ref="+ ref;
  img.width = 1;
  img.height = 1;
  img.style.display = "none";
  head.appendChild(img);

}
