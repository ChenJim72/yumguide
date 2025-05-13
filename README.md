# 🍱 YumGuide – Food Product Review Platform

YumGuide is a full-stack web application that allows users to submit, browse, and review packaged food products. Built with PHP and MySQL, it includes user authentication, dynamic product submission, AJAX-based reviews, and admin moderation tools.

---

## 🚀 Features

- 🔐 User registration and login with hashed passwords
- 📦 Product submission with dynamic categories, barcode duplication check, and image uploads
- 🌟 Star rating and review system using AJAX
- 💬 Comment replies and like/emoji feedback
- 🛠 Admin panel for product and suggestion approval
- 🏷️ Select2-enhanced dropdowns for store selection
- 📊 User levels based on contributions (products, reviews, replies)

---

## 🛠 Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript, jQuery, Select2, SVG
- **Backend:** PHP 8+, MySQL (phpMyAdmin)
- **AJAX:** jQuery-based async interactions for reviews, likes, replies
- **File Handling:** Upload and store multiple product images
- **Security:** Session handling and basic access control

---

## 🗂 Folder Structure

yumguide/
├── index.php # Homepage / login
├── add_product.php # Product submission form
├── products.php # Product list / search result
├── product_details.php # Single product page
├── submit_review.php # AJAX endpoint for reviews
├── send_suggestion.php # Suggest edit form handler
├── config.example.php # Database connection sample
├── styles.css, account.css # Main styling files
├── /pictures # SVGs and demo images
├── /uploads # Image uploads (not tracked in Git)


---

## 📦 Database

Use the `yumguide_schema.sql` file to create the database structure.

```sql
-- Example: Create table `products`
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category_id INT,
  brand VARCHAR(255)
  -- Add additional fields as needed
);

📝 Note: This project is designed for local use with XAMPP/AMPPS.
Update your config.php with your own DB credentials (see config.example.php).

🧑‍💻 Author
Chun-Ying Chen
Web Development & Internet Applications
Algonquin College (2025)
GitHub: @ChenJim72

📄 License
This project is for educational and portfolio use. Feel free to fork and build on it!


