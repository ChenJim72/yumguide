<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 5) {
    header('Location: index.php');
    exit();
}
include 'config.php';
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Pending Product Review</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            width: 80%;
            margin: 100px auto;
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
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .contribution-card-link:hover .contribution-card {
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        .approve-btn {
            margin-top: 10px;
            padding: 6px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .approve-btn:hover {
            background-color: #3c9742;
        }
        h2 {
            color: #4682A9;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h2>🛠 Pending Product Approvals</h2>

    <?php
    $sql = "SELECT p.*, c.category_name, u.username,
                   (SELECT front_view FROM product_images WHERE product_id = p.id LIMIT 1) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.is_approved = 0
            ORDER BY p.created_at DESC";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) > 0):
        while ($row = mysqli_fetch_assoc($res)):
    ?>
        <div class="contribution-card-link">
            <div class="contribution-card">
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Product Image">
                <div>
                    <p><strong><?= htmlspecialchars($row['brand']) ?> <?= htmlspecialchars($row['product_name']) ?></strong></p>
                    <p>Category: <?= htmlspecialchars($row['category_name']) ?></p>
                    <p>Submitted By: <?= htmlspecialchars($row['username']) ?></p>
                    <p>Created At: <?= htmlspecialchars($row['created_at']) ?></p>

                    <a href="product_details.php?id=<?= $row['id'] ?>" target="_blank">🔍 View Details</a>

                    <form action="approve_product.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="approve-btn">✅ Approve</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; else: ?>
        <p>No products pending approval.</p>
    <?php endif; ?>
        
        <section class="contribution-history">
            <h2>💡 Product Edit Suggestions</h2>
            <?php
            $sug_sql = "SELECT s.*, p.product_name, p.brand,
                           (SELECT front_view FROM product_images WHERE product_id = p.id LIMIT 1) AS image
                    FROM suggestions s
                    LEFT JOIN products p ON s.product_id = p.id
                    WHERE s.status = 'pending'
                    ORDER BY s.created_at DESC";
            $sug_res = mysqli_query($conn, $sug_sql);

            if (mysqli_num_rows($sug_res) > 0):
                while($sug = mysqli_fetch_assoc($sug_res)): ?>
                <div class="contribution-card-link">
                    <div class="contribution-card">
                        <img src="<?= htmlspecialchars($sug['image']) ?>" alt="Product Image">

                        <div>
                            <p><strong><?= htmlspecialchars($sug['brand'] . ' ' . $sug['product_name']) ?></strong></p>
                            <p><strong>User ID:</strong> <?= $sug['user_id'] ?></p>
                            <p><strong>Suggestion:</strong> <?= nl2br(htmlspecialchars($sug['suggestion_text'])) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($sug['status']) ?> | 
                               <strong>Time:</strong> <?= $sug['created_at'] ?></p>

                            <form action="update_suggestion_status.php" method="POST" style="margin-top:10px;">
                                <input type="hidden" name="suggestion_id" value="<?= $sug['id'] ?>">
                                <button name="action" value="approve" class="approve-btn">✅ Approve</button>
                                <button name="action" value="reject" class="approve-btn" style="background-color:#e74c3c;">❌ Reject</button>
                            </form>
                          
                        </div>
                    </div>
                </div>
            <?php endwhile;else: ?>
                <p>No suggestions yet.</p>
            <?php endif; ?>
        </section>
</div>
    
    


</body>
</html>
