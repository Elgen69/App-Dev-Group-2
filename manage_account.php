<?php
// Include the database connection file
require_once("DBConnection.php");

// Execute a query to select the current user's details from the user_list table
$qry = $conn->query("SELECT * FROM `user_list` where user_id = '{$_SESSION['user_id']}'");

// Loop through the fetched array and assign each key-value pair to a variable with the key's name
foreach($qry->fetch_array() as $k => $v){
    $$k = $v;
}
?>
<div class="content py-3">
    <div class="card shadow rounded-0">
        <div class="card-body">
        <h3>Manage Account</h3>
            <hr>
            <div class="col-md-6">
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
                        <label for="password" class="control-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control form-control-sm rounded-0" value="">
                    </div>
                    <div class="form-group">
                        <small>Leave the New Password field blank if you don't want update your password.</small>
                    </div>
                    <div class="form-group">
                        <label for="old_password" class="control-label">Old Password</label>
                        <input type="password" name="old_password" id="old_password" class="form-control form-control-sm rounded-0" value="">
                    </div>
                    <div class="form-group d-flex w-100 justify-content-end">
                        <button class="btn btn-sm btn-primary rounded-0 my-1">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
  $('#user-form').submit(function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    var $f = $(this);
    var $btn = $('#uni_modal button[type="submit"]');
    var el = $('<div>').addClass('pop_msg');

    // Basic checks
    var username = $.trim($f.find('[name="username"]').val());
    var fullname = $.trim($f.find('[name="fullname"]').val());
    var password = $f.find('[name="password"]').val();
    var confirm_password = $f.find('[name="confirm_password"]').val();

    if(!username){ el.addClass('alert alert-danger').text('Username is required.'); $f.prepend(el); return; }
    if(!fullname){ el.addClass('alert alert-danger').text('Full name is required.'); $f.prepend(el); return; }
    // if password is supplied, require confirm and length
    if(password && password.length < 6){ el.addClass('alert alert-danger').text('Password must be at least 6 characters.'); $f.prepend(el); return; }
    if(password && password !== confirm_password){ el.addClass('alert alert-danger').text('Password and confirm password do not match.'); $f.prepend(el); return; }

    $btn.attr('disabled',true).text('Submitting...');
    $.ajax({
      url:'./Actions.php?a=update_credentials',
      method:'POST',
      data:$f.serialize(),
      dataType:'JSON'
    }).done(function(resp){
      if(resp && resp.status == 'success'){
        location.reload();
      } else {
        el.addClass('alert alert-danger').text(resp && resp.msg ? resp.msg : 'Update failed');
        $f.prepend(el);
      }
    }).fail(function(){ el.addClass('alert alert-danger').text('Server error'); $f.prepend(el); })
    .always(function(){ $btn.attr('disabled', false).text('Save'); });

  });
});
</script>
