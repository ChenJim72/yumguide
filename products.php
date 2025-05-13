<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
include "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - YumGuide</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-bar input[type="text"] {
            flex: 1;
            padding: 10px 20px;
            border-radius: 50px;
            border: 2px solid #ccc;
            font-size: 16px;
        }
        
        .search-bar input[type="text"]:focus {
            border-color: #4682A9;
            outline: none;
            box-shadow: 0 0 6px rgba(70, 130, 169, 0.5);
        }
            
            
        .search-bar button {
            background-color: #4682A9;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
        }
        .filters {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .filters label {
            font-weight: bold;
            margin-right: 10px;
        }
        .filters select,
        .filters input[type="range"] {
            margin-right: 15px;
        }
        
        
        .cta-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: #91C8E4;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        .cta-link:hover {
            background-color: #5a99c9;
        }
        
        .product-list-no-match {
    text-align: center;
    font-size: 18px;
    margin-top: 40px;
}


    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Products</h2>

    <form method="GET" action="products.php" class="search-bar">
        <input type="text" name="q" id="barcode" placeholder="Search by product name or brand" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="startCameraScan()">📷 Scan Barcode</button>
        <input type="file" id="upload-image" accept="image/*" style="display:none">
        <button type="button" onclick="triggerImageUpload()">🖼 Upload Barcode Image</button>
    </form>

    <div id="reader" style="width:300px; display:none; margin-top:10px;"></div>

    <!-- 篩選區塊 -->
    <form method="GET" action="products.php" class="filters">
        <input type="hidden" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        <h3>Filter Results</h3>
        <label>Food Type:</label>
        <select name="category">
            <option value="">All</option>
            <?php
            if (!empty($_GET['q'])) {
                $q = mysqli_real_escape_string($conn, $_GET['q']);
                $catres = mysqli_query($conn, "SELECT DISTINCT c.category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.product_name LIKE '%$q%' OR p.brand LIKE '%$q%' OR p.barcode LIKE '%$q%'");
            } else {
                $catres = mysqli_query($conn, "SELECT DISTINCT category_name FROM categories");
            }
            while($cat = mysqli_fetch_assoc($catres)) {
                $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category_name']) ? 'selected' : '';
                echo "<option value='{$cat['category_name']}' $selected>{$cat['category_name']}</option>";
            }
            ?>
        </select>

        <label>Minimum Rating:</label>
        <select name="min_rating">
            <option value="">Any</option>
            <option value="5" <?php if(isset($_GET['min_rating']) && $_GET['min_rating']=='5') echo 'selected'; ?>>★★★★★</option>
            <option value="4" <?php if(isset($_GET['min_rating']) && $_GET['min_rating']=='4') echo 'selected'; ?>>★★★★☆+</option>
            <option value="3" <?php if(isset($_GET['min_rating']) && $_GET['min_rating']=='3') echo 'selected'; ?>>★★★☆☆+</option>
        </select>
        <button type="submit">Apply Filter</button>
    </form>

    <?php
    $where = "1";
    if (!empty($_GET['q'])) {
        $q = mysqli_real_escape_string($conn, trim($_GET['q']));
        $where .= " AND (p.product_name LIKE '%$q%' OR p.brand LIKE '%$q%' OR p.barcode LIKE '%$q%')";
        echo "<p>Results for '<strong>" . htmlspecialchars($q) . "</strong>'</p>";
    }

    if (!empty($_GET['category'])) {
        $category = mysqli_real_escape_string($conn, $_GET['category']);
        $where .= " AND c.category_name = '$category'";
    }

    $having = "";
    if (!empty($_GET['min_rating'])) {
        $rating = (int)$_GET['min_rating'];
        $having = "HAVING avg_rating >= $rating";
    }

    $sql = "SELECT p.id, p.product_name, p.brand, c.category_name,
                   MAX(pi.front_view) AS front_image,
                   AVG(r.rating) AS avg_rating
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            LEFT JOIN reviews r ON r.product_id = p.id
            WHERE $where AND p.is_approved = 1
            GROUP BY p.id
            $having
            ORDER BY p.created_at DESC";

    $res = mysqli_query($conn, $sql);
    ?>

    <div class="product-list">
        <?php if (mysqli_num_rows($res) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $row['id']; ?>" class="product-card-link">
                        <img src="<?php echo $row['front_image'] ? htmlspecialchars($row['front_image']) : 'placeholder.png'; ?>" alt="Product Image">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['brand']); ?></p>
                        <p><?php echo htmlspecialchars($row['category_name']); ?></p>
                        <p><?php echo $row['avg_rating'] ? str_repeat("⭐", round($row['avg_rating'])) : "No rating yet"; ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
    </div>
    <p class="product-list-no-match">No matching products found. Want to help others?<br><a href="add_product.php" class="cta-link">➕ Add a new product</a></p>
        <?php endif; ?>
</div>

<script>
    let html5QrCode;
    function startCameraScan() {
        document.getElementById("reader").style.display = "block";
        if (!html5QrCode) html5QrCode = new Html5QrCode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            (decodedText) => {
                document.getElementById("barcode").value = decodedText;
                html5QrCode.stop().then(() => {
                    document.getElementById("reader").style.display = "none";
                });
            },
            (error) => console.warn(error)
        ).catch(err => alert("❌ Cannot start camera: " + err));
    }

    function triggerImageUpload() {
        document.getElementById("upload-image").click();
    }

    document.getElementById("upload-image").addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            if (!html5QrCode) html5QrCode = new Html5QrCode("reader");
            html5QrCode.scanFile(file, false)
                .then(decodedText => document.getElementById("barcode").value = decodedText)
                .catch(err => alert("Failed to decode barcode: " + err));
        }
    });
</script>
</body>
</html>
