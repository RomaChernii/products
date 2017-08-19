<?php
class Product {
    public $datab;

    public function __construct($host = 'localhost', $db = 'csv', $user = 'root', $pass = '', $charset = 'utf8') {
        try {
            $opt = array(
                PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            );

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $this->datab = new PDO($dsn, $user, $pass, $opt);
            $sql = "CREATE TABLE IF NOT EXISTS warehouses (
                    wh_id  INT AUTO_INCREMENT NOT NULL,
                    warehouses  VARCHAR(50) NOT NULL,
                    PRIMARY KEY(wh_id)
                    ) ENGINE=InnoDB CHARACTER SET=UTF8;
                    CREATE TABLE products (
                    id  INT AUTO_INCREMENT NOT NULL,
                    product_name  VARCHAR(150) NOT NULL,
                    qty INT (11) NOT NULL,
                    wh_id  INT NOT NULL,
                    PRIMARY KEY(id),
                    FOREIGN KEY (wh_id) REFERENCES warehouses(wh_id))
                    ENGINE=InnoDB CHARACTER SET=UTF8;";

            $this->datab->exec($sql);
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }   
    }

    public function validator() {
        $valid_types = array('txt', 'csv');
        $uploads_dir = 'files/';

        if (isset($_FILES['filename'])) {
            for ($i = 0; $i < count($_FILES['filename']['name']); $i++) {
                $name = $_FILES['filename']['name'][$i];
                $ext = explode('.', $name);

                if (!empty($ext[1]) && is_uploaded_file($_FILES['filename']['tmp_name'][$i])) {
                    $max_size = ini_get('post_max_size');
                    preg_match('/[0-9]+/', $max_size, $match);
                    $size = $match[0] * 1024 * 1024;

                    if ($_FILES['filename']['size'][$i] > $size) {
                        $this->messages['size'] = '<div class="alert alert-danger col-md-12 text-center">Error: File size > "' . $size . '".</div>';
                    }
                    elseif (!in_array($ext[1], $valid_types)) {
                        $this->messages['type'] =  '<div class="alert alert-danger col-md-12 text-center">Error: Invalid file type.</div>';
                    }
                    else {
                        move_uploaded_file($_FILES['filename']['tmp_name'][$i], $uploads_dir.$name);
                        $this->files[] = $uploads_dir.$name;
                        $this->messages['success'] = '<div class="alert alert-success col-md-12 text-center">Moved file to destination directory.</div>';
                    }
                }
            }
        }
    }

    public function saveProducts() {
        if (!empty($this->files)) {
            foreach ($this->files as $file_path) {
                $file = file_get_contents($file_path);
                $lines = explode(PHP_EOL, $file);
                $data = array();

                foreach ($lines as $key => $line) {
                    if ($key == 0) {
                        continue;
                    }
                    $line = str_getcsv($line);
                    $data[] = reset($line);
                }

                foreach ($data as $row) {
                    $row = str_getcsv($row, ";");
                    if (is_numeric($row[1]) && is_string($row[0]) && is_string($row[2])) {
                        if ($elements = $this->datab->query("SELECT * FROM products p, warehouses w WHERE p.wh_id=w.wh_id and w.warehouses='" . $row[2] . "'")->fetchAll()) {
                            foreach ($elements as $element) {
                                if ($element['product_name'] == $row[0]) {
                                    $updated = TRUE;
                                    $qty = $row[1] + $element['qty'];
                                    $this->datab->query("UPDATE products SET qty='" . $qty . "' WHERE product_name='" . $row[0] . "' and wh_id='" . $element['wh_id'] . "'");
                                } 
                            }
                            if (empty($updated)) {
                                $this->datab->query("INSERT INTO products  (product_name, qty, wh_id ) VALUES ('$row[0]', '$row[1]', '" . $element['wh_id'] . "')");
                            }
                        }
                        else {
                            $this->datab->query("INSERT INTO warehouses (warehouses) VALUES('$row[2]')");
                            $id = $this->datab->lastInsertId();
                            $this->datab->query("INSERT INTO products  (product_name, qty, wh_id ) VALUES ('$row[0]', '$row[1]', '" . $id . "')");
                        }
                    }
                    else {
                        $this->messages['data'] = '<div class="alert alert-danger col-md-12 text-center">File data not valid</div>';
                    }
                }
                @unlink($file_path);
            }
        }
    }

    public function getProducts() {
        try {
            return $this->datab->query('SELECT * FROM products p, warehouses w WHERE p.wh_id=w.wh_id AND p.qty > 0')->fetchAll();
        }
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

}
