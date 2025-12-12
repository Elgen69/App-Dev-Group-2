<?php
// home.php (replace your current home.php content with this block)
// Assumes DBConnection.php already required earlier in your page and
// helper function format_num($n) exists.

?>
<style>
:root{
    --radius:12px;
    --card-bg:#ffffff;
    --glow-opacity:.45;
    --glow-blur:36px;
    --btn-start:#2d2d2d;
    --btn-end:#5a5a5a;
    --btn-text:#ffffff;
}

.glowing-border{
    position:relative;
    padding:1rem;
    border-radius:var(--radius);
    background:var(--card-bg);
    overflow:hidden;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
}

.glowing-border::before{
    content:"";
    position:absolute;
    inset:-2px;
    z-index:0;
    background: linear-gradient(90deg, #00ffd1, #00bfff, #9b59b6, #ff7f50, #ffd700);
    filter: blur(var(--glow-blur));
    opacity:var(--glow-opacity);
    transform: scale(1.02);
    transition: opacity .3s ease;
    animation: glow-slide 6s linear infinite;
    pointer-events:none;
}
.glowing-border > *{ position:relative; z-index:1; }
@keyframes glow-slide{
    0%{ transform: translateX(-30%) scale(1.02) rotate(0deg); opacity:.45; }
    50%{ transform: translateX(30%) scale(1.03) rotate(1deg); opacity:.55; }
    100%{ transform: translateX(-30%) scale(1.02) rotate(0deg); opacity:.45; }
}
@media (prefers-reduced-motion: reduce){
    .glowing-border::before{ animation:none; opacity:.25; filter: blur(18px); }
}
.btn-gradient{
    display:inline-block;
    background: linear-gradient(135deg, var(--btn-start), var(--btn-end));
    color: var(--btn-text);
    border:none;
    padding:6px 12px;
    border-radius:8px;
    cursor:pointer;
    box-shadow:0 4px 8px rgba(0,0,0,0.08);
    transition: transform .12s ease, box-shadow .12s ease;
    font-weight:600;
}
.btn-gradient:hover{ transform: translateY(-2px); box-shadow:0 8px 18px rgba(0,0,0,0.12); }
.btn-gradient:focus{ outline:3px solid rgba(0,187,255,0.18); outline-offset:2px; }
@media (max-width:576px){
    .glowing-border{ padding:.8rem; border-radius:10px; }
    .btn-gradient{ padding:5px 10px; font-size:.95rem; }
}
</style>

<div class="content py-3 glowing-border">
    <div class="card rounded-0 shadow">
        <div class="card-body">
            <h3 style="font-family: 'Pacifico', cursive; background: linear-gradient(145deg, #00ffbd, #00bfff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                Julie's Bakery Shop Management System
            </h3>
            <hr style="border-top: 1px solid rgba(0,0,0,0.1);">
            <div class="col-12">
                <div class="row gx-3 row-cols-1 row-cols-md-2 row-cols-lg-4">
                    <!-- Categories card -->
                    <div class="col mb-4">
                        <div class="card text-dark h-100" style="border-radius: 15px; background-color: #f8f9fa;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="col-auto pe-3">
                                        <span class="fa fa-th-list fs-3 text-primary"></span>
                                    </div>
                                    <div class="col-auto flex-grow-1">
                                        <div class="fs-5"><b>Categories</b></div>
                                        <div class="fs-6 text-end fw-bold">
                                            <?php 
                                            $category = $conn->query("SELECT COUNT(category_id) as `count` FROM `category_list` WHERE delete_flag = 0")->fetch_array()['count'];
                                            echo $category > 0 ? format_num($category) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products card -->
                    <div class="col mb-4">
                        <div class="card text-dark h-100" style="border-radius: 15px; background-color: #f8f9fa;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="col-auto pe-3">
                                        <span class="fas fa-shopping-bag fs-3 text-secondary"></span>
                                    </div>
                                    <div class="col-auto flex-grow-1">
                                        <div class="fs-5"><b>Products</b></div>
                                        <div class="fs-6 text-end fw-bold">
                                            <?php 
                                            $product = $conn->query("SELECT COUNT(product_id) as `count` FROM `product_list` WHERE delete_flag = 0")->fetch_array()['count'];
                                            echo $product > 0 ? format_num($product) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Stocks card (computed from stock_list & transaction_items) -->
                    <div class="col mb-4">
                        <div class="card text-dark h-100" style="border-radius: 15px; background-color: #f8f9fa;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="col-auto pe-3">
                                        <span class="fa fa-file-alt fs-3 text-info"></span>
                                    </div>
                                    <div class="col-auto flex-grow-1">
                                        <div class="fs-5"><b>Total Stocks</b></div>
                                        <div class="fs-6 text-end fw-bold">
                                            <?php 
                                            // Compute total across products by aggregating using subqueries to avoid duplicates
                                            $stock_total = 0;
                                            $stock_q = $conn->query("
                                                SELECT p.product_id,
                                                    COALESCE((SELECT SUM(s.quantity) FROM stock_list s WHERE s.product_id = p.product_id AND s.expiry_date >= CURDATE()),0) as stock_in,
                                                    COALESCE((SELECT SUM(ti.quantity) FROM transaction_items ti WHERE ti.product_id = p.product_id),0) as stock_out
                                                FROM product_list p
                                                WHERE p.delete_flag = 0
                                            ");
                                            while($r = $stock_q->fetch_assoc()){
                                                $in = (int)$r['stock_in'];
                                                $out = (int)$r['stock_out'];
                                                $qty = max($in - $out, 0);
                                                $stock_total += $qty;
                                            }
                                            echo $stock_total > 0 ? format_num($stock_total) : 0;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Sales card -->
                    <div class="col mb-4">
                        <div class="card text-dark h-100" style="border-radius: 15px; background-color: #f8f9fa;">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="col-auto pe-3">
                                        <span class="fa fa-coins fs-3 text-warning"></span>
                                    </div>
                                    <div class="col-auto flex-grow-1">
                                        <div class="fs-5"><b>Today's Sales</b></div>
                                        <div class="fs-6 text-end fw-bold">
                                            <?php 
                                            $sales_q = $conn->query("SELECT COALESCE(SUM(total),0) as total FROM transaction_list WHERE DATE(date_added) = DATE(CURRENT_TIMESTAMP) " . (($_SESSION['type'] != 1)? " AND user_id = '{$_SESSION['user_id']}' " : ""));
                                            $sales = $sales_q->fetch_array()['total'];
                                            echo $sales > 0 ? format_num($sales) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- .row cards -->
                <hr style="border-top: 1px solid rgba(0,0,0,0.1);">

                <h3 class="mb-4" style="font-family: 'Arial', sans-serif; font-weight: bold; color: #333;">
                    Stock Available
                    <select id="categoryFilter" class="form-select d-inline-block w-auto ms-3">
                        <option value="all">All Categories</option>
                        <?php
                        $categories = $conn->query("SELECT category_id, name FROM `category_list` WHERE delete_flag = 0 ORDER BY name ASC");
                        while ($category = $categories->fetch_assoc()):
                        ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </h3>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="inventory">
                        <colgroup>
                            <col width="25%">
                            <col width="25%">
                            <col width="25%">
                            <col width="25%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="py-0 px-1">Category</th>
                                <th class="py-0 px-1">Product Code</th>
                                <th class="py-0 px-1">Product Name</th>
                                <th class="py-0 px-1">Available Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            /* Single query per product with subqueries for accurate stock counts.
                               stock_in : sum of stock_list.quantity where expiry_date >= today
                               stock_out: sum of transaction_items.quantity for the product
                            */
                            $sql = "
                                SELECT p.product_id, p.product_code, p.name AS pname, p.alert_restock,
                                    c.category_id, c.name AS cname,
                                    COALESCE((SELECT SUM(s.quantity) FROM stock_list s WHERE s.product_id = p.product_id AND s.expiry_date >= CURDATE()),0) AS stock_in,
                                    COALESCE((SELECT SUM(ti.quantity) FROM transaction_items ti WHERE ti.product_id = p.product_id),0) AS stock_out
                                FROM product_list p
                                INNER JOIN category_list c ON p.category_id = c.category_id
                                WHERE p.status = 1 AND p.delete_flag = 0
                                ORDER BY c.name ASC, p.product_code ASC
                            ";
                            $qry = $conn->query($sql);
                            while($row = $qry->fetch_assoc()):
                                $stock_in = (int)$row['stock_in'];
                                $stock_out = (int)$row['stock_out'];
                                $qty = $stock_in - $stock_out;
                                $qty = $qty > 0 ? $qty : 0;
                            ?>
                            <tr class="<?php echo ($qty < 6) ? 'bg-danger bg-opacity-25' : ''; ?>" data-category-id="<?php echo $row['category_id']; ?>">
                                <td class="td py-0 px-1"><?php echo htmlspecialchars($row['cname']) ?></td>
                                <td class="td py-0 px-1"><?php echo htmlspecialchars($row['product_code']) ?></td>
                                <td class="td py-0 px-1"><?php echo htmlspecialchars($row['pname']) ?></td>
                                <td class="td py-0 px-1 text-end">
                                    <?php if(isset($_SESSION['type']) && $_SESSION['type'] == 1 && $qty < (int)$row['alert_restock']): ?>
                                        <button type="button" class="btn btn-sm btn-gradient restock" data-pid="<?php echo (int)$row['product_id']; ?>" data-name="<?php echo htmlspecialchars($row['product_code'].' - '.$row['pname']); ?>">
                                            Restock
                                        </button>
                                    <?php endif; ?>
                                    <?php echo $qty; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div> <!-- .table-responsive -->
            </div> <!-- .col-12 -->
        </div> <!-- .card-body -->
    </div> <!-- .card -->
</div> <!-- .content -->

<script>
$(function(){
    // open restock modal (admin only). small guard: ensure pid exists
    $(document).on('click', '.restock', function(){
        var pid = $(this).data('pid');
        var name = $(this).data('name') || '';
        if(!pid) return;
        uni_modal('Add New Stock for <span class="text-primary">' + name + '</span>', 'manage_stock.php?pid=' + pid + '&restock=1');
    });

    // DataTable init (static table appearance; no search/paging)
    if($.fn.dataTable){
        $('table#inventory').dataTable({
            paging:false,
            searching:false,
            info:false,
            ordering:false,
            autoWidth: false
        });
    }
});

// category filter (scoped to this table)
document.addEventListener('DOMContentLoaded', function() {
    var catFilter = document.getElementById('categoryFilter');
    if(!catFilter) return;
    catFilter.addEventListener('change', function() {
        var categoryId = this.value;
        var rows = document.querySelectorAll('#inventory tbody tr');
        rows.forEach(function(row) {
            if (categoryId === 'all') {
                row.style.display = '';
            } else {
                if (row.getAttribute('data-category-id') === categoryId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
});
</script>
