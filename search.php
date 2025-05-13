<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Products - YumGuide</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }
        .search-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .search-box h2 {
            color: #4682A9;
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
       .search-form input[type="text"] {
            width: 30%;
            padding: 10px;
            border-radius: 30px;
            border: 1px solid #ccc;
            font-size: 16px;
            transition: box-shadow 0.2s ease;
        }
        
        .search-form input[type="text"]:focus {
            border-color: #4682A9;
            outline: none;
            box-shadow: 0 0 6px rgba(70, 130, 169, 0.5);
}

        .search-form button {
            background: #4682A9;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
        }
        .barcode-actions {
            margin-top: 15px;
        }
        .barcode-actions button {
            background: #F6F4EB;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            margin: 5px;
            font-size: 15px;
            cursor: pointer;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .product-card img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-card h3 {
            margin-top: 10px;
            font-size: 18px;
            color: #333;
        }
        .product-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .product-card a {
            display: inline-block;
            margin-top: 10px;
            color: #4682A9;
            text-decoration: none;
            font-size: 14px;
        }
        .product-card a:hover {
            text-decoration: underline;
        }
        .no-result {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 16px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="search-box">
        <h2><i class="fa fa-search"></i> Search Products</h2>
        <form class="search-form" method="GET" action="products.php">
            <input type="text" name="q" id="barcode" placeholder="Search by product name or brand" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <button type="submit"><i class="fa fa-search"></i> Search</button>
        </form>
        <div class="barcode-actions">
            <button type="button" onclick="startCameraScan()"><i class="fa fa-camera"></i> Scan Barcode</button>
            <button type="button" onclick="triggerImageUpload()"><i class="fa fa-image"></i> Upload Barcode Image</button>
        </div>
        <div id="reader" style="width:300px; display:none; margin: 20px auto;"></div>
        <input type="file" id="upload-image" accept="image/*" style="display:none">
    </div>
</div>


<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function startCameraScan() {
        document.getElementById("reader").style.display = "block";
        const html5QrCode = new Html5Qrcode("reader");

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            (decodedText, decodedResult) => {
                document.getElementById("barcode").value = decodedText;
                html5QrCode.stop().then(() => {
                    document.getElementById("reader").style.display = "none";
                });
            },
            (error) => {
                console.warn("Scan error", error);
            }
        ).catch((err) => {
            alert("❌ Cannot start camera: " + err);
        });
    }

    function triggerImageUpload() {
        document.getElementById("upload-image").click();
    }

    document.getElementById("upload-image").addEventListener("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const html5QrCode = new Html5Qrcode("reader");
            html5QrCode.scanFile(file, false)
                .then(decodedText => {
                    document.getElementById("barcode").value = decodedText;
                })
                .catch(err => {
                    alert("❌ Failed to decode barcode: " + err);
                });
        }
    });
</script>


</body>
</html>
