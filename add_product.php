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


if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - YumGuide</title>
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="add_product.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

</head>
<body>

<?php include "header.php"; ?>

<div class="container">

    <h2>Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?>, Thanks for Your Contribution</h2>

    <form action="process_add_product.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
        <label for="brand">Brand Name</label>
        <input type="text" name="brand" id="brand" required>

        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" id="product_name" required>

        <label for="category">Category</label>
        <select name="category" id="category" required>
            <option value="" disabled selected>Choose a category</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['category_name']) . "</option>";
            }
            ?>
        </select>

        <label for="country">Product of</label>
        <select name="country" id="country" required>
            <option value="" disabled selected>Choose a country</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, country_name FROM countries ORDER BY country_name ASC");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['country_name']) . "</option>";
            }
            ?>
        </select>

        <label for="store">Where You Bought</label>
        <select name="store[]" id="store" multiple required style="width:100%"></select>
        <input type="text" name="input_store" id="input_store" placeholder="If not listed, enter here" style="display:none">

        <label for="price">Price (CAD)</label>
        <input type="text" name="price" id="price" pattern="\d+(\.\d{1,2})?" required>

        <label>Select the characteristics (up to 5)</label>
        <div class="tag-options" id="tag-options">

        </div>

       
        <label for="barcode">Barcode (Scan or Type)</label>
        <input type="text" name="barcode" id="barcode" placeholder="Scan or type barcode number" pattern="[0-9]{8,13}" required>
        
        <!-- 兩個按鈕：掃描 和 上傳圖片 -->
        <div class="barcode-actions">
            <button type="button" onclick="startCameraScan()">📷 Scan Barcode</button>
            <button type="button" onclick="triggerImageUpload()">🖼 Upload Barcode Image</button>
        </div>

        <!-- Camera Scanner -->
        <div id="reader" style="width: 300px; display: none; margin-top: 10px;"></div>

        <!-- 隱藏的上傳圖片 input -->
        <input type="file" id="upload-image" accept="image/*" style="display: none;">


        <label><strong>Upload Product Photos</strong></label>
        <label for="front_photo">Front View</label>
        <input type="file" name="front_photo" id="front_photo" accept="image/*" required>

        <label for="back_photo">Back View</label>
        <input type="file" name="back_photo" id="back_photo" accept="image/*" required>

        <label for="ingredients_photo">Ingredients</label>
        <input type="file" name="ingredients_photo" id="ingredients_photo" accept="image/*"required>

        <label for="nutrition_photo">Nutrition Facts</label>
        <input type="file" name="nutrition_photo" id="nutrition_photo" accept="image/*" required>

        <label><strong>Rate & Review</strong></label>
        <div class="star-rating">
            <label><input type="radio" name="rating" value="1" required><span class="star">★</span></label>
            <label><input type="radio" name="rating" value="2"><span class="star">★</span></label>
            <label><input type="radio" name="rating" value="3"><span class="star">★</span></label>
            <label><input type="radio" name="rating" value="4"><span class="star">★</span></label>
            <label><input type="radio" name="rating" value="5"><span class="star">★</span></label>
        </div>


        <label for="review">Write a review</label>
        <textarea name="review" id="review" required></textarea>

        <button type="submit">Submit</button>
    </form>
    
    <div id="thankYouModal" class="modal">
      <div class="modal-content">
        <h2>🎉 Thank You for Your Contribution!</h2>
        <p>Your product has been successfully submitted.</p>
        <p>Now others can find this product and benefit from your sharing.</p>
        <p>Redirecting you to the Search page...</p>
      </div>
    </div>
</div>

    
<script>
    let html5QrCode;
    function startCameraScan() {
        document.getElementById("reader").style.display = "block";
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader"); // ✅ 修正大小寫錯誤
        }
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            function(decodedText) {
                document.getElementById("barcode").value = decodedText;
                html5QrCode.stop().then(() => {
                    document.getElementById("reader").style.display = "none";
                });
            },
            function(err) { console.warn("Scan error", err); }
        ).catch((err) => {
            alert("❌ Cannot start camera: " + err);
        });
    }



    function triggerImageUpload() {
        document.getElementById("upload-image").click();
    }

    document.getElementById("upload-image").addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            const html5QrCode = new Html5Qrcode("reader");
            html5QrCode.scanFile(file, false)
                .then(decodedText => {
                    document.getElementById("barcode").value = decodedText;
                })
                .catch(err => {
                    alert("Failed to decode barcode: " + err);
                });
        }
    });



    
    
    
$(document).ready(function() {
   

    // characteristics
    $('#category').on('change', function () {
        var categoryId = $(this).val();

        if (categoryId) {
            $.ajax({
                url: 'get_characteristics.php',
                type: 'GET',
                data: { category_id: categoryId },
                dataType: 'json',
                success: function (data) {
                    let tagContainer = $('.tag-options');
                    tagContainer.empty(); // clean checkbox

                    data.forEach(function(tag) {
                        tagContainer.append(
                            `<label><input type="checkbox" name="tags[]" value="${tag.id}"> ${tag.characteristic_name}</label>`
                        );
                    });
                }
            });
        }
    });
});

    
    
$(document).ready(function() {
    $('#store').select2({
        placeholder: "Select stores...",
        allowClear: true,
        closeOnSelect: false,
//        width: '100%',
        ajax: {
            url: 'get_stores.php',
            dataType: 'json',
            processResults: function (data) {
                return { results: data };
            }
        }
    });

    $('#store').on('change', function() {
        let selected = $(this).val();
        if (selected && selected.includes("Other")) {
            $('#input_store').show();
        } else {
            $('#input_store').hide();
        }
    });
});



$('#barcode').on('blur', function () {
    const barcode = $(this).val();
    if (barcode.length >= 8) {
        $.ajax({
            url: 'check_barcode.php',
            type: 'GET',
            data: { barcode: barcode },
            success: function (response) {
                if (response.exists) {
                    alert("⚠️ This barcode already exists. Please double check.");
                    $('#barcode').focus();
                }
            }
        });
    }
});




document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll('.star-rating label');

    stars.forEach((label, index) => {
      label.addEventListener('click', () => {
        // clean all active
        stars.forEach(lab => lab.querySelector('.star').classList.remove('active'));

        for (let i = 0; i <= index; i++) {
          stars[i].querySelector('.star').classList.add('active');
        }

        // radio checked
        label.querySelector('input').checked = true;
      });
    });
  });
  
  
  
  function validateForm() {
  const rating = document.querySelector('input[name="rating"]:checked');
  console.log("Rating checked?", rating); // debug log
  if (!rating) {
    alert("⚠️ Please select a star rating before submitting.");
    return false;
  }

  // 圖片必填驗證
  const requiredImages = ['front_photo', 'back_photo', 'ingredients_photo', 'nutrition_photo'];
  for (let id of requiredImages) {
    if (!document.getElementById(id).value) {
      alert("⚠️ Please upload " + id.replace('_photo', '').replace('_', ' ') + " image.");
      return false;
    }
  }

  return true;
}




  
  // ✅ 成功彈出視窗並導向搜尋頁
  window.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      document.getElementById("thankYouModal").style.display = "block";
      setTimeout(function () {
        window.location.href = "search.php";  // ✅請改成你實際的搜尋頁
      }, 4000);
    }
  });
</script>
<script src="https://unpkg.com/html5-qrcode"></script>

</body>
</html>
