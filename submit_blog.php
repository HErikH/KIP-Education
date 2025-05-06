<?php
// Ներառում ենք տվյալների բազայի կապի ֆայլը
include 'db_connect.php';
require_once 'constants.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ստանում ենք ձևից մուտքագրված տվյալները
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Ստուգում ենք՝ արդյոք ֆայլ վերբեռնվել է
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Նկարների համար վերբեռնման թիրախ պանակը
        $target_dir = UPLOAD_DIR . "resource/img/posts/";
        $image_name = time() . '-' . basename($_FILES["image"]["name"]);
        $save_path = $target_dir . $image_name;
        $image_path = IMAGE_URL_BASE_FOR_DB . "resource/img/posts/" . $image_name;
        
        // Փոխադրում ենք վերբեռնված ֆայլը թիրախ պանակ
        move_uploaded_file($_FILES["image"]["tmp_name"], $save_path);
    } else {
        $image_path = NULL;  // Եթե նկար չկա, թող NULL լինի
    }

    // Ստեղծում ենք հարցումը
    $sql = "INSERT INTO blog_posts (title, content, image_url, created_at) VALUES (?, ?, ?, NOW())";

    // Պատրաստում ենք հայտարարությունը
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $image_path);

    // Վազեցնում ենք հարցումը
    if ($stmt->execute()) {
        echo "New blog post created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Փակում ենք հայտարարությունն ու կապը
    $stmt->close();
    $conn->close();
}
?>
