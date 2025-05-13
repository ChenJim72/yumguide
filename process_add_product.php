<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "config.php"; 


if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}


$user_id = $_SESSION["user_id"];
$brand = $_POST["brand"] ?? '';
$product_name = $_POST["product_name"] ?? '';
$category_id = $_POST["category"] ?? null;
$country_id = $_POST["country"] ?? null;
$price = $_POST["price"] ?? 0.0;


if (empty($_POST["barcode"])) {
    header("Location: add_product.php?error=barcode_required");
    exit();
}
$barcode = $_POST["barcode"];

// check barcode 
$check_stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
$check_stmt->bind_param("s", $barcode);
$check_stmt->execute();
$check_stmt->store_result();
if ($check_stmt->num_rows > 0) {
    header("Location: add_product.php?error=barcode_exists");
    exit();
}

// check rating
$rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : null;
if ($rating === null || $rating < 1 || $rating > 5) {
    header("Location: add_product.php?error=invalid_rating");
    exit();
}

$review_text = $_POST["review"] ?? '';

// upload required
$required_images = ["front_photo", "back_photo", "ingredients_photo", "nutrition_photo"];
foreach ($required_images as $img_field) {
    if (!isset($_FILES[$img_field]) || $_FILES[$img_field]["error"] !== UPLOAD_ERR_OK) {
        header("Location: add_product.php?error=image_missing&field=$img_field");
        exit();
    }
}

// products
$stmt = $conn->prepare("INSERT INTO products (brand, product_name, category_id, country_id, price, barcode, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("ssiidsi", $brand, $product_name, $category_id, $country_id, $price, $barcode, $user_id);
$stmt->execute();
$product_id = $stmt->insert_id;

// product_stores  many to many
if (!empty($_POST["store"])) {
    $store_ids = $_POST["store"];
    foreach ($store_ids as $store_id) {
        $stmt_store = $conn->prepare("INSERT INTO product_stores (product_id, store_id) VALUES (?, ?)");
        $stmt_store->bind_param("ii", $product_id, $store_id);
        $stmt_store->execute();
    }
}

// characteristic tag
if (!empty($_POST["tags"])) {
    $tags = $_POST["tags"];
    foreach ($tags as $tag_id) {
        $stmt_tag = $conn->prepare("INSERT INTO product_characteristics (product_id, characteristic_id) VALUES (?, ?)");
        $stmt_tag->bind_param("ii", $product_id, $tag_id);
        $stmt_tag->execute();
    }
}

// uploads pics
$upload_dir = "uploads/";
$front_path = $upload_dir . "front_" . $product_id . "." . pathinfo($_FILES["front_photo"]["name"], PATHINFO_EXTENSION);
$back_path = $upload_dir . "back_" . $product_id . "." . pathinfo($_FILES["back_photo"]["name"], PATHINFO_EXTENSION);
$ingredient_path = $upload_dir . "ingredients_" . $product_id . "." . pathinfo($_FILES["ingredients_photo"]["name"], PATHINFO_EXTENSION);
$nutrition_path = $upload_dir . "nutrition_" . $product_id . "." . pathinfo($_FILES["nutrition_photo"]["name"], PATHINFO_EXTENSION);

move_uploaded_file($_FILES["front_photo"]["tmp_name"], $front_path);
move_uploaded_file($_FILES["back_photo"]["tmp_name"], $back_path);
move_uploaded_file($_FILES["ingredients_photo"]["tmp_name"], $ingredient_path);
move_uploaded_file($_FILES["nutrition_photo"]["tmp_name"], $nutrition_path);

$stmt_img = $conn->prepare("INSERT INTO product_images (product_id, front_view, back_view, ingredients, nutrition_facts, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
$stmt_img->bind_param("issss", $product_id, $front_path, $back_path, $ingredient_path, $nutrition_path);
$stmt_img->execute();

// review
$stmt_review = $conn->prepare("INSERT INTO reviews (user_id, product_id, review_text, rating, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt_review->bind_param("iisi", $user_id, $product_id, $review_text, $rating);
$stmt_review->execute();

// direct
header("Location: add_product.php?success=1");
exit();
?>