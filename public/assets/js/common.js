function validation(checkKey, id) {
  var returnVal = 1;
  $('#' + id + ' input').css('border', '');
  $('[id^=' + id + '] input,[id^=' + id + '] textarea').each(
    function(index) {
      var input = $(this);
      var value = input.val();
      var id = input.attr('name');
      input.parent('div').removeClass('has-error');
      $('.error-block').remove()
      var errorMessage = '';
      if (checkKey != 1 && id && input.attr('type') != 'hidden') {
        if (id == 'email' && !validate_email(value)) {
          errorMessage = 'Please provide valid email';
        } else if ((id == 'name' || id == 'firstname' || id == 'lastname') && value == '') {
          errorMessage = 'Please provide valid name';
        } else if ((id == 'mobile' || id == 'telephone' || id == 'phone') && value.length < 10) {
          errorMessage = 'Please provide 10 digit mobile no.';
        } else if ((id == 'postcode' || id == 'pincode') && value == '') {
          errorMessage = 'Please provide valid pincode';
        } else if ((id == 'postcode' || id == 'pincode') && value.length < 6) {
          errorMessage = 'Please provide 6 digit pincode';
        } else if (id == 'password' && value == '') {
          errorMessage = 'Please provide valid password';
        } else if (value != '' && id == 'password' && value.length < 6) {
          errorMessage = 'Please enter at least 6 characters password';
        } else if (value != '' && id == 'password' && value.length > 20) {
          errorMessage = 'Password must be between 6 and 20 characters!';
        } else if (id == 'confirm' && value != $('#password').val()) {
          errorMessage = 'Password and confirm password does not match';
        } else if (id == 'subject' && value == '') {
          errorMessage = 'Please enter subject';
        } else if (id == 'file' && value == '') {
          errorMessage = 'Please select file';
        } else if (id == 'city' && value == '') {
          errorMessage = 'Please provide city name';
        } else if (id == 'country' && value == '') {
          errorMessage = 'Please provide country name';
        } else if (id == 'address_1' && value == '') {
          errorMessage = 'Please provide House No';
        } else if (id == 'address_2' && value == '') {
          errorMessage = 'Please provide Society Name';
        } else if (id == 'address_3' && value == '') {
          errorMessage = 'Please provide Street Address';
        }
      }
      if (errorMessage) {
        returnVal = 0;
        if (errorMessage) {
          if (id == 'file') {
            input.parent().addClass('inpuT_ErrorData').css('border-bottom', '1px solid red').after(' <p class="error-block small">' + errorMessage + '</p>');
          } else input.addClass('inpuT_ErrorData').css('border-bottom', '1px solid red').after(' <p class="error-block small">' + errorMessage + '</p>');
        }
        return false;
      }
    });
  return returnVal;
}
function validate_email(o) {
  var a = o.indexOf("@"),
    t = o.lastIndexOf(".");
  return !(a < 1 || t < a + 2 || t + 2 >= o.length)
}
$('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    startDate: '+1',
    autoclose: true
});
 $('#btn-to-top').click(function (e) {
        
  e.preventDefault();
            $('body,html').animate({
                scrollTop: 0
            }, 600);
            return false;
   });

function contactSumit(d){
    
  if(!validation(0,'contact_form'))return false;
 $('#btndiv').html('').html('<div class="loader"></div>');
 
  $.ajax({
    url:"home/saveContact",
    type:'post',
    data:$('#contact_form').serialize(),
   
    success:function(data){
     
     $('#contact_form').html('').html('<h3>'+data+'</h3>');
    }

  });
}
