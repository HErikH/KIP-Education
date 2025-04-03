<?php
// Ներառում ենք տվյալների բազայի կապի ֆայլը
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ստանում ենք ձևից մուտքագրված տվյալները
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Ստուգում ենք՝ արդյոք ֆայլ վերբեռնվել է
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Նկարների համար վերբեռնման թիրախ պանակը
        $target_dir = "resource/img/posts/";
        $image_name = time() . '-' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_url = $target_file;
        
        // Փոխադրում ենք վերբեռնված ֆայլը թիրախ պանակ
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } else {
        $image_url = NULL;  // Եթե նկար չկա, թող NULL լինի
    }

    // Ստեղծում ենք հարցումը
    $sql = "INSERT INTO blog_posts (title, content, image_url, created_at) VALUES (?, ?, ?, NOW())";

    // Պատրաստում ենք հայտարարությունը
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $image_url);

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
