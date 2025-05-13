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

//user's review 
$product_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$isAdmin = ($user_id == 5); // ✅  id=5 is admin

$filter = $isAdmin ? "" : " AND p.is_approved = 1"; 


$my_review_stmt = $conn->prepare("SELECT * FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
$my_review_stmt->bind_param("ii", $product_id, $user_id);
$my_review_stmt->execute();
$my_review_result = $my_review_stmt->get_result();
$my_review = $my_review_result->fetch_assoc();


// products details
$sql = "SELECT p.*, c.category_name, cn.country_name, u.username,
        (SELECT front_view FROM product_images WHERE product_id = p.id LIMIT 1) AS front_image,
        (SELECT back_view FROM product_images WHERE product_id = p.id LIMIT 1) AS back_image,
        (SELECT ingredients FROM product_images WHERE product_id = p.id LIMIT 1) AS ingredients_image,
        (SELECT nutrition_facts FROM product_images WHERE product_id = p.id LIMIT 1) AS nutrition_image,
        (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) AS review_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN countries cn ON p.country_id = cn.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = $product_id $filter";


$product = mysqli_fetch_assoc(mysqli_query($conn, $sql));


if (!$product) {
    if (!$isAdmin) {
        error_log("Unauthorized access attempt to product ID $product_id by user ID $user_id");
    }
    header("Location: index.php");
    exit();
}


//  Tags
$tag_sql = "SELECT ch.characteristic_name FROM product_characteristics pc 
            JOIN characteristics ch ON pc.characteristic_id = ch.id 
            WHERE pc.product_id = $product_id";
$tags_res = mysqli_query($conn, $tag_sql);

// 取得所有評論
$review_sql = "SELECT r.*, u.username, 
                (SELECT COUNT(*) FROM review_likes WHERE review_id = r.id) AS likes,
                (SELECT COUNT(*) FROM review_replies WHERE review_id = r.id) AS replies,
                (SELECT COUNT(*) FROM review_likes WHERE review_id = r.id AND user_id = " . intval($user_id) . ") AS user_liked
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = $product_id
                ORDER BY r.created_at DESC";

$reviews = mysqli_query($conn, $review_sql);

// place
$store_sql = "SELECT s.store_name FROM product_stores ps 
              JOIN stores s ON ps.store_id = s.id 
              WHERE ps.product_id = $product_id";
$stores = mysqli_query($conn, $store_sql);

// reply
$reply_sql = "SELECT rr.*, u.username FROM review_replies rr
              JOIN users u ON rr.user_id = u.id
              WHERE rr.review_id = ?
              ORDER BY rr.created_at ASC";
$reply_stmt = $conn->prepare($reply_sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - YumGuide</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .product-detail { display: flex; flex-wrap: wrap; gap: 20px; }
        .main-img { max-width: 300px; border-radius: 10px; }
        .thumbs img { width: 70px; height: 70px; object-fit: cover; margin: 5px; cursor: pointer; border-radius: 5px; }
        .info { flex: 1; }
        .tag { display: inline-block; background: #eee; padding: 5px 10px; margin: 3px; border-radius: 15px; }
        .rating { font-size: 1.5rem; color: #f5b301; }
        .reviews { margin-top: 30px; }
        .review-card { font-size: 20px; border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 10px; background: #fff; }
        .review-header { font-size: 22px; font-weight: bold; display: flex; justify-content: space-between;}
        .review-actions span { margin-right: 10px; cursor: pointer; color: #555; }
        .filter-buttons button { margin: 5px; padding: 5px 10px; border: none; background: #ddd; border-radius: 20px; cursor: pointer; }
        .purchase-info { margin-top: 15px; color: #333; font-size: 0.95rem; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 12px; }
        
        
     /* ratings */
        .star-rating {
          display: flex;
          flex-direction: row;
          gap: 5px;
          font-size: 32px;
          justify-content: flex-start;
        }

        .star-rating input {
          display: none;
        }

        .star-rating .star {
          color: #ccc;
          cursor: pointer;
          transition: color 0.2s;
        }

        .star-rating .star.active {
          color: #f5b301;
        }
        
        
    /* like comment area */
        .icon {
          width: 20px;
          height: 20px;
          color: gray;
          
          vertical-align: middle;
          cursor: pointer;
          transition: filter 0.3s ease;
        }
        
        .delete-icon{
          fill-opacity: 0.7;
          width:14px;
          height: 14px;
        }
        

        
        .like-icon path {
          fill-opacity:0.3;
        /* fill: none;*/
        /*stroke: #91C8E4;
         stroke-width: 1.5;*/
        }
        
        .like-icon.liked path {
          fill: #91C8E4;
          fill-opacity:1;
        }

       

    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="product-detail">
        <div class="left">
            <img src="<?php echo $product['front_image']; ?>" alt="Main image" class="main-img" id="mainImage">
            <div class="thumbs">
                <img src="<?php echo $product['front_image']; ?>" onclick="changeImage(this)">
                <img src="<?php echo $product['back_image']; ?>" onclick="changeImage(this)">
                <img src="<?php echo $product['ingredients_image']; ?>" onclick="changeImage(this)">
                <img src="<?php echo $product['nutrition_image']; ?>" onclick="changeImage(this)">
            </div>
        </div>
        <div class="info">
            <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p>Brand: <?php echo htmlspecialchars($product['brand']); ?></p>
            <p>Country: <?php echo htmlspecialchars($product['country_name']); ?></p>
            <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
            <div>
                <?php while ($tag = mysqli_fetch_assoc($tags_res)) {
                    echo '<span class="tag">' . htmlspecialchars($tag['characteristic_name']) . '</span>'; } ?>
            </div>
            <div class="rating">
                <?php echo round($product['avg_rating'], 1); ?> ⭐ (<?php echo $product['review_count']; ?> ratings)
            </div>
            <div class="purchase-info">
                <strong>Reported Purchase Locations:</strong>
                <?php
                if (mysqli_num_rows($stores) > 0) {
                    while ($s = mysqli_fetch_assoc($stores)) {
                        echo '<span class="tag">' . htmlspecialchars($s['store_name']) . '</span>';
                    }
                    echo '<p style="font-size: 0.85rem; color: #777;">(Provided by users; availability may vary.)</p>';
                } else {
                    echo '<p>No store information provided yet.</p>';
                }
                ?>
            </div>
            
            
            
            <!-- suggetion modal -->
            <button onclick="document.getElementById('suggestModal').style.display='block'">💡 Suggest Edit</button>
                <div id="suggestModal" class="modal">
              <div class="modal-content">
                <h3>Suggest a Product Edit</h3>
                <form id="suggestForm">
                    <textarea name="suggestion" rows="5" style="width:100%" placeholder="Please describe your suggestion..."></textarea>
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <br>
                    <button type="submit">Send Suggestion</button>
                    <button type="button" onclick="document.getElementById('suggestModal').style.display='none'">Cancel</button>
                </form>
              </div>
            </div>
            
            
            
            
        </div>
    </div>

    <div class="reviews">
        <h3>Community Reviews 
            
            
    <!--write/edit a riview area-->
        <?php if ($my_review): ?>
            <button onclick="openEditReview()">Edit Your Review</button>
        <?php else: ?>
            <button onclick="document.getElementById('reviewModal').style.display='block'">
                Write a Review
            </button>
        <?php endif; ?>
            
            
        </h3>
        
        <div id="reviewModal" class="modal">
            <div class="modal-content">
              <h3>Write a Review</h3>
              <form id="reviewForm">
                <label>Rating:</label>
                <div class="star-rating">
                    <label><input type="radio" name="rating" value="1"><span class="star">★</span></label>
                    <label><input type="radio" name="rating" value="2"><span class="star">★</span></label>
                    <label><input type="radio" name="rating" value="3"><span class="star">★</span></label>
                    <label><input type="radio" name="rating" value="4"><span class="star">★</span></label>
                    <label><input type="radio" name="rating" value="5"><span class="star">★</span></label>
                </div><br>
                <label>Your Comment:</label><br>
                <textarea name="review_text" rows="4" style="width:100%;" required></textarea>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <br>
                <button type="submit">Submit Review</button>
                <button type="button" onclick="document.getElementById('reviewModal').style.display='none'">Cancel</button>
              </form>
            </div>
        </div>
    <!--^^^write a riview area^^^-->
    
        <div id="editReviewModal" class="modal">
            <div class="modal-content">
              <h3>Edit Your Review</h3>
              <form id="editReviewForm">
                <label>Rating:</label>
                <div class="star-rating">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        $checked = ($my_review['rating'] == $i) ? 'checked' : '';
                        $active = ($my_review['rating'] >= $i) ? 'active' : '';
                        echo "<label><input type='radio' name='rating' value='$i' $checked><span class='star $active'>★</span></label>";
                    }
                    ?>
                    
                    </div><br>
                <br>
                <label>Your Comment:</label><br>
                <textarea name="review_text" rows="4" style="width:100%;" required>
                    <?php echo htmlspecialchars($my_review['review_text']); ?>
                </textarea>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="review_id" value="<?php echo $my_review['id']; ?>">
                <br>
                <button type="submit">Submit Review</button>
                <button type="button" onclick="document.getElementById('editReviewModal').style.display='none'">Cancel</button>
              </form>
            </div>
        </div>
    </div>
        
            

        
        <div class="filter-buttons">
            <button onclick="filterReviews('all')">All</button>
            <button onclick="filterReviews(5)">5⭐</button>
            <button onclick="filterReviews(4)">4⭐</button>
            <button onclick="filterReviews(3)">3⭐</button>
            <button onclick="filterReviews(2)">2⭐</button>
            <button onclick="filterReviews(1)">1⭐</button>
        </div>
        <div id="reviewContainer">
            <?php while ($r = mysqli_fetch_assoc($reviews)) { ?>
                <div class="review-card" data-rating="<?php echo $r['rating']; ?>">
                    <div class="review-header">
                        <div><?php echo htmlspecialchars($r['username']); ?> - <?php echo str_repeat('⭐', $r['rating']); ?></div>
                        <div><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></div>
                    </div>
                    <p><?php echo htmlspecialchars($r['review_text']); ?></p>
                    
                    
                    <div class="review-actions">
                        
                        
                        <!--button like-->
                        <span class="like-btn" onclick="likeReview(<?= $r['id'] ?>, this)">
                            <svg class="icon like-icon <?= $r['user_liked'] ? 'liked' : '' ?>" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path d="M5.62436 4.4241C3.96537 5.18243 2.75 6.98614 2.75 9.13701C2.75 11.3344 3.64922 13.0281 4.93829 14.4797C6.00072 15.676 7.28684 16.6675 8.54113 17.6345C8.83904 17.8642 9.13515 18.0925 9.42605 18.3218C9.95208 18.7365 10.4213 19.1004 10.8736 19.3647C11.3261 19.6292 11.6904 19.7499 12 19.7499C12.3096 19.7499 12.6739 19.6292 13.1264 19.3647C13.5787 19.1004 14.0479 18.7365 14.574 18.3218C14.8649 18.0925 15.161 17.8642 15.4589 17.6345C16.7132 16.6675 17.9993 15.676 19.0617 14.4797C20.3508 13.0281 21.25 11.3344 21.25 9.13701C21.25 6.98614 20.0346 5.18243 18.3756 4.4241C16.7639 3.68739 14.5983 3.88249 12.5404 6.02065C12.399 6.16754 12.2039 6.25054 12 6.25054C11.7961 6.25054 11.601 6.16754 11.4596 6.02065C9.40166 3.88249 7.23607 3.68739 5.62436 4.4241ZM12 4.45873C9.68795 2.39015 7.09896 2.10078 5.00076 3.05987C2.78471 4.07283 1.25 6.42494 1.25 9.13701C1.25 11.8025 2.3605 13.836 3.81672 15.4757C4.98287 16.7888 6.41022 17.8879 7.67083 18.8585C7.95659 19.0785 8.23378 19.292 8.49742 19.4998C9.00965 19.9036 9.55954 20.3342 10.1168 20.6598C10.6739 20.9853 11.3096 21.2499 12 21.2499C12.6904 21.2499 13.3261 20.9853 13.8832 20.6598C14.4405 20.3342 14.9903 19.9036 15.5026 19.4998C15.7662 19.292 16.0434 19.0785 16.3292 18.8585C17.5898 17.8879 19.0171 16.7888 20.1833 15.4757C21.6395 13.836 22.75 11.8025 22.75 9.13701C22.75 6.42494 21.2153 4.07283 18.9992 3.05987C16.901 2.10078 14.3121 2.39015 12 4.45873Z"/>
                              <!--<path d="M12 22.59l-9.2-9.12C.43 11.09.43 7.21 2.8 4.83a6.03 6.03 0 0 1 4.29-1.79c1.62 0 3.14.63 4.29 1.79l.62.62.62-.62a6.014 6.014 0 0 1 4.29-1.79c1.62 0 3.14.63 4.29 1.79 2.37 2.38 2.37 6.26 0 8.64L12 22.59zM7.09 4c-1.37 0-2.65.54-3.61 1.51-2 2.01-2 5.28 0 7.29L12 21.25l8.53-8.45c2-2.01 2-5.28 0-7.29A5.079 5.079 0 0 0 16.92 4c-1.37 0-2.65.54-3.61 1.51l-1.3 1.3-1.3-1.3C9.75 4.54 8.46 4.01 7.1 4z"/>-->
                            </svg> <span class="like-count"><?= $r['likes'] ?></span>
                        </span>

                        <!--button reply-->
                        <span class="reply-btn" onclick="toggleReplyForm(<?= $r['id'] ?>)">
                          <svg class="icon reply-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                               stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 12H8.01M12 12H12.01M16 12H16.01M21.0039 12C21.0039 16.9706 16.9745 21 12.0039 21C9.9675 21 3.00463 21 3.00463 21C3.00463 21 4.56382 17.2561 3.93982 16.0008C3.34076 14.7956 3.00391 13.4372 3.00391 12C3.00391 7.02944 7.03334 3 12.0039 3C16.9745 3 21.0039 7.02944 21.0039 12Z"/>
                          </svg><span class="reply-count"><?= $r['replies'] ?></span>
                        </span>
                        
                        
                        <!--button delete-->
                        <?php if ($r['user_id'] == $_SESSION['user_id']): ?>
                        <span class="delete-btn" onclick="deleteReview(<?= $r['id'] ?>)">
                          <svg class="icon delete-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"  >
                            <path d="M15.5 3H11V1.5A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5V3H0.5a.5.5 0 0 0 0 1H2v10.5A1.5 1.5 0 0 0 3.5 16h9A1.5 1.5 0 0 0 14 14.5V4h1.5a.5.5 0 0 0 0-1zM6 1.5A.5.5 0 0 1 6.5 1h3a.5.5 0 0 1 .5.5V3H6V1.5zM13 14.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V4h10v10.5zM5.5 5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5z"/>
                          </svg>
                        </span>
                        <?php endif; ?>

                                                
                        
                        <div class="reply-form" id="reply-form-<?= $r['id'] ?>" style="display:none; margin-top:10px;">
                            <textarea id="reply-text-<?= $r['id'] ?>" rows="2" style="width:100%;" placeholder="Write a reply..."></textarea>
                            <button onclick="submitReply(<?= $r['id'] ?>)">Submit</button>
                         </div>
                       
                        <div class="reply-list" id="reply-list-<?= $r['id'] ?>"  style="margin-left:20px; margin-top:10px;">
                            <?php
                                $reply_stmt->bind_param("i", $r['id']);
                                $reply_stmt->execute();
                                $reply_result = $reply_stmt->get_result();
                                while ($reply = $reply_result->fetch_assoc()) {
                                    echo '<div class="reply-item" data-id="' . $reply['id'] . '">';
                                    echo '<strong>' . htmlspecialchars($reply['username']) . '</strong>: '
                                       . htmlspecialchars($reply['reply_text']);
                                    echo ' <span style="color:#999; font-size:0.85em;">' . $reply['created_at'] . '</span>';

                                    // ✅ 這段判斷必須在 while 裡面
                                    if (isset($_SESSION['user_id']) && $reply['user_id'] == $_SESSION['user_id']) {
                                        echo '<span class="delete-btn" onclick="deleteReply(' . $reply['id'] . ')" style="cursor:pointer; margin-left:10px;">';
                                        echo '<svg class="icon delete-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"  >
                                        <path d="M15.5 3H11V1.5A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5V3H0.5a.5.5 0 0 0 0 1H2v10.5A1.5 1.5 0 0 0 3.5 16h9A1.5 1.5 0 0 0 14 14.5V4h1.5a.5.5 0 0 0 0-1zM6 1.5A.5.5 0 0 1 6.5 1h3a.5.5 0 0 1 .5.5V3H6V1.5zM13 14.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V4h10v10.5zM5.5 5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0v-8a.5.5 0 0 1 .5-.5z"/>
                                      </svg>';
                                        echo '</span>';
                                    }

                                    echo '</div>';
                                }

                            ?>
                                              
                         </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>



<script>
    
//圖片預覽功能
function changeImage(img) {
    document.getElementById("mainImage").src = img.src;
}
function filterReviews(rating) {
    const reviews = document.querySelectorAll('.review-card');
    reviews.forEach(r => {
        if (rating === 'all' || parseInt(r.dataset.rating) === rating) {
            r.style.display = 'block';
        } else {
            r.style.display = 'none';
        }
    });
}

//send suggetion
document.getElementById('suggestForm').addEventListener('submit', function(e) {
  e.preventDefault(); 

  const formData = new FormData(this);

  fetch('send_suggestion.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("✅ Thank you for your suggestion!");
      document.getElementById('suggestModal').style.display = 'none';
      document.getElementById('suggestForm').reset();
    } else {
      alert("❌ " + data.message);
    }
  })
  .catch(err => {
    alert("❌ Error submitting suggestion.");
    console.error(err);
  });
});



//review
function likeReview(id, el) {
  fetch('like_review.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'review_id=' + id
  })
  .then(res => res.json())
  .then(data => {
    const svgIcon = el.querySelector('svg');
    if (data.status === 'liked') {
      svgIcon.classList.add('liked');
    } else {
      svgIcon.classList.remove('liked');
    }
    el.querySelector('.like-count').innerText = data.likes;
  });
}



//star rating
document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll('.star-rating label');

    stars.forEach((label, index) => {
      label.addEventListener('click', () => {
        //  clear active
        stars.forEach(lab => lab.querySelector('.star').classList.remove('active'));

        // active 
        for (let i = 0; i <= index; i++) {
          stars[i].querySelector('.star').classList.add('active');
        }

        // radio check
        label.querySelector('input').checked = true;
      });
    });
  });
  
// Submit Review by AJAX
  document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault(); 

    const formData = new FormData(this);

    fetch('submit_review.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
          
//        alert("✅ Review submitted successfully!");
        // close modal
        document.getElementById('reviewModal').style.display = 'none';
        // optional
        location.reload(); 
      } else {
        alert("❌ Failed: " + data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert("❌ Error submitting review.");
    });
  });
  
  
  //submot reply
  function toggleReplyForm(reviewId) {
  const form = document.getElementById('reply-form-' + reviewId);
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function submitReply(reviewId) {
  const text = document.getElementById('reply-text-' + reviewId).value;
  if (text.trim() === '') return;

  fetch('submit_reply.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'review_id=' + reviewId + '&reply_text=' + encodeURIComponent(text)
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      // ✅ clean
      document.getElementById('reply-text-' + reviewId).value = '';

      // ✅ AJAX reload reply list
      reloadReplyList(reviewId);
    } else {
      alert("Failed to reply.");
    }
  });
}

// ✅  reloadReplyList()
function reloadReplyList(reviewId) {
  const container = document.getElementById('reply-list-' + reviewId);
  fetch('load_reply_list.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'review_id=' + reviewId
  })
  .then(res => res.text())
  .then(html => {
    container.innerHTML = html;
  });
}



//Review Edit
function openEditReview() {
  document.getElementById('editReviewModal').style.display = 'block';
}

        //AJAX - Edit Review 
document.getElementById('editReviewForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('edit_review.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('editReviewModal').style.display = 'none';
      location.reload();
    } else {
      alert('Update failed');
    }
  });
});


// Delete Review
function deleteReview(id) {
  if (!confirm("Are you sure you want to delete this review?")) return;
  fetch('delete_review.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'review_id=' + id
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.querySelector('.review-card[data-id="'+id+'"]').remove();
    } else {
      alert("❌ Delete failed: " + data.message);
    }
  });
}


// Delete Reply
function deleteReply(id) {
  if (!confirm("Are you sure you want to delete this reply?")) return;
  fetch('delete_reply.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'reply_id=' + id
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.querySelector('.reply-item[data-id="'+id+'"]').remove();
    } else {
      alert("❌ Delete failed: " + data.message);
    }
  });
}


</script>
</body>
</html>
