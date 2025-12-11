<?php

require_once "DBConnection.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use null defaults so we don't accidentally treat unsigned users as cashiers/admins
$current_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$current_user_type = isset($_SESSION['type']) ? intval($_SESSION['type']) : null; // 1 admin, 0 cashier, null = not logged

// Helper: escape output
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

// Fetch list of cashiers (for selects) - always read current DB state and treat NULL as cashier (0)
function get_cashiers($conn){
    $sql = "SELECT user_id, fullname FROM `user_list` WHERE COALESCE(`type`,0) = 0 ORDER BY fullname ASC";
    $res = $conn->query($sql);
    $items = [];
    if ($res) {
        while($r = $res->fetch_assoc()) $items[] = $r;
        $res->free();
    }
    return $items;
}

// Fetch single shift if editing via GET
$editing = false;
$shift = null;
if (isset($_GET['shift_id']) && is_numeric($_GET['shift_id'])) {
    $editing = true;
    $shift_id = intval($_GET['shift_id']);
    $stmt = $conn->prepare("SELECT * FROM `cashier_shifts` WHERE shift_id = ?");
    $stmt->bind_param('i', $shift_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $shift = $res->fetch_assoc() ?: null;
    $stmt->close();
}
// If current user is cashier, fetch their details (only when we have a valid logged-in cashier id)
$cashier = null;
// Admin sees all shifts; cashiers only see their own shifts
if ($current_user_type === 0 && $current_user_id !== null) {
    // Cashier: show only their shifts
    $stmt = $conn->prepare("SELECT cs.*, u.fullname as cashier FROM `cashier_shifts` cs LEFT JOIN `user_list` u ON cs.cashier_id = u.user_id WHERE cs.cashier_id = ? ORDER BY cs.shift_date DESC, cs.time_in DESC");
    if ($stmt) {
        $stmt->bind_param('i', $current_user_id);
        $stmt->execute();
        $list_qry = $stmt->get_result();
        // fallback to an empty result if get_result failed
        if (!$list_qry) {
            $list_qry = $conn->query("SELECT * FROM `cashier_shifts` WHERE 0");
        }
        $stmt->close();
    } else {
        // if prepare failed, return empty result to avoid exposing data
        $list_qry = $conn->query("SELECT * FROM `cashier_shifts` WHERE 0");
    }
} else {
    // Admin or not-logged-in: show all shifts
    $list_sql = "SELECT cs.*, u.fullname as cashier FROM `cashier_shifts` cs LEFT JOIN `user_list` u ON cs.cashier_id = u.user_id ORDER BY cs.shift_date DESC, cs.time_in DESC";
    $list_qry = $conn->query($list_sql);
    // fallback to an empty result if the query failed for any reason
    if (!$list_qry) {
        $list_qry = $conn->query("SELECT * FROM `cashier_shifts` WHERE 0");
    }
}
// For listing shifts
// Use LEFT JOIN so shifts remain visible even if the user record was modified/removed,
// and so newly added cashiers are picked up by the JOIN when present.
$list_sql = "SELECT cs.*, u.fullname as cashier FROM `cashier_shifts` cs LEFT JOIN `user_list` u ON cs.cashier_id = u.user_id ORDER BY cs.shift_date DESC, cs.time_in DESC";
$list_qry = $conn->query($list_sql);
// fallback to an empty result if the query failed for any reason (prevents fatal errors later)
if (!$list_qry) {
    $list_qry = $conn->query("SELECT * FROM `cashier_shifts` WHERE 0");
}

$cashiers = get_cashiers($conn);
?>
<!-- Add Shift (card) + Shifts list -->
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Start New Shift</h5>
                    <small class="text-white-50">Quick entry</small>
                </div>
                <div class="card-body">
                    <form id="shift-form" class="needs-validation" novalidate>
                        <input type="hidden" name="shift_id" value="<?php echo e($shift['shift_id'] ?? ''); ?>">
                        <div class="mb-3">
                            <label class="form-label">Cashier</label>
                            <?php if ($current_user_type === 1): // admin sees select ?>
                                <select name="cashier_id" id="cashier_id_add" class="form-select form-select-sm" required>
                                    <option value="" disabled <?php echo !isset($shift['cashier_id']) ? 'selected' : ''; ?>>Select cashier</option>
                                    <?php foreach($cashiers as $c): ?>
                                        <option value="<?php echo e($c['user_id']) ?>" <?php echo (isset($shift['cashier_id']) && $shift['cashier_id']==$c['user_id'])?'selected':''; ?>><?php echo e($c['fullname']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: // cashier sees readonly ?>
                                <input type="hidden" name="cashier_id" value="<?php echo e($cashier['user_id'] ?? ''); ?>">
                                <input type="text" class="form-control form-control-sm" value="<?php echo e($cashier['fullname'] ?? '') ?>" readonly>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Starting Cash</label>
                                <input type="number" step="0.01" min="0" max="1000" name="starting_cash" id="starting_cash_add" class="form-control form-control-sm text-end" required value="<?php echo e($shift['starting_cash'] ?? '') ?>" oninput="if(this.value!=='' && parseFloat(this.value)>1000) this.value=1000;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Starting Inventory</label>
                                <input type="number" step="1" min="0" max="9999" name="starting_inventory" id="starting_inventory_add" class="form-control form-control-sm text-end" required value="<?php echo e($shift['starting_inventory'] ?? '') ?>" oninput="if(this.value!=='' && parseInt(this.value,10)>=10000) this.value=9999;">
                            </div>
                        </div>

                        <div class="mb-3 mt-2">
                            <label class="form-label small">Shift Date</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="shift_date" id="shift_date_add" class="form-control form-control-sm" required value="<?php echo e(isset($shift['shift_date'])?date('Y-m-d', strtotime($shift['shift_date'])):date('Y-m-d')) ?>">
                                <button type="button" class="btn btn-outline-secondary" id="set-shift-date-add">Today</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Time In</label>
                            <div class="input-group input-group-sm">
                                <input type="time" name="time_in" id="time_in_add" class="form-control form-control-sm" required value="<?php echo e(isset($shift['time_in'])?date('H:i', strtotime($shift['time_in'])):'') ?>">
                                <button type="button" class="btn btn-outline-secondary" id="set-time-in-add">Now</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Notes</label>
                            <textarea name="notes" id="notes_add" class="form-control form-control-sm" rows="3"><?php echo e($shift['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" id="submit-add" class="btn btn-success btn-sm">Add Shift</button>
                                <?php if($editing): ?>
                                    <a href="manage_shifts.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">All times local</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Shifts</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" id="refresh-list">Refresh</button>
                        <button class="btn btn-sm btn-outline-secondary" id="print-list">Print</button>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive" style="max-height:520px; overflow:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Cashier</th>
                                    <th class="text-end">Start Cash</th>
                                    <th class="text-end">End Cash</th>
                                    <th class="text-end">Start Inv</th>
                                    <th class="text-end">End Inv</th>
                                    <th>In</th>
                                    <th>Out</th>
                                    <th class="text-end">Sales</th>
                                    <th>Notes</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i = 1;
                                    while($row = $list_qry->fetch_assoc()):
                                ?>
                                    <tr class="bg-light">
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo e(date('Y-m-d', strtotime($row['shift_date']))); ?></td>
                                        <td><?php echo e($row['cashier']); ?></td>
                                        <td class="text-end"><?php echo isset($row['starting_cash']) ? number_format((float)$row['starting_cash'], 2) : '-'; ?></td>
                                        <td class="text-end"><?php echo isset($row['ending_cash']) ? number_format((float)$row['ending_cash'], 2) : '-'; ?></td>
                                        <td class="text-end"><?php echo isset($row['starting_inventory']) ? number_format((int)$row['starting_inventory']) : '-'; ?></td>
                                        <td class="text-end"><?php echo isset($row['ending_inventory']) ? number_format((int)$row['ending_inventory']) : '-'; ?></td>
                                        <td><?php echo e($row['time_in'] ? date('H:i', strtotime($row['time_in'])) : '-'); ?></td>
                                        <td><?php echo e($row['time_out'] ? date('H:i', strtotime($row['time_out'])) : '-'); ?></td>
                                        <td class="text-end"><?php echo isset($row['sales']) ? number_format((float)$row['sales'], 2) : '-'; ?></td>
                                        <td style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo e($row['notes']); ?></td>
                                        <td class="text-center">
                                            <?php
                                                $showActions = false;
                                                if ($current_user_type === 1) {
                                                    // admins can always act
                                                    $showActions = true;
                                                } elseif ($current_user_type === 0 && $current_user_id !== null && isset($row['cashier_id'])) {
                                                    // cashiers can only act on their own shifts
                                                    if (intval($row['cashier_id']) === intval($current_user_id)) {
                                                        $showActions = true;
                                                    }
                                                }
                                            ?>
                                            <?php if ($showActions): ?>
                                                <button class="btn btn-sm btn-primary edit-shift" data-id="<?php echo e($row['shift_id']); ?>">Edit</button>
                                                <button class="btn btn-sm btn-danger ms-1 delete-shift" data-id="<?php echo e($row['shift_id']); ?>">Delete</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($list_qry->num_rows == 0): ?>
                                    <tr><td colspan="12" class="text-center small text-muted">No shifts found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="edit-shift-form" class="needs-validation" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Edit Shift</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="shift_id" id="edit_shift_id">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Cashier</label>
                    <?php if ($current_user_type === 1): ?>
                        <select name="cashier_id" id="edit_cashier_id" class="form-select form-select-sm">
                            <option value="" disabled>Select cashier</option>
                            <?php foreach($cashiers as $c): ?>
                                <option value="<?php echo e($c['user_id']) ?>"><?php echo e($c['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" id="edit_cashier_id" name="cashier_id" value="<?php echo e($cashier['user_id'] ?? '') ?>">
                        <input type="text" class="form-control form-control-sm" id="edit_cashier_name" readonly value="<?php echo e($cashier['fullname'] ?? '') ?>">
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Start Cash</label>
                    <input type="number" step="0.01" min="0" max="1000" class="form-control form-control-sm text-end" id="edit_starting_cash" name="starting_cash" required oninput="if(this.value!=='' && parseFloat(this.value)>1000) this.value=1000;">
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Start Inv</label>
                    <input type="number" step="1" min="0" class="form-control form-control-sm text-end" id="edit_starting_inventory" name="starting_inventory" required>
                </div>
            </div>

            <hr class="my-2">

            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small">End Cash</label>
                    <input type="number" step="0.01" min="0" max="1000" class="form-control form-control-sm text-end" id="edit_ending_cash" name="ending_cash" oninput="if(this.value!=='' && parseFloat(this.value)>1000) this.value=1000;">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">End Inv</label>
                    <input type="number" step="1" min="0" class="form-control form-control-sm text-end" id="edit_ending_inventory" name="ending_inventory">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Sales</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" id="edit_sales" name="sales">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Time Out</label>
                    <div class="input-group input-group-sm">
                        <input type="time" class="form-control form-control-sm" id="edit_time_out" name="time_out">
                        <button type="button" class="btn btn-outline-secondary" id="set-time-out-modal">Now</button>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <label class="form-label small">Notes</label>
                <textarea class="form-control form-control-sm" id="edit_notes" name="notes" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" id="save-edit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
/* Requires jQuery and Bootstrap JS */
(function($){
    'use strict';

    // Submit add shift
    $('#shift-form').on('submit', function(e){
        e.preventDefault();

        // Required presence checks (prevent empty fields from being treated as 0)
        var cashierSelectExists = $('#cashier_id_add').length;
        var cashierVal = cashierSelectExists ? ($('#cashier_id_add').val() || '') : ($('input[name="cashier_id"]').val() || '');
        var startingCashRaw = $('#starting_cash_add').val();
        var startingInvRaw = $('#starting_inventory_add').val();
        var shiftDateRaw = $('#shift_date_add').val();
        var timeInRaw = $('#time_in_add').val();

        if (cashierSelectExists && (!cashierVal || String(cashierVal).trim() === '')) {
            alert('Please select a cashier.');
            return;
        }
        if (typeof startingCashRaw === 'undefined' || startingCashRaw === null || String(startingCashRaw).trim() === '') {
            alert('Starting cash is required.');
            return;
        }
        if (typeof startingInvRaw === 'undefined' || startingInvRaw === null || String(startingInvRaw).trim() === '') {
            alert('Starting inventory is required.');
            return;
        }

        var startingCash = parseFloat(startingCashRaw);
        var startingInv = parseInt(startingInvRaw, 10);
        var MAX_STARTING_CASH = 1000;
        var MAX_STARTING_INV = 10000; // inventory must be < 10000

        if (isNaN(startingCash) || isNaN(startingInv)) {
            alert('Please enter valid numeric values for starting cash and inventory.');
            return;
        }
        if (startingCash < 0 || startingInv < 0) {
            alert('Starting cash and starting inventory cannot be negative.');
            return;
        }
        // Require starting inventory to be strictly less than MAX_STARTING_INV (i.e. < 10000)
        if (startingCash > MAX_STARTING_CASH || startingInv >= MAX_STARTING_INV) {
            alert('Starting cash must be less than or equal to ' + MAX_STARTING_CASH + ' and starting inventory must be less than ' + MAX_STARTING_INV + '.');
            return;
        }

        var $btn = $('#submit-add');
        $btn.prop('disabled', true).text('Saving...');
        var form = new FormData(this);
        $.ajax({
            url: './Actions.php?a=save_shift',
            method: 'POST',
            data: form,
            cache: false, contentType: false, processData: false, dataType: 'json'
        }).done(function(resp){
            if(resp && resp.status === 'success'){
                alert(resp.msg);
                location.reload();
            } else {
                alert(resp.msg || 'Failed to save.');
            }
        }).fail(function(){
            alert('Error while saving.');
        }).always(function(){ $btn.prop('disabled', false).text('Add Shift'); });
    });

    // Edit button click: fetch details and populate modal
    $(document).on('click', '.edit-shift', function(){
        var id = $(this).data('id');
        $.post('./Actions.php?a=get_shift_details', { shift_id: id }, function(resp){
            if(resp && resp.status === 'success'){
                var s = resp.data || {};

                // Fill fields defensively: Actions.php may return different key names
                $('#edit_shift_id').val(s.shift_id || s.id || '');
                // Try multiple possible cashier name/id keys
                var cashierId = s.cashier_id || s.user_id || s.cashierId || '';
                var cashierName = s.cashier_name || s.fullname || s.cashier || '';

                // If the select exists (admin), set it; otherwise set hidden input + readonly name
                if ($('#edit_cashier_id').is('select')) {
                    $('#edit_cashier_id').val(cashierId || '');
                } else {
                    $('#edit_cashier_id').val(cashierId || '');
                    $('#edit_cashier_name').val(cashierName || '');
                }

                $('#edit_starting_cash').val((typeof s.starting_cash !== 'undefined') ? s.starting_cash : '');
                $('#edit_starting_inventory').val((typeof s.starting_inventory !== 'undefined') ? s.starting_inventory : '');
                $('#edit_ending_cash').val((typeof s.ending_cash !== 'undefined') ? s.ending_cash : '');
                $('#edit_ending_inventory').val((typeof s.ending_inventory !== 'undefined') ? s.ending_inventory : '');
                $('#edit_sales').val((typeof s.sales !== 'undefined') ? s.sales : '');
                $('#edit_notes').val((typeof s.notes !== 'undefined') ? s.notes : '');

                // Handle various possible time formats: "HH:MM:SS", "YYYY-MM-DD HH:MM:SS", or "HH:MM"
                var timeOutRaw = s.time_out || s.timeOut || '';
                var timeVal = '';
                if (timeOutRaw) {
                    if (timeOutRaw.indexOf(' ') !== -1) {
                        // datetime: "YYYY-MM-DD HH:MM:SS"
                        var parts = timeOutRaw.split(' ');
                        timeVal = (parts[1] || '').substring(0,5);
                    } else {
                        timeVal = timeOutRaw.substring(0,5);
                    }
                }
                $('#edit_time_out').val(timeVal);

                // show edit modal (Bootstrap 5)
                var modalEl = document.getElementById('editShiftModal');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                alert(resp.msg || 'Failed to fetch shift details.');
            }
        }, 'json').fail(function(){ alert('Error fetching shift details.'); });
    });

    // Submit edit shift (separate handler)
    $('#edit-shift-form').on('submit', function(e){
        e.preventDefault();

        var sc = parseFloat($('#edit_starting_cash').val() || 0);
        var si = parseInt($('#edit_starting_inventory').val() || 0, 10);
        var ec = parseFloat($('#edit_ending_cash').val() || 0);
        var ei = parseInt($('#edit_ending_inventory').val() || 0, 10);
        var MAX_STARTING_CASH = 1000;
        var MAX_STARTING_INV = 10000;

        if (isNaN(sc) || isNaN(si) || isNaN(ec) || isNaN(ei)) {
            alert('Please enter valid numeric values.');
            return;
        }
        if (sc < 0 || si < 0 || ec < 0 || ei < 0) {
            alert('Cash and inventory values cannot be negative.');
            return;
        }
        // Enforce strict upper bound for inventory (< 10000)
        if (sc > MAX_STARTING_CASH || si >= MAX_STARTING_INV || ec > MAX_STARTING_CASH || ei >= MAX_STARTING_INV) {
            alert('Cash and inventory values exceed allowed limits (max cash: ' + MAX_STARTING_CASH + ', inventory must be less than ' + MAX_STARTING_INV + ').');
            return;
        }

        var $btn = $('#save-edit');
        $btn.prop('disabled', true).text('Saving...');
        $.post('./Actions.php?a=update_shift', $('#edit-shift-form').serialize(), function(resp){
            if(resp && resp.status === 'success'){
                alert(resp.msg);
                location.reload();
            } else {
                alert(resp.msg || 'Failed to update.');
                $btn.prop('disabled', false).text('Save changes');
            }
        }, 'json').fail(function(){ alert('Error saving shift.'); $btn.prop('disabled', false).text('Save changes'); });
    });

    // Delete
    $(document).on('click', '.delete-shift', function(){
        if(!confirm('Delete this shift?')) return;
        var id = $(this).data('id');
        $.post('./Actions.php?a=delete_shift', { shift_id: id }, function(resp){
            alert(resp.msg || 'Action result');
            if(resp && resp.status === 'success') location.reload();
        }, 'json').fail(function(){ alert('Error deleting.'); });
    });

    // Utilities: set times/dates
    $('#set-shift-date-add').on('click', function(){
        $('#shift_date_add').val(new Date().toISOString().slice(0,10));
    });
    $('#set-time-in-add').on('click', function(){
        var d = new Date(), hh = String(d.getHours()).padStart(2,'0'), mm = String(d.getMinutes()).padStart(2,'0');
        $('#time_in_add').val(hh+':'+mm);
    });
    $('#set-time-out-modal').on('click', function(){
        var d = new Date(), hh = String(d.getHours()).padStart(2,'0'), mm = String(d.getMinutes()).padStart(2,'0');
        $('#edit_time_out').val(hh+':'+mm);
    });

    // Refresh & print
    $('#refresh-list').on('click', function(){ location.reload(); });
    $('#print-list').on('click', function(){ window.print(); });

    // Basic bootstrap form validation (visual feedback)
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })();

})(jQuery);
</script>
