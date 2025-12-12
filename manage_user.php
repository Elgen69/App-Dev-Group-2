<?php
require_once("DBConnection.php");

if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM `user_list` where user_id = '{$_GET['id']}'");
    foreach($qry->fetch_assoc() as $k => $v){
        $$k = $v;
    }
     // Set default type to 0 if not set (for existing users)
    if (!isset($type)) {
        $type = 0;
    }
}
?>
<div class="container-fluid">
    <form action="" id="user-form">
        <input type="hidden" name="id" value="<?php echo isset($user_id) ? $user_id : '' ?>">
        <div class="form-group">
            <label for="fullname" class="control-label">Full Name</label>
            <input type="text" name="fullname" id="fullname" required class="form-control form-control-sm rounded-0" value="<?php echo isset($fullname) ? $fullname : '' ?>">
        </div>
        <div class="form-group">
            <label for="username" class="control-label">Username</label>
            <input type="text" name="username" id="username" required class="form-control form-control-sm rounded-0" value="<?php echo isset($username) ? $username : '' ?>">
        </div>
        <div class="form-group">
            <label for="email" class="control-label">Email</label>
            <input type="email" name="email" id="email" required class="form-control form-control-sm rounded-0" value="<?php echo isset($email) ? $email : '' ?>">
        </div>
        <div class="form-group">
            <label for="phone_number" class="control-label">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" required class="form-control form-control-sm rounded-0" value="<?php echo isset($phone_number) ? $phone_number : '' ?>">
        </div>
        <div class="form-group">
            <label for="type" class="control-label">Type</label>
            <select name="type" id="type" class="form-select form-select-sm rounded-0" required>
                <option value="1" <?php echo isset($type) && $type == 1 ? 'selected' : '' ?>>Administrator</option>
                <option value="0" <?php echo isset($type) && $type == 0 ? 'selected' : '' ?>>Cashier</option>
            </select>
        </div>

    </form>
</div>


<script>
$(function(){

    function showFormMessage($form, type, text, autoHideMs){
        $('.pop_msg').remove();
        var $msg = $('<div>').addClass('pop_msg alert').addClass('alert-' + type).text(text).hide();
        $form.prepend($msg);
        $msg.show('slow');
        if(autoHideMs){
            setTimeout(function(){ $msg.fadeOut(300, function(){ $(this).remove(); }); }, autoHideMs);
        }
        return $msg;
    }

    function isValidEmail(email){
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    $('#user-form').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        $('.pop_msg').remove();

        var user_id = $.trim($form.find('[name="id"]').val() || '');
        var fullname = $.trim($form.find('[name="fullname"]').val() || '');
        var username = $.trim($form.find('[name="username"]').val() || '');
        var email = $.trim($form.find('[name="email"]').val() || '');
        var type = $form.find('[name="type"]').val();

        // When editing — optional password update
        var password = $.trim($form.find('[name="password"]').val() || '');
        var confirm = $.trim($form.find('[name="confirm_password"]').val() || '');

        var creating = (user_id === '');
        var errors = [];

        // Required fields
        if(!fullname) errors.push('Fullname is required.');
        if(!username) errors.push('Username is required.');
        if(!type) errors.push('Please select account type.');

        // Email optional but must be valid if present
        if(email && !isValidEmail(email)) errors.push('Email format is invalid.');

        // ONLY VALIDATE PASSWORD IF EDITING AND THEY TYPE SOMETHING
        if(!creating && password){
            if(password.length < 6) errors.push('Password must be at least 6 characters.');
            if(password !== confirm) errors.push('Password and confirmation do not match.');
        }

        if(errors.length){
            showFormMessage($form, 'danger', errors.join(' '), 6000);
            return;
        }

        var $btns = $('#uni_modal button');
        $btns.prop('disabled', true);
        $btns.filter('[type="submit"]').text('Submitting...');

        $.ajax({
            url: './Actions.php?a=save_user',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            timeout: 30000
        })
        .done(function(resp){
            if(!resp){
                showFormMessage($form, 'danger', 'Empty server response.', 5000);
                return;
            }

            if(resp.status === 'success'){
                showFormMessage($form, 'success', resp.msg || 'User saved.', 1500);

                // 🎉 NEW USER — show the auto-generated password
                if(creating && resp.initial_password){
                    setTimeout(function(){
                        alert(
                            'User created successfully.\n' +
                            'Initial Password: ' + resp.initial_password
                        );
                        $('#uni_modal').modal('hide');
                        location.reload();
                    }, 700);
                    return;
                }

                // EDIT SUCCESS → Reload
                setTimeout(function(){
                    $('#uni_modal').modal('hide');
                    location.reload();
                }, 700);

            } else {
                showFormMessage($form, 'danger', resp.msg || 'Failed to save user.', 6000);
            }
        })
        .fail(function(xhr, status, err){
            console.error('AJAX error', status, err, xhr?.responseText);
            showFormMessage($form, 'danger', 'Server error occurred while saving user.', 7000);
        })
        .always(function(){
            $btns.prop('disabled', false);
            $btns.filter('[type="submit"]').text('Save');
        });

    });

});
</script>
