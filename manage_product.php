<?php
// Required to connect to the database
require_once("DBConnection.php");

// Check if a product ID is set via GET request
if(isset($_GET['id'])){
    $product_id = $_GET['id'];
    // Fetch the product details from the database
    $qry = $conn->query("SELECT * FROM `product_list` where product_id = '$product_id'");
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v; // Dynamically set variables for each field in the product record
    }
}
?>

<div class="container-fluid">
    <form action="" id="product-form" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo isset($product_id) ? $product_id : '' ?>">
        <div class="col-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="product_code" class="control-label">Code</label>
                        <input type="text" name="product_code" autofocus id="product_code" required class="form-control form-control-sm rounded-0" value="<?php echo isset($product_code) ? $product_code : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="category_id" class="control-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select form-select-sm rounded-0 select2" required>
                            <option <?php echo (!isset($category_id)) ? 'selected' : '' ?> disabled>Please Select Here</option>
                            <?php
                            $cat_qry = $conn->query("SELECT * FROM category_list where `status` = 1 and `delete_flag` = 0  order by `name` asc");
                            while($row= $cat_qry->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['category_id'] ?>" <?php echo (isset($category_id) && $category_id == $row['category_id'] ) ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="name"  id="name" required class="form-control form-control-sm rounded-0" value="<?php echo isset($name) ? $name : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="price" class="control-label">Price</label>
                        <input type="number" step="any" name="price"  id="price" required class="form-control form-control-sm rounded-0 text-end" value="<?php echo isset($price) ? $price : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="alert_restock" class="control-label">QTY Alert for Restock</label>
                        <input type="number" step="any" name="alert_restock"  id="alert_restock" required class="form-control form-control-sm rounded-0 text-end" value="<?php echo isset($alert_restock) ? $alert_restock : '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
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
                    <div class="form-group">
                        <label for="images" class="control-label">Images</label>
                        <input type="file" name="images[]" id="images" class="form-control form-control-sm rounded-0" multiple>
                    </div>
                    <?php if(isset($product_id)): ?>
                    <div class="form-group">
                        <label for="existing_images" class="control-label">Existing Images</label>
                        <div id="existing_images">
                            <?php
                            $images_qry = $conn->query("SELECT image_path FROM product_list WHERE product_id = '{$product_id}'");
                            while($img_row = $images_qry->fetch_assoc()):
                            ?>
                                <div class="img-item">
                                    <img src="<?php echo $img_row['image_path'] ?>" alt="Product Image" class="img-thumbnail" style="width:100px; height:100px;">
                                    <button type="button" class="btn btn-danger btn-sm remove-image" data-id="<?php echo $product_id ?>">Remove</button>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(function(){
  function showMsg($form, type, msg){
    $('.pop_msg').remove();
    var $el = $('<div>').addClass('pop_msg alert').addClass(type=='success'?'alert-success':'alert-danger').text(msg);
    $form.prepend($el);
    $el.show('slow');
  }

  $('#product-form').submit(function(e){
    e.preventDefault();
    var $f = $(this);

    // Basic client-side validation
    var code = $.trim($f.find('[name="product_code"]').val());
    var cat  = $f.find('[name="category_id"]').val();
    var name = $.trim($f.find('[name="name"]').val());
    var price = parseFloat($f.find('[name="price"]').val());
    var alert_restock = parseFloat($f.find('[name="alert_restock"]').val());
    var description = $.trim($f.find('[name="description"]').val());

    if (!code){ showMsg($f,'error','Product code is required.'); return; }
    if (!cat){ showMsg($f,'error','Please choose a category.'); return; }
    if (!name){ showMsg($f,'error','Product name is required.'); return; }
    if (isNaN(price) || price < 0){ showMsg($f,'error','Price must be a valid number >= 0.'); return; }
    if (isNaN(alert_restock) || alert_restock < 0){ showMsg($f,'error','Alert restock must be a valid number >= 0.'); return; }
    if (!description){ showMsg($f,'error','Description is required.'); return; }

    // disable and submit
    var $btn = $('#uni_modal button[type="submit"]');
    $btn.prop('disabled', true).text('Submitting...');

    $.ajax({
      url: './Actions.php?a=save_product',
      data: new FormData($f[0]),
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      dataType: 'json'
    }).done(function(resp){
      if (resp && resp.status == 'success'){
        showMsg($f,'success', resp.msg || 'Product saved.');
        // reset form only for new product
        if(!('<?php echo isset($product_id) ? 1 : 0 ?>')){
          $f[0].reset();
          $('.select2').val('').trigger('change');
        }
        // reload after small delay (so user sees message)
        setTimeout(function(){ location.reload(); }, 600);
      } else {
        showMsg($f,'error', (resp && resp.msg) ? resp.msg : 'Save failed.');
      }
    }).fail(function(xhr, st, err){
      console.error(xhr,st,err);
      showMsg($f,'error','Network/server error. Check console.');
    }).always(function(){ $btn.prop('disabled', false).text('Save'); });

  });

  // image remove uses image identifier: make sure your server expects image id, not product id
  $(document).on('click', '.remove-image', function(){
    var $btn = $(this);
    var imgId = $btn.data('id');
    if(!imgId) return alert('Missing image id.');
    if(!confirm('Remove this image?')) return;
    $.post('./Actions.php?a=delete_image', { id: imgId }, function(resp){
      try { resp = typeof resp === 'string' ? JSON.parse(resp) : resp; } catch(e){}
      if (resp && resp.status == 'success'){
        $btn.closest('.img-item').remove();
      } else {
        alert(resp && resp.msg ? resp.msg : 'Delete failed');
      }
    }, 'json').fail(function(){ alert('Delete failed'); });
  });

});
</script>
