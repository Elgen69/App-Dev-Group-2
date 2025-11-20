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

/* Subtle animated glow using a pseudo-element so inner content isn't affected */
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

/* Keep content above the glow */
.glowing-border > *{ position:relative; z-index:1; }

/* Calmer keyframes for a smooth movement */
@keyframes glow-slide{
    0%{ transform: translateX(-30%) scale(1.02) rotate(0deg); opacity:.45; }
    50%{ transform: translateX(30%) scale(1.03) rotate(1deg); opacity:.55; }
    100%{ transform: translateX(-30%) scale(1.02) rotate(0deg); opacity:.45; }
}

/* Respect users who prefer reduced motion */
@media (prefers-reduced-motion: reduce){
    .glowing-border::before{ animation:none; opacity:.25; filter: blur(18px); }
}

/* Improved accessible gradient button */
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

/* Small-screen adjustments */
@media (max-width:576px){
    .glowing-border{ padding:.8rem; border-radius:10px; }
    .btn-gradient{ padding:5px 10px; font-size:.95rem; }
}
</style>


<div class="content py-3 glowing-border">
    <div class="card rounded-0 shadow">
        <div class="card-body">
        <h3 style="font-family: 'Pacifico', cursive; background: linear-gradient(145deg, #00ffbd, #00bfff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Welcome to Julie's Bakery Shop Management System</h3>
            <hr style="border-top: 1px solid rgba(0,0,0,0.1);">
            <div class="col-12">
                <div class="row gx-3 row-cols-1 row-cols-md-2 row-cols-lg-4">
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
                                            $category = $conn->query("SELECT count(category_id) as `count` FROM `category_list` where delete_flag = 0 ")->fetch_array()['count'];
                                            echo $category > 0 ? format_num($category) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                            $product = $conn->query("SELECT count(product_id) as `count` FROM `product_list` where delete_flag = 0 ")->fetch_array()['count'];
                                            echo $product > 0 ? format_num($product) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                            $stock = 0;
                                            $stock_query = $conn->query("SELECT * FROM `stock_list` where product_id in (SELECT product_id FROM `product_list` where delete_flag = 0) and unix_timestamp(CONCAT(`expiry_date`)) >= unix_timestamp(CURRENT_TIMESTAMP) ");
                                            while($row = $stock_query->fetch_assoc()):
                                                $stock_in = $conn->query("SELECT sum(quantity) as `total` FROM `stock_list` where unix_timestamp(CONCAT(`expiry_date`, ' 23:59:59')) >= unix_timestamp(CURRENT_TIMESTAMP) and product_id = '{$row['product_id']}' ")->fetch_array()['total'];
                                                $stock_out = $conn->query("SELECT sum(quantity) as `total` FROM `transaction_items` where product_id = '{$row['product_id']}' ")->fetch_array()['total'];
                                                $stock_in = $stock_in > 0 ? $stock_in : 0;
                                                $stock_out = $stock_out > 0 ? $stock_out : 0;
                                                $qty = $stock_in - $stock_out;
                                                $qty = $qty > 0 ? $qty : 0;
                                                $stock += $qty;
                                            endwhile;
                                            echo $stock > 0 ? format_num($stock) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                            $sales = $conn->query("SELECT sum(total) as `total` FROM `transaction_list` where date(date_added) = date(CURRENT_TIMESTAMP) ".(($_SESSION['type'] != 1)? " and user_id = '{$_SESSION['user_id']}' " : ""))->fetch_array()[0];
                                            echo $sales > 0 ? format_num($sales) : 0 ;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr style="border-top: 1px solid rgba(0,0,0,0.1);">
                <h3 class="mb-4" style="font-family: 'Arial', sans-serif; font-weight: bold; color: #333;">
                    Stock Available
                    <select id="categoryFilter" class="form-select d-inline-block w-auto ms-3">
                        <option value="all">All Categories</option>
                        <?php
                        $categories = $conn->query("SELECT category_id, name FROM `category_list` WHERE delete_flag = 0");
                        while ($category = $categories->fetch_assoc()):
                        ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
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
                            $sql = "SELECT p.*, c.name as cname, c.category_id FROM `product_list` p INNER JOIN `category_list` c ON p.category_id = c.category_id WHERE p.status = 1 AND p.delete_flag = 0 ORDER BY `cname` ASC";
                            $qry = $conn->query($sql);
                            while($row = $qry->fetch_assoc()):
                                $stock_in = $conn->query("SELECT SUM(quantity) as `total` FROM `stock_list` WHERE `expiry_date` >= CURDATE() AND product_id = '{$row['product_id']}'")->fetch_array()['total'];
                                $stock_out = $conn->query("SELECT SUM(quantity) as `total` FROM `transaction_items` WHERE product_id = '{$row['product_id']}'")->fetch_array()['total'];
                                $stock_in = $stock_in > 0 ? $stock_in : 0;
                                $stock_out = $stock_out > 0 ? $stock_out : 0;
                                $qty = $stock_in - $stock_out;
                                $qty = $qty > 0 ? $qty : 0;
                            ?>
                            <tr class="<?php echo $qty < 6 ? 'bg-danger bg-opacity-25' : ''; ?>" data-category-id="<?php echo $row['category_id']; ?>">
                                <td class="td py-0 px-1"><?php echo $row['cname'] ?></td>
                                <td class="td py-0 px-1"><?php echo $row['product_code'] ?></td>
                                <td class="td py-0 px-1"><?php echo $row['name'] ?></td>
                                <td class="td py-0 px-1 text-end">
                                    <?php if($_SESSION['type'] == 1 && $qty < $row['alert_restock']): ?>
                                        <button type="button" class="btn btn-sm btn-gradient restock" data-pid="<?php echo $row['product_id']; ?>" data-name="<?php echo $row['product_code'].' - '.$row['name']; ?>">
                                            Restock
                                        </button>
                                    <?php endif; ?>
                                    <?php echo $qty ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- $stock_in = $conn->query("SELECT sum(quantity) as `total` FROM `stock_list` where unix_timestamp(CONCAT(`expiry_date`, ' 23:59:59')) >= unix_timestamp(CURRENT_TIMESTAMP) and product_id = '{$row['product_id']}' ")->fetch_array()['total']; -->
<script>
$(function(){
    // When the document is ready, execute the following function

    // Attach a click event handler to elements with the class 'restock'
    $('.restock').click(function(){
        // When an element with the class 'restock' is clicked, execute the following function
        
        // Open a modal dialog to add new stock
        // 'uni_modal' is a custom function to show a modal dialog
        // The title of the modal will include the name of the product, which is retrieved from the 'data-name' attribute of the clicked element
        // The URL for the modal content is 'manage_stock.php' with a query parameter 'pid' set to the product ID, retrieved from the 'data-pid' attribute of the clicked element
        uni_modal('Add New Stock for <span class="text-primary">' + $(this).attr('data-name') + "</span>", "manage_stock.php?pid=" + $(this).attr('data-pid'))
    });

    // Initialize the DataTable plugin on the table with the ID 'inventory'
    $('table#inventory').dataTable({
        // Disable pagination (no page numbers)
        paging: false,
        // Disable the search box
        searching: false,
        // Disable the information display (e.g., "Showing 1 to 10 of 57 entries")
        info: false
    });
    
});

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('categoryFilter').addEventListener('change', function() {
        var categoryId = this.value;
        var rows = document.querySelectorAll('tbody tr');
        
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
