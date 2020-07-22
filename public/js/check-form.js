
    function onSubmit(token) {

        if (!isEmail($('input#email').val())) {            
            $('.message').html('<div class="alert alert-danger">Please, enter a valid email address.</div>');
        } else if($('input#name').val().length === 0) {
            $('.message').html('<div class="alert alert-danger">Please, enter a valid name.</div>');
        } else if($('input#subject').val().length === 0) {
            $('.message').html('<div class="alert alert-danger">Please, enter a valid subject.</div>');
        } else if(!$("#message").val()) {
            $('.message').html('<div class="alert alert-danger">Please, enter a valid message.</div>');
        } else if(!$('input#agree').prop('checked')) {
            $('.message').html('<div class="alert alert-danger">Please, accept the terms.</div>');
        } else {
            $("form").submit();
        }

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
      }
    }