$(document).ready(function() {

  //$(".select").chosen({width: "95%",allow_single_deselect:false,disable_search_threshold:10});

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
    $("#div_" + nextindex).append("<div class='row' id='txt_"+ nextindex +"'><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Full Name:</label><input type='text' class='form-control input_box' name='reference["+nextindex_a+"][name]' placeholder='Full Name' required></div></div><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Phone:</label><input type='text' class='form-control input_box' name='reference["+nextindex_a+"][phone]' placeholder='Phone' required></div></div><div class='col-lg-3'><div class='form-group'><label for='exampleFormControlInput1'>Email:</label><input type='email' class='form-control input_box' name='reference["+nextindex_a+"][email]' placeholder='Email' required></div></div><div class='col-lg-3 align-self-end'><div class='form-group add_btn_i'><span class='icon icon-minus-icon remove' id='remove_" + nextindex + "'></span></div></div></div>");

}

});

// Remove element
$('.dev_container').on('click','.remove',function(){
  var id = this.id;
  var split_id = id.split("_");
  var deleteindex = split_id[1];

  // Remove <div> with id
  $("#div_" + deleteindex).remove();

}); 

$(function(){
  var options = {
    types: ['geocode'],
    componentRestrictions: {country: "au"}
};

var input_auto = document.getElementById('pac-input');
autocomplete = new google.maps.places.Autocomplete(input_auto, options);

google.maps.event.addListener(autocomplete, 'place_changed', function() {
    console.log(autocomplete.getPlace())
            //console.log(autocomplete.getPlace().formatted_address)  //for db
            console.log(autocomplete.getPlace().address_components)
            var addr_comp = autocomplete.getPlace().address_components;

           /* var street = autocomplete.getPlace().address_components[0].long_name;
            var postal = autocomplete.getPlace().address_components[5].long_name;
            var state = autocomplete.getPlace().address_components[3].short_name;
            alert(state);*/

           /* input_auto.addEventListener('blur', function() {
                input_auto.value = street;
            });
            input_auto.addEventListener('keydown', function() {
                input_auto.value = street;
            });*/


            /**/
            var componentForm = { street_number: "short_name", route: "long_name", locality: "long_name", administrative_area_level_1: "short_name", postal_code: "short_name" };
            var state = {};

            if (Array.isArray(addr_comp)) {
                for (var i = 0; i < addr_comp.length; i++) {
                  var addressType = addr_comp[i].types[0];
                  if (componentForm[addressType]) {
                    var val = addr_comp[i][componentForm[addressType]];

                    if (addressType === "route") {
                      state["street"] = (state["street"] ? state["street"] : "") + " " + val;
                  } else if (addressType === "street_number") {
                      state["street"] = val;
                  } else if (addressType === "locality") {
                      state["suburb"] = val;
                  } else if (addressType === "administrative_area_level_1") {
                      if (statesList) { 
                        statesList = JSON.parse(statesList);
                        console.log(statesList);
                                //var t_index =   statesList.findIndex(item => item.label === val);  
                                //var t_index =   findWithAttr(statesList, 'label',  val);
                                //var t_index =   _.indexOf(val,statesList)
                                //var t_index =    statesList.findIndex(function(el){ return el.label === val; }) 
                                var t_index =     _.findIndex(statesList, val) 

                                console.log('====>t_index',t_index);
                                state["state"] = statesList[t_index].value;
                                //state["state"] = val;
                            } //else {
                                //var t_index = obj.state.states.findIndex(x => x.label == val);
                                //state["state"] = obj.state.states[t_index].value;
                            //}
                        } else if (addressType === "postal_code") {
                            state["postcode"] = val;
                        }
                    }
                }
            }
            console.log(state);
            /**/
            setTimeout(function(){
                //$('#pac-input').val(street);
                //$('#postal').val(postal);

                /*$("#state_select").val('VIC');
                $('#state_select').niceSelect('update'); */
                //mySelect.selectItem(2)

            },1);
        });
});

get_state_list();
get_hdn_id();

//$("#suburb_chosen_search").chosen({width: "95%",search_contains:true});

//chosen_ajaxify('suburb_chosen_search');

/*function chosen_ajaxify(id){ 

$("#suburb_chosen_search").chosen().change(function(){
    console.log(params);
 // params.selected and params.deselected will now contain the values of the
 // or deselected elements.
});
}*/

function chosen_ajaxify(id){ 
    $('div#' + id + '_chosen .chosen-search input').keyup(function(evt, params){
        var keyword = $(this).val();
        var keyword_pattern = new RegExp(keyword, 'gi');
        $('div#' + id + '_chosen ul.chosen-results').empty();
        $("#"+id).empty();
        $.ajax({
            url: "http://localhost:82/BitBucket_Dir/OCS_Dev/admin/back-end/recruitment/RecruitmentAppliedForJob/get_suburb_list?name="+ keyword,
            dataType: "json",
            success: function(response){
                // map, just as in functional programming :). Other way to say "foreach"
                $.map(response, function(item){
                    $('#'+id).append('<option value="' + item.value + '">' + item.label + '</option>');
                });

                $("#"+id).trigger("chosen:updated");
                $('div#' + id + '_chosen').removeClass('chosen-container-single-nosearch');
                $('div#' + id + '_chosen .chosen-search input').val(keyword);
                $('div#' + id + '_chosen .chosen-search input').removeAttr('readonly');
                $('div#' + id + '_chosen .chosen-search input').focus();
                // put that underscores
                $('div#' + id + '_chosen .active-result').each(function(){
                    var html = $(this).html();
                    $(this).html(html.replace(keyword_pattern, function(matched){ 
                        return '<em>' + matched + '</em>';
                    }));
                });

                /*var selectedValue = params;
                console.log($("#suburb_chosen_search").chosen().find("option:selected" ).text());*/
                /*console.log($("#suburb_chosen_search").chosen().val())
                console.log($(this).val())*/

                /* var ar = [];
                $('#suburb_chosen_search option:selected').each(function(index,valor){
                ar.push(valor.value);
                });
                console.log(ar);*/

                /* var  selectedValue = $.map( $(this).find("option:selected").val(), function(n){
                console.log(this.value );  //this will print array in console.
                console.log(this.value.join(',')); //this will alert all values ,comma seperated
                //return this.value;
            });*/
        }
    });
    });
}

});

function findWithAttr(array, attr, value) {
  for(var i = 0; i < array.length; i += 1) {

    var my_ary  = Object.entries(array);
    console.log(my_ary[i][attr] +'==='+ value)
    if(my_ary[i][attr] === value) {
      return i;
  }
}
return -1;
}


function clear_form_inputs()
{
    //alert();
    var validator = $("#job_applied").validate({ });

    validator.resetForm();
    validator.destroy();

    $("#job_applied").validate().destroy();


    $("#jpbAppliedModalCenter").modal("hide");

    $(':input','#job_applied')
    .not(':button, :submit, :reset, :hidden,:checkbox')
    .val('')
    .removeAttr('checked')
    .removeAttr('selected');
    $("#jpbAppliedModalCenter").modal("hide");
}


$('.save_data').click(function(e) 
{   
  e.preventDefault();
  var form = $("#job_applied");
  validator = $("#job_applied").validate({ });

  if ($("#job_applied").valid())
  {
    var hdn_param =  get_hdn_id();
    var data_1 = $('#job_applied').serializeArray();
    data_1.push({name: 'hdn_param', value: hdn_param});

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data:data_1,
      success:function(results) {
        $("#gridD").html(results);
    }
});
}
});

function get_job_record()
{
  $.ajax({
    url: '',
    type: 'POST',
        //data:form.serialize(),
        success:function(results) {
          var myObj = $.parseJSON(results);
            //console.log(myObj.status);
            if(myObj.status){
             var operate_obj = myObj.data;
             $("#html_desc").html(operate_obj.description);
             $("#html_type").html(operate_obj.job_type);
             $("#html_category").html(operate_obj.job_category);
             $("#html_sub_cat").html(operate_obj.job_sub_category);
             $("#html_job_position").html(operate_obj.position);
             $("#html_address").html(operate_obj.address);
             $("#html_phone").html(operate_obj.phone);
             $("#html_email").html(operate_obj.email);
             $("#html_website").html(operate_obj.website);

             if(operate_obj.docs){
              var docs_html = '';
              docs_html +="<div class='pt-5'><h5><strong>Required Documents:</strong></h5><div class='list_req_doc'>";
              $.each( operate_obj.docs, function( key, val ) {
                if(operate_obj.docs.hasOwnProperty(key)){

                  docs_html += "<div><span>"+val.title+"</span> <span class='btn_small_1'>";
                  if(val.is_required && val.is_required == 1)
                    docs_html +="Required";
                else
                    docs_html +="Optional";

                docs_html +="</span></div>";
            }
        })
              docs_html +="</div></div>";
              $("#html_docs").html(docs_html);
          }
      }
  }
});
}

function get_state_list(){
  $.ajax({
    url: 'http://localhost:82/BitBucket_Dir/OCS_Dev/admin/back-end/recruitment/RecruitmentAppliedForJob/get_state_list',
    type: 'POST',
    success:function(response) {    
      var myObj = $.parseJSON(response);
      var len = myObj.length;

      $("#state_select").empty();
      $("#state_select").append("<option>Please select</option>");
      $.each( myObj, function( key, vall ) {
        var id = vall.value;
        var name = vall.label;
        $("#state_select").append("<option value='"+id+"'>"+name+"</option>");
        $('#state_select').trigger('chosen:updated');
    })
  }
});
}

function get_hdn_id()
{
    var pathArray = window.location.pathname.split('/');
    var last_node = pathArray[pathArray.length-1];
    if(last_node){
        var temp = last_node.split('.html');
        var hdn_id =  temp[0];
        if(hdn_id)
            return hdn_id;
    }
    return '';
}





