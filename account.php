<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';
include 'header.php';
include 'level_info.php';

// Get review count
$review_sql = "SELECT COUNT(*) AS total_reviews FROM reviews WHERE user_id = ?";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $_SESSION['user_id']);
$review_stmt->execute();
$review_result = $review_stmt->get_result()->fetch_assoc();
$reviews_written = $review_result['total_reviews'] ?? 0;

// Get product count
$product_sql = "SELECT COUNT(*) AS total_products FROM products WHERE created_by = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $_SESSION['user_id']);
$product_stmt->execute();
$product_result = $product_stmt->get_result()->fetch_assoc();
$products_added = $product_result['total_products'] ?? 0;

// Get reply count
$reply_sql = "SELECT COUNT(*) AS total_replies FROM review_replies WHERE user_id = ?";
$reply_stmt = $conn->prepare($reply_sql);
$reply_stmt->bind_param("i", $_SESSION['user_id']);
$reply_stmt->execute();
$reply_result = $reply_stmt->get_result()->fetch_assoc();
$replies_written = $reply_result['total_replies'] ?? 0;

// Calculate points and level
$total_points = 10 + ($products_added * 150) + ($reviews_written * 15) + ($replies_written * 1);
function calculateLevel($points) {
    if ($points >= 10000) return 10;
    elseif ($points >= 7000) return 9;
    elseif ($points >= 5000) return 8;
    elseif ($points >= 4000) return 7;
    elseif ($points >= 3000) return 6;
    elseif ($points >= 2000) return 5;
    elseif ($points >= 1000) return 4;
    elseif ($points >= 500) return 3;
    elseif ($points >= 100) return 2;
    else return 1;
}
$user_level = calculateLevel($total_points);

// Current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'info';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .account-container {
            display: flex;
            width: 80%;
            margin: 100px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .account-sidebar {
            color: #4682A9;
            margin-bottom: 15px;
            padding-left: 40px; 
        }
        .account-sidebar ul {
            list-style-type: none; /* 移除圓點 */
            padding-left: 0;        /* 移除縮排 */
            margin: 0;
        }

.account-sidebar li {
  margin-bottom: 10px;  /* 適當間距 */
}

        
        .account-sidebar a {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: #4682A9;
            font-weight: bold;
            padding: 10px;
            display: block;
            border-radius: 5px;
        }
        
        .account-sidebar a:hover {
            background-color: #f0f5fa;
        }
        
        .account-sidebar a.active {
    font-weight: bold;
    background-color: #dceeff;
    color: #174c75;
}
        
        .account-content {
            width: 60%;
            padding: 20px;
            margin: 0px auto;
        }
        
        .account-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
          }

        .account-info .info-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
          }

        .info-number {
            height: 80px;
            font-size: 80px;
            font-weight: bold;
            display: flex;
            align-items: center;   /* 垂直置中 */
            justify-content: center; /* 水平置中 */
            color: gray;
          }

        .info-title {
            font-size: 18px;
            margin-top: 5px;
            color: #333;
            font-weight: bold;
          }
        
        
        
        .account-info img {
            width: 70px;
            height: 70px;
            transition: transform 0.3s ease;
        }
        .account-info img:hover {
            transform: scale(1.1);
        }

        
        .account-details {
  margin-top: 30px;
  padding: 20px;
  background-color: #f9f9f9; /* 淺灰底讓資訊區分開 */
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  font-size: 16px;
  line-height: 1.6;
}

.account-details p {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
}

.account-details p strong {
  color: #333;
  min-width: 180px; /* 左側欄固定寬度對齊 */
  font-weight: 600;
}

.account-details a {
  font-size: 14px;
  color: #1e88e5;
  text-decoration: underline;
}

.account-details a:hover {
  color: #1565c0;
}

/* Modal 基本樣式 */
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
}

/* Modal 內容 */
.modal-content {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  max-width: 450px;
  width: 90%;
  margin: 10% auto;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  font-family: "Segoe UI", sans-serif;
}

/* 標題 */
.modal-content h3 {
  margin-bottom: 20px;
  font-size: 24px;
  color: #333;
}

/* 表單欄位包裹 */
.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* label+input 對齊 */
.modal-content label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #444;
}

.modal-content input[type="password"] {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
  width: 100%;
  box-sizing: border-box;
}

/* 按鈕區塊 */
.modal-content .btn-group {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}

/* 按鈕樣式 */
.modal-content button {
  padding: 8px 16px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.modal-content button[type="submit"] {
  background-color: #4682A9;
  color: white;
}

.modal-content button[type="button"] {
  background-color: #eee;
  color: #333;
}

input:focus {
  outline: none;               /* 移除預設藍色外框 */
  box-shadow: 0 0 4px #4682A9; /* 選擇性：加上柔和陰影 */
}

/*user's review*/
.review-card {
  display: flex;
  background: #fafafa;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.review-card img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 10px;
  margin-right: 15px;
}

.review-card-link {
    text-decoration: none; /* 去除預設底線 */
    color: inherit;        /* 保持文字顏色 */
    display: block;
}
.review-card-link:hover .review-card {
    box-shadow: 0 4px 10px rgba(0,0,0,0.2); /* 滑鼠移過去有 hover 效果 */
    transform: scale(1.01);
    transition: all 0.3s ease;
}

section.contribution-history + section.contribution-history {
  border-top: 1px solid #ddd;
  padding-top: 20px;
  margin-top: 40px;
}


.contribution-card {
  display: flex;
  background: #fafafa;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

 .contribution-card img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 10px;
  margin-right: 15px;
}

.contribution-card-link {
    text-decoration: none; /* 去除預設底線 */
    color: inherit;        /* 保持文字顏色 */
    display: block;
}
.contribution-card-link:hover .contribution-card {
    box-shadow: 0 4px 10px rgba(0,0,0,0.2); /* 滑鼠移過去有 hover 效果 */
    transform: scale(1.01);
    transition: all 0.3s ease;
}




    </style>
</head>
<body>

<div class="account-container">
    <aside class="account-sidebar">
        <h2>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <ul>
            <li><a href="?tab=info" class="<?php echo $tab === 'info' ? 'active' : ''; ?>">Information</a></li>
            <li><a href="?tab=history" class="<?php echo $tab === 'history' ? 'active' : ''; ?>">Contribution History</a></li>
        </ul>
    </aside>

    <main class="account-content">
        
    <!--acoount information-->
        <?php if ($tab === 'info'): ?>
            <div class="account-info">
                <div class="info-box">
                    <div class="info-number">
                        <img src="<?php echo $levelInfo[$user_level]['image']; ?>" alt="Level <?php echo $user_level; ?>" title="<?php echo $levelInfo[$user_level]['desc']; ?>">
                    </div>
                        <div class="info-title">Level <?php echo $user_level; ?></div>
                  </div>

                  <div class="info-box">
                    <div class="info-number"><?php echo $reviews_written; ?></div>
                    <div class="info-title">Reviews written</div>
                  </div>

                  <div class="info-box">
                    <div class="info-number"><?php echo $products_added; ?></div>
                    <div class="info-title">Products added</div>
                  </div>

                  <div class="info-box">
                    <div class="info-number"><?php echo $replies_written; ?></div>
                    <div class="info-title">Replies written</div>
                  </div>
           </div>
                <div class="account-details">
                    <p><strong>NAME:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p><strong>EMAIL:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>PASSWORD:</strong> ******** <a href="javascript:void(0);" onclick="document.getElementById('changePasswordModal').style.display='block'">Change password?</a></p>
                    <p><strong>REGISTRATION TIME:</strong> <?php echo htmlspecialchars($_SESSION['created_at']); ?></p>
                </div>
            <!-- Trigger Button -->
            

            <!-- Change Password Modal -->
                <div id="changePasswordModal" class="modal">
                  <div class="modal-content">
                    <h3>Change Password</h3>
                    <form id="changePasswordForm">
                      <label>Current Password:</label>
                      <input type="password" name="current_password" required>

                      <label>New Password:</label>
                      <input type="password" name="new_password" required>

                      <label>Confirm New Password:</label>
                      <input type="password" name="confirm_password" required>

                      <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" onclick="document.getElementById('changePasswordModal').style.display='none'">Cancel</button>
                      </div>
                    </form>
                  </div>
                </div>


    <!--TAB  Contribution history-->
        <?php elseif ($tab === 'history'): ?>
                <?php
                // all user's reviews
                $review_sql = "SELECT r.*, p.product_name, p.brand, p.id AS product_id,
                                      (SELECT front_view FROM product_images WHERE product_id = p.id LIMIT 1) AS image                                  
                               FROM reviews r
                               JOIN products p ON r.product_id = p.id
                               WHERE r.user_id = ?
                               ORDER BY r.created_at DESC";
                $review_stmt = $conn->prepare($review_sql);
                $review_stmt->bind_param("i", $_SESSION['user_id']);
                $review_stmt->execute();
                $review_result = $review_stmt->get_result();
                ?>
            <!--review section-->
                <section class="contribution-history">
                    <h2>Review Contribution</h2>

                 <?php while ($r = $review_result->fetch_assoc()) { ?>
                    <a href="product_details.php?id=<?php echo $r['product_id']; ?>" class="review-card-link">
                        <div class="review-card">
                            <img src="<?php echo htmlspecialchars($r['image']); ?>" alt="Product Image">
                            <div>
                                <p><strong><?php echo htmlspecialchars($r['brand'] . ' ' . $r['product_name']); ?></strong></p>
                                <p>
                                    <?php echo str_repeat('⭐', round($r['rating'])); ?>
                                    (<?php echo $r['rating'];?> stars)
                                </p>
                                <p><?php echo htmlspecialchars(mb_strimwidth($r['review_text'], 0, 120, '...')); ?></p>
                            </div>
                        </div>
                    </a>
                 <?php } ?>
                </section>
           
            
            
            <!--product added section-->
                <?php
                    $product_list_sql = "SELECT p.*, 
                                (SELECT front_view FROM product_images WHERE product_id = p.id LIMIT 1) AS image
                         FROM products p
                         WHERE p.created_by = ?
                         ORDER BY p.created_at DESC";

                    $product_list_stmt = $conn->prepare($product_list_sql);
                    $product_list_stmt->bind_param("i", $_SESSION['user_id']);
                    $product_list_stmt->execute();
                    $product_list_result = $product_list_stmt->get_result();

                ?>
            
            <section class="contribution-history">
                <h2>New Product Contribution</h2>

                <?php while($p = $product_list_result->fetch_assoc()) { ?>
                  <a href="product_details.php?id=<?php echo $p['id']; ?>" class="contribution-card-link">
                    <div class="contribution-card">
                      <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="Product Image" class="contribution-image">
                      <div>
                          <p><strong><?php echo htmlspecialchars($p['brand']); ?> <?php echo htmlspecialchars($p['product_name']); ?></strong></p>
                          <p>Created at: <?php echo date('Y-m-d', strtotime($p['created_at'])); ?></p>
                       
                      </div>
                    </div>
                  </a>
                <?php } ?>
            </section>
            
            
         <?php endif; ?> 
    </main>
</div>

    
    <script>
       document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('change_password.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(text => {
    console.log('[Raw Response]', text);
    let data;
    try {
      data = JSON.parse(text);
      if (data.success) {
        alert('✅ Password updated successfully!');
        document.getElementById('changePasswordForm').reset();
        document.getElementById('changePasswordModal').style.display = 'none';
      } else {
        alert('❌ ' + data.message);
      }
    } catch (err) {
      alert('❌ JSON Parse error:\n' + text);
    }
  })
  .catch(err => {
    console.error(err);
    alert('❌ System error');
  });
});

    </script>

</body>
</html>
