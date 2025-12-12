<?php
require_once("DBConnection.php");
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM `category_list` where category_id = '{$_GET['id']}'");
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
}
?>
<div class="container-fluid">
    <form action="" id="category-form">
        <input type="hidden" name="id" value="<?php echo isset($category_id) ? $category_id : '' ?>">
        <div class="form-group">
            <label for="name" class="control-label">Name</label>
            <input type="text" name="name" autofocus id="name" required class="form-control form-control-sm rounded-0" value="<?php echo isset($name) ? $name : '' ?>">
        </div>
        <div class="form-group">
            <label for="description" class="control-label">Description</label>
            <textarea name="description" id="description" cols="30" rows="3" class="form-control rounded-0" required><?php echo isset($description) ? $description : '' ?></textarea>
        </div>
        <div class="form-group">
            <label for="status" class="control-label">Status</label>
            <select name="status" id="status" class="form-select form-select-sm rounded-0" required>
                <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </form>
</div>

<script>
$(function(){

    function showMsg($form, type, text, autoHide){
        $('.pop_msg').remove();
        var $m = $('<div>').addClass('pop_msg alert alert-'+type).text(text).hide();
        $form.prepend($m);
        $m.show('slow');
        if(autoHide){
            setTimeout(()=>{ $m.fadeOut(300,()=>{$m.remove();}); }, autoHide);
        }
    }

    $('#category-form').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);

        let name = $.trim($form.find('[name="name"]').val());
        let status = $form.find('[name="status"]').val();

        let errors = [];

        if(!name) errors.push("Category name is required.");
        if(name.length < 2) errors.push("Category name must be at least 2 characters.");
        if(status !== "0" && status !== "1") errors.push("Please select a valid status.");

        if(errors.length){
            showMsg($form, 'danger', errors.join(' '), 5000);
            return;
        }

        var $btns = $('#uni_modal button');
        $btns.prop('disabled', true);
        $btns.filter('[type="submit"]').text('Submitting...');

        $.ajax({
            url:'./Actions.php?a=save_category',
            method:'POST',
            data:$form.serialize(),
            dataType:'JSON'
        })
        .done(function(resp){
            if(resp.status === 'success'){
                showMsg($form, 'success', resp.msg || "Saved successfully.", 1000);

                $('#uni_modal').on('hide.bs.modal', ()=> location.reload());

                // reset only if adding new
                if("<?php echo isset($category_id) ?>" != 1){
                    $form[0].reset();
                }
            } else {
                showMsg($form, 'danger', resp.msg || "Failed to save category.", 5000);
            }
        })
        .fail(function(){
            showMsg($form, 'danger', "Server error occurred.", 5000);
        })
        .always(function(){
            $btns.prop('disabled', false);
            $btns.filter('[type="submit"]').text('Save');
        });

    });

});
</script>
