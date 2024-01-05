<?php
require_once 'connectdb.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
</head>
<style>
    th, td {
        padding: 10px;
    }

    th {
        background: #606060;
        color: white;
    }

    td {
        background: #b5b5b5;
    }
    .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1;
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    width: 60%;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
}
.modal-content input,
.modal-content textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
}
.add-button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
}
</style>

    <button class="add-button" onclick="openModal()">Добавить товар</button>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span onclick="closeModal()" style="float: right; cursor: pointer;">&times;</span>
            <h2>Опубликовать новый товар</h2>
            <form method="POST" action="add_product.php">
                <!-- Здесь добавьте поля формы (название, описание, цена и т.д.) -->
                <label for="product_name">Название:</label>
                <input type="text" id="product_name" name="product_name"><br>

                <label for="product_description">Описание:</label>
                <textarea id="product_description" name="product_description"></textarea><br>

                <label for="product_price">Цена:</label>
                <input type="text" id="product_price" name="product_price"><br>

                <!-- Кнопка для отправки формы -->
                <button type="submit" name="submit">Добавить</button>
            </form>
        </div>
    </div>

    <!-- JavaScript для открытия и закрытия модального окна -->
    <script>
        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }
    </script>
<?php
// Начало сессии (если не было начато ранее)
session_start();

// Переменные для хранения текущего порядка сортировки по ID и по цене
if (!isset($_SESSION['sortOrder'])) {
    $_SESSION['sortOrder'] = "ASC";
}

if (!isset($_SESSION['sortOrderPrice'])) {
    $_SESSION['sortOrderPrice'] = "ASC";
}

if (isset($_POST['sort'])) {
    // Если кнопка "Сортировать по ID" была нажата, изменяем порядок сортировки по ID
    $_SESSION['sortOrder'] = ($_SESSION['sortOrder'] == "ASC") ? "DESC" : "ASC";
    unset($_SESSION['sortOrderPrice']); // Очищаем переменную сортировки по цене
}

if (isset($_POST['sortPrice'])) {
    // Если кнопка "Сортировать по цене" была нажата, изменяем порядок сортировки по цене
    $_SESSION['sortOrderPrice'] = ($_SESSION['sortOrderPrice'] == "ASC") ? "DESC" : "ASC";
    unset($_SESSION['sortOrder']); // Очищаем переменную сортировки по ID
}

// Проверка, была ли нажата кнопка "Удалить"
if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    // Выполняем SQL-запрос для удаления товара с указанным ID
    $delete_sql = "DELETE FROM phpshop_products WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        // Успешно удалено
        echo "Товар с ID $delete_id успешно удален.";
    } else {
        echo "Ошибка при удалении товара: " . $conn->error;
    }
}

// Переменные для нового товара
$newProductCategory = "";
$newProductName = "";
$newProductDescription = "";
$newProductPrice = "";

// Проверка, была ли нажата кнопка "Опубликовать товар"
if (isset($_POST['publish'])) {
    $newProductCategory = $_POST['new_product_category'];
    $newProductName = $_POST['new_product_name'];
    $newProductDescription = $_POST['new_product_description'];
    $newProductPrice = $_POST['new_product_price'];

    // Выполняем SQL-запрос для добавления нового товара в базу данных
    $insert_sql = "INSERT INTO phpshop_products (category, name, description, price) VALUES ('$newProductCategory', '$newProductName', '$newProductDescription', '$newProductPrice')";
    if ($conn->query($insert_sql) === TRUE) {
        // Успешно добавлено
        echo "Новый товар успешно добавлен.";
    } else {
        echo "Ошибка при добавлении нового товара: " . $conn->error;
    }
}

// SQL-запрос с учетом порядка сортировки
if (isset($_SESSION['sortOrderPrice'])) {
    $sql = "SELECT * FROM phpshop_products ORDER BY price " . $_SESSION['sortOrderPrice'];
} else {
    $sql = "SELECT * FROM phpshop_products ORDER BY id " . $_SESSION['sortOrder'];
}

$result = $conn->query($sql);
?>

<body>
    <form method="POST" action="">
        <button type="submit" name="sort">Сортировать по ID <?php echo ($_SESSION['sortOrder'] == "ASC") ? "▲" : "▼"; ?></button>
        <button type="submit" name="sortPrice">Сортировать по цене <?php echo ($_SESSION['sortOrderPrice'] == "ASC") ? "▲" : "▼"; ?></button>
    </form>

    <table>
        <tr>
            <th>id</th>
            <th>Category</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Vendor code</th>
            <th>Action</th>
        </tr>

        <?php
        if ($result) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($products as $product) {
                echo '
                <tr>
                    <td>' . $product['id'] . '</td>
                    <td>' . $product['category'] . '</td>
                    <td>' . $product['name'] . '</td>
                    <td>' . $product['description'] . '</td>
                    <td>' . $product['price'] . '</td>
                    <td>' . $product['uid'] . '</td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="delete_id" value="' . $product['id'] . '">
                            <button type="submit" name="delete">Удалить</button>
                        </form>
                    </td>
                </tr>';
            }
        } else {
            echo "Ошибка выполнения запроса: " . $conn->error;
        }
        ?>

    </table> 
</html>