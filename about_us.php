<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="resource/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>About Us</title>

    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        body::after {
            content: "";
            display: block;
            height: 100px;
        }

        .about-us {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- Include Header -->
    <?php include "header.php"; ?>
    <div class="about-us">
        <h2>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ad autem iste eos error, quae facilis architecto
            quisquam nemo, voluptas pariatur sapiente quaerat quis veniam corrupti obcaecati nisi dignissimos ex doloribus.
        </h2>
    </div>
    <!-- Include Footer -->
    <?php include "footer.php"; ?>
</body>

</html>