
<?php
require_once("DBConnection.php");
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM `stock_list` where stock_id = '{$_GET['id']}'");
    foreach($qry->fetch_array() as $k => $v){
        $$k = $v;
    }
}
?>
<div class="container-fluid">
    <form action="" id="stock-form">
        <input type="hidden" name="id" value="<?php echo isset($stock_id) ? $stock_id : '' ?>">
        <div class="col-12">
            <div class="row">
                <div class="col-md-12">
                    <?php if(!isset($_GET['pid'])): ?>
                    <div class="form-group">
                        <label for="product_id" class="control-label">Product</label>
                        <select name="product_id" id="product_id" class="form-select form-select-sm rounded-0 select2" required >
                            <option <?php echo (!isset($product_id)) ? 'selected' : '' ?> disabled>Please Select Here</option>
                            <?php
                            $prod_qry = $conn->query("SELECT * FROM product_list where `status` = 1 and delete_flag = 0  order by `name` asc");
                            while($row= $prod_qry->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['product_id'] ?>" <?php echo ((isset($product_id) && $product_id == $row['product_id']) || (isset($_GET['pid']) && $_GET['pid'] == $row['product_id']) ) ? 'selected' : '' ?>><?php echo $row['name'].'-'.$row['product_code'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="product_id" value="<?php echo $_GET['pid'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="quantity" class="control-label">Quantity</label>
                        <input type="number" step="any" name="quantity"  id="quantity" required class="form-control form-control-sm rounded-0 text-end" value="<?php echo isset($quantity) ? $quantity : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="expiry_date" class="control-label">Expiry Date</label>
                        <input type="date" name="expiry_date"  id="expiry_date" required class="form-control form-control-sm rounded-0" value="<?php echo isset($expiry_date) ? date("Y-m-d", strtotime($expiry_date)) : '' ?>">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(function(){
  $('#stock-form').submit(function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    var $f = $(this), el = $('<div>').addClass('pop_msg');
    var $btn = $('#uni_modal button[type="submit"]');

    var pid = $f.find('[name="product_id"]').val() || $f.find('[name="pid"]').val();
    var qty = parseFloat($f.find('[name="quantity"]').val());
    var expiry = $f.find('[name="expiry_date"]').val();

    if(!pid){ el.addClass('alert alert-danger').text('Product is required.'); $f.prepend(el); return; }
    if(isNaN(qty) || qty <= 0){ el.addClass('alert alert-danger').text('Quantity must be greater than 0.'); $f.prepend(el); return; }
    if(!expiry){ el.addClass('alert alert-danger').text('Expiry date is required.'); $f.prepend(el); return; }

    $btn.attr('disabled', true).text('Submitting...');
    $.ajax({
      url:'./Actions.php?a=save_stock',
      data: new FormData($f[0]),
      cache: false,
      contentType: false,
      processData: false,
      method:'POST',
      dataType:'json'
    }).done(function(resp){
      if(resp && resp.status == 'success'){
        el.addClass('alert alert-success').text(resp.msg || 'Stock saved');
        $f.prepend(el);
        setTimeout(function(){ location.reload(); }, 500);
      } else {
        el.addClass('alert alert-danger').text(resp && resp.msg ? resp.msg : 'Save failed'); $f.prepend(el);
      }
    }).fail(function(){ el.addClass('alert alert-danger').text('Server error'); $f.prepend(el); })
    .always(function(){ $btn.attr('disabled', false).text('Save'); });

  });
});
</script>
