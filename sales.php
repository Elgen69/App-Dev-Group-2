<div class="card shadow rounded-0">
    <div class="card-body">
        <div class="w-100 h-100 d-flex flex-column">
            <div class="row">
                <div class="col-8">
                    <h3>Transaction</h3>
                </div>
                <div class="col-4 d-flex justify-content-end">
                    <button class="btn btn-sm btn-primary rounded-0 " id="transaction-save-btn" type="button">Save Transaction</button>
                </div>
                <div class="clear-fix mb-1"></div>
                <hr>
            </div>
            <style>
                #plist .item,#item-list tr{
                    cursor:pointer
                }
            </style>
            <div class="col-12 flex-grow-1">
            <form action="" class="h-100" id="transaction-form">
                <div class="w-100 h-100 mx-0 row row-cols-2 bg-dark">
                    <div class="col-8 h-100 pb-2 d-flex flex-column">
                        <div>
                            <h3 class="text-light">Please select product below</h3>
                        </div>
                        <div class="flex-grow-1 d-flex flex-column bg-light bg-opacity-50">
                            <div class="form-group py-2 d-flex border-bottom">
                                <label for="search" class="col-auto px-2 fw-bolder text-light">Search</label>
                                <div class="flex-grow-1 col-auto pe-2">
                                    <input type="text" autocomplete="off" class="form-control form-control-sm rounded-0" id="search">
                                </div>
                            </div>
                            <div>
                                <table class="table table-hover table-striped mb-0">
                                    <colgroup>
                                        <col width="25%">
                                        <col width="20%">
                                        <col width="25%">
                                        <col width="10%">
                                        <col width="8%">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="py-0 px-1">Category</th>
                                            <th class="py-0 px-1">Product Code</th>
                                            <th class="py-0 px-1">Product Name</th>
                                            <th class="py-0 px-1">Price</th>
                                            <th class="py-0 px-1">Available Quantity</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="flex-grow-1" >
                                <div class="h-100 overflow-auto" style="height:60vh !important ">
                                    <table class="table table-hover table-striped bg-light" id="plist">
                                        <colgroup>
                                            <col width="25%">
                                            <col width="20%">
                                            <col width="25%">
                                            <col width="15%">
                                            <col width="15%">
                                        </colgroup>
                                        <tbody>
                                        <?php 
                                        $sql = "SELECT p.*,c.name as cname FROM `product_list` p inner join `category_list` c on p.category_id = c.category_id where p.status = 1 and p.delete_flag = 0 order by p.`product_code` asc";
                                        $qry = $conn->query($sql);
                                        while($row = $qry->fetch_assoc()):
                                            $stock_in = $conn->query("SELECT sum(quantity) as `total` FROM `stock_list` where unix_timestamp(`expiry_date`) >= unix_timestamp(CURRENT_TIMESTAMP) and product_id = '{$row['product_id']}' ")->fetch_assoc()['total'];
                                            $stock_out = $conn->query("SELECT sum(quantity) as `total` FROM `transaction_items` where product_id = '{$row['product_id']}' ")->fetch_assoc()['total'];
                                            $stock_in = $stock_in > 0 ? $stock_in : 0;
                                            $stock_out = $stock_out > 0 ? $stock_out : 0;
                                            $qty = $stock_in-$stock_out;
                                            $qty = $qty > 0 ? $qty : 0;
                                        ?>
                                        <tr class="item <?php echo $qty < 6? "bg-danger bg-opacity-25":'' ?>" data-id="<?php echo $row['product_id'] ?>">
                                            <td class="td py-0 px-1 pname"><?php echo $row['cname'] ?></td>
                                            <td class="td py-0 px-1 pcode"><?php echo $row['product_code'] ?></td>
                                            <td class="td py-0 px-1 name"><?php echo $row['name'] ?></td>
                                            <td class="td py-0 px-1 text-end price"><?php echo format_num($row['price']) ?></td>
                                            <td class="td py-0 px-1 text-end qty"><?php echo $qty ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 h-100 py-2">
                        <div class="h-100 d-flex flex-column">
                            <div class="w-100 flex-grow-1">
                                <div class="h-100 d-flex w-100 flex-column">
                                    <div class="d-flex">
                                    <div class="fs-5 fw-bolder text-light flex-grow-1">Items</div>
                                    <div class="col-auto">
                                        <button class="btn btn-danger rounded-0 py-0" type="button" id="remove-item" disabled onclick="remove_item()"><i class="fa fa-trash"></i></button>
                                    </div>
                                    </div>
                                    <div>
                                        <table class="table table-hover table-bordered table-striped bg-secondary m-0">
                                            <colgroup>
                                                <col width="20%">
                                                <col width="65%">
                                                <col width="15%">
                                            </colgroup>  
                                            <thead>
                                                <th class="py-0 px-1 text-center">Qty</th>
                                                <th class="py-0 px-1 text-center">Product</th>
                                                <th class="py-0 px-1 text-center">Total</th>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div style="height:55vh !important" class="overflow-auto bg-light bg-opacity-75">
                                            <table class="table table-hover table-bordered table-striped bg-light" id="item-list">
                                            <colgroup>
                                                <col width="20%">
                                                <col width="65%">
                                                <col width="15%">
                                            </colgroup>  
                                            <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="pt-2">
                                        <div class="w-100 mx-0 d-flex pb-1">
                                            <div class="col-4 pe-2 fs-6 fw-bolder text-light">Sub-Total</div>
                                            <div class="flex-grow-1 bg-light fs-6 fw-bolder text-end px-2" id="subTotal">0.00</div>
                                        </div>
                                        <div class="w-100 mx-0 d-flex pb-1 align-items-center">
                                            <div class="col-4 pe-2 fs-6 fw-bolder text-light">Tax (5%)<small>Inclusive</small></div>
                                            <div class="flex-grow-1 bg-light fs-6 fw-bolder text-end px-2" id="tax">0.00</div>
                                        </div>
                                        <div class="w-100 mx-0 d-flex pb-1">
                                            <div class="col-4 pe-2 fs-6 fw-bolder text-light">Total</div>
                                            <div class="flex-grow-1 bg-light fs-6 fw-bolder text-end px-2" id="total">0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="total" value="0">
                <input type="hidden" name="tendered_amount" value="0">
                <input type="hidden" name="change" value="0">
            </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
  $('#search').on('input', function(){
    var _search = $(this).val().toLowerCase();
    $('#plist tbody tr').each(function(){
      var _text = $(this).text().toLowerCase();
      $(this).toggle(_text.includes(_search));
    });
  });

  // helper to parse numeric text
  function parseNum(s){
    s = (s || '').toString().replace(/,/g,'');
    return isNaN(parseFloat(s)) ? 0 : parseFloat(s);
  }

  // on product click: check available and existing items
  $('#plist tbody').on('click','tr', function(){
    var $tr = $(this);
    var pid = $tr.data('id');
    var available = parseInt($tr.find('.qty').text()) || 0;
    if(available <= 0){
      alert('Out of stock');
      return;
    }
    // current quantity already in cart for this pid
    var $existing = $('#item-list tbody tr[data-id="'+pid+'"]');
    var currentInCart = 0;
    if($existing.length){
      currentInCart = parseInt($existing.find('[name="quantity[]"]').val()) || 0;
    }

    // If already at max, refuse to add more
    if(currentInCart >= available){
      alert('Cannot add more than available stock ('+available+')');
      return;
    }

    // If exists, increment by 1 (but cap at available)
    if($existing.length){
      var newQty = currentInCart + 1;
      if(newQty > available) newQty = available;
      $existing.find('[name="quantity[]"]').val(newQty).trigger('input');
      return;
    }

    // create new item row with quantity 1
    var pcode = $tr.find('.pcode').text();
    var name = $tr.find('.name').text();
    var price = parseNum($tr.find('.price').text());
    var qty = 1;

    var ntr = $("<tr tabindex='0'>").attr('data-id', pid);
    ntr.append('<td class="py-0 px-1 align-middle"><input class="w-100 text-center quantity-input" type="number" name="quantity[]" min="1" value="'+qty+'" max="'+available+'"/>'+
               '<input type="hidden" name="product_id[]" value="'+pid+'"/>'+
               '<input type="hidden" name="price[]" value="'+price+'"/></td>');
    ntr.append('<td class="py-0 px-1 align-middle"><div class="fs-6 mb-0 lh-1">'+pcode+'<br/><span class="name">'+name+'</span><br/>(<span class="price">'+price.toLocaleString()+'</span>)</div></td>');
    ntr.append('<td class="py-0 px-1 align-middle text-end total">'+(price*qty).toLocaleString()+'</td>');
    $('#item-list tbody').append(ntr);
    compute(ntr);
    calculate_total();
  });

  // compute() updated to enforce max by reading available quantity from product list row
  function compute($row){
    $row.find('[name="quantity[]"]').on('input change', function(){
      var $input = $(this);
      var newQty = parseInt($input.val()) || 0;
      if (newQty < 0) newQty = 0;
      // find product id
      var pid = $row.data('id');
      // find available from product list row
      var available = parseInt($('#plist tbody tr[data-id="'+pid+'"]').find('.qty').text()) || 0;
      if(newQty > available){
        newQty = available;
        $input.val(newQty);
        alert('Reached max available: '+available);
      }
      var price = parseNum($row.find('[name="price[]"]').val());
      var total = parseFloat(newQty) * parseFloat(price);
      $row.find('.total').text(parseFloat(total || 0).toLocaleString());
      calculate_total();
    });

    $row.on('focusin', function(){
      $(this).addClass("bg-primary bg-opacity-50 selected-item");
      $('#remove-item').attr('disabled', false);
    });
    $row.on('focusout', function(){
      if($('#remove-item').is(':focus') || $('#remove-item').is(':hover')) return;
      $(this).removeClass("bg-primary bg-opacity-50 selected-item");
      $('#remove-item').attr('disabled', true);
    });
  }

  // calculate totals (subtotal, tax, grand total)
  function calculate_total(){
    var subtotal = 0;
    $('#item-list tbody tr').each(function(){
      var totalText = $(this).find('.total').text();
      subtotal += parseNum(totalText);
    });
    var tax = parseFloat(subtotal) * 0.05; // 5% tax
    var grand_total = parseFloat(subtotal) + parseFloat(tax);
    $('#subTotal').text(parseFloat(subtotal || 0).toLocaleString());
    $('#tax').text(parseFloat(tax || 0).toLocaleString());
    $('#total').text(parseFloat(grand_total || 0).toLocaleString());
    $('[name="total"]').val(parseFloat(grand_total || 0));
  }

  // remove item button
  $('#remove-item').on('click', function(){
    $('#item-list tr.selected-item').remove();
    calculate_total();
    $('#remove-item').attr('disabled', true);
  });

  $('#transaction-save-btn').click(function(){
    if($('#item-list tbody tr').length <= 0){
      alert("Please add at least 1 item.");
      return false;
    }
    // before opening payment, final validation: ensure none of the requested qty exceed availability
    var ok = true;
    $('#item-list tbody tr').each(function(){
      var pid = $(this).data('id');
      var want = parseInt($(this).find('[name="quantity[]"]').val()) || 0;
      var avail = parseInt($('#plist tbody tr[data-id="'+pid+'"]').find('.qty').text()) || 0;
      if(want <= 0){ ok = false; alert('Quantity must be > 0'); return false; }
      if(want > avail){ ok = false; alert('Requested more than available for product ID '+pid); return false; }
    });
    if(!ok) return false;

    uni_modal("Payment","tender_amount.php?amount="+$('[name="total"]').val());
  });

  // form submit remains same: do server-side check as well
  $('#transaction-form').submit(function(e){
    e.preventDefault();
    var $btn = $('#transaction-save-btn');
    $btn.attr('disabled',true);
    $('.pop_msg').remove();
    var _this = $(this);
    var _el = $('<div>').addClass('pop_msg');

    $.ajax({
      url:'./Actions.php?a=save_transaction',
      data: new FormData(_this[0]),
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      dataType: 'json'
    }).done(function(resp){
      if(resp && resp.status == 'success'){
        setTimeout(function(){ uni_modal("RECEIPT","view_receipt.php?id="+resp.transaction_id); }, 1000);
      } else {
        _el.addClass('alert alert-danger').text(resp && resp.msg ? resp.msg : 'Save failed'); _this.prepend(_el);
      }
    }).fail(function(){ _el.addClass('alert alert-danger').text('Server error'); _this.prepend(_el); })
    .always(function(){ $btn.attr('disabled', false); });
  });

  // disable Enter default in form
  $('#transaction-form input').keydown(function(e){ if(e.which == 13){ e.preventDefault(); return false; } });

});
</script>
