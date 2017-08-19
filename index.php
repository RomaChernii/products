<?php
    include('product.php');
    $product = new Product();
    if (isset($_FILES['filename'])) {
        $product->validator(); 
        $product->saveProducts();
        if (isset($product->messages)) {
            foreach ($product->messages as $message) {
                echo $message;
            }
        }   
    }    
?>

<meta charset="utf-8">
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Warehouses</title>
        <link href="/public/css/bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <div class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <h3><p><b class="navbar-brand">Warehouses</b></p></h3>
                </div>
                <div class="collapse navbar-collapse">
                    <h4><p><b class="navbar-btn">Form for uploading files </b></p></h4>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="filename[]" multiple>
                        <input type="submit"class="btn btn-info navbar-btn" value="Download">
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-inverse">
                    <tr class="bg-primary">
                        <th>#</th>
                        <th>Product_Name</th>
                        <th>Qty</th>
                        <th>Warehouse</th>
                    </tr>
                </thead>
                <?php
                    $rows = $product->getProducts();
                    $number = 1;
                ?>
                <?php foreach($rows as $row): ?>
                <tbody>
                    <tr class="bg-success">
                        <th scope="row"><?=$number++?></th>
                        <td><?=$row['product_name']?></td>
                        <td><?=$row['qty']?></td>
                        <td><?=$row['warehouses']?></td>
                    </tr>
                </tbody>
                <?php endforeach;?>
            </table>
        </div>
    </body>
</html>
