<?php
session_start();
include 'db_connect.php'; // Подключаем файл с подключением к базе данных

$error = ''; // Инициализируем переменную для ошибок

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $first_last_name = trim($_POST['first_last_name']); // Նոր արժեք՝ first_last_name
    $phone_number = trim($_POST['phone_number']);
    $country = trim($_POST['country']); // Նոր արժեք՝ country

    // Ստուգում ենք, որ գաղտնաբառերը համընկնեն և այլն
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Ստուգում ենք, արդյոք email-ը արդեն գոյություն ունի
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "User with this email already exists.";
        } else {
            // Հեշավորում ենք գաղտնաբառը
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Մուտքագրում ենք նոր օգտագործողը բազայում
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_last_name, phone_number, country, role) VALUES (?, ?, ?, ?, ?, 'guest')");
            if ($stmt === false) {
                die("Database query failed: " . $conn->error); // Ստուգում ենք, արդյոք հարցումը հաջողվել է
            }

            $stmt->bind_param("sssss", $email, $hashed_password, $first_last_name, $phone_number, $country);

            if (!$stmt->execute()) {
                die("Database query failed: " . $stmt->error);
            } else {
                header("Location: login");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.css">
    
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
body {
    background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    flex-direction: column;
}

/* Մեդիա հարցումներ՝ փոքր էկրանների համար */
@media (max-width: 768px) {
    body {
        padding-top: 0px;  /* Տարածություն վերևից */
        padding-bottom: 50px;  /* Տարածություն ներքևից */
        height: auto;  /* Հեռացնում ենք ֆիքսված height-ը փոքր էկրանների համար */
    }
}
.register-container {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px; /* Ավելացրել ենք max-width 600px */
    margin: 20px auto; /* Հիմնական մարժինը մեծ էկրանների համար */
}


        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-size: 18px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .alert {
            display: none; /* Թաքցնում ենք նախնական */
        }
        .alert-danger {
            display: block;
            background-color: rgba(255, 0, 0, 0.1);
            border: none;
            color: white;
        }
        .show-hide {
            color: white;
            position: absolute;
            right: 15px;
            top: 38px;
            cursor: pointer;
        }
        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            margin: 20px 0;
        }
        .btn-secondary {
            background-color: #f39c12;
            border: none;
            width: auto;
            padding: 10px 25px;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-secondary:hover {
            background-color: #e67e22;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password a {
            color: #3498db;
            text-decoration: underline;
            font-size: 14px;
        }
        .main-page-btn {
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
            border: 2px solid #3498db;
            text-align: center;
            display: inline-block;
        }
        .main-page-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
.no-credentials {
    text-align: center; /* Կենտրոնացնելը */
    margin-top: 20px; /* Տարածություն վերևից */
}

.click-here-btn {
    display: inline-block; /* Ցուցադրում ենք որպես inline-block */
    background-color: #f3ed17; /* ֆոն */
    color: white; /* Թեքստի գույն */
    padding: 10px 25px; /* Տարածություն */
    border-radius: 25px; /* Կողքերը եզրագծել */
    font-weight: bold; /* Խիտ տառ */
    font-size: 16px; /* Տառի չափ */
    transition: background-color 0.3s ease, transform 0.3s ease; /* Անցման էֆեկտներ */
    margin-top: 10px; /* Տարածություն վերևից */
    text-decoration: none; /* Հեռացնում ենք շեշտվածը */
}

.click-here-btn:hover {
    background-color: #27ae60; /* Դառնում է ավելի մութ կանաչ մաուսի վրա hover-ի ժամանակ */
    transform: scale(1.05); /* Որպեսզի թեթևակի մեծանա */
    color: white;
}
.form-group {
    position: relative;
}

.form-group .position-absolute {
    position: absolute;
    top: 40px; /* Իջեցնում ենք icon-ը մի փոքր ներքև */
    right: 10px; /* Icon-ի դիրքը աջից */
    cursor: pointer;
}

.form-group .fa-eye, 
.form-group .fa-eye-slash {
    color: #425682; /* Կապույտ գույն ենք տալիս icon-երին */
}

/* Մուգ ֆոն երկիրի ընտրողի համար */
        .select2-container--default .select2-selection--single {
            background-color: #6d7ea6;
            color: white;
            border-color: #425682;
                        height: calc(2.25rem + 2px); /* Համապատասխանում է form-control չափին */
  display: flex;
            align-items: center; /* Կենտրոնացնում է ուղղահայաց */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
                        height: 100%; /* Նույն բարձրությունը select2-ի սլաքի համար */
            background-color: #425682;

            color: #425682;
        }
           .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 0.75rem; /* Padding-ի փոխարինում */
            display: flex;
            align-items: center; /* Կենտրոնացնում է երկրները */
        }

        .select2-container--default .select2-results__option {
            background-color: #425682;
            color: white;
        }

        /* Հեռախոսահամարի intl-tel-input-ի ֆոնն ու կոդերը */
        .iti__selected-flag {
            background-color: #425682;
        }

        .iti__flag-container {
            background-color: #425682;
        }

        .iti__dial-code {
            color: white !important;
        }

        .iti__flag {
            background-color: #425682;
        }

        .iti__country-list {
            background-color: #425682;
        }

    </style>
</head>
<body>
<div class="register-container">
        <h2 class="mt-5">Register</h2>

        <!-- Ցուցադրում ենք սխալը, եթե կա -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <!-- Ձախ սյունակ, First and Last Name, Email -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="first_last_name">First and Last Name:</label>
                        <input type="text" id="first_last_name" name="first_last_name" class="form-control" placeholder="John Doe" required>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>
            </div>

            <!-- Երկիր և հեռախոսահամար -->
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <select id="country" name="country" class="form-control" required></select>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Enter your phone number" required>
                    </div>
                </div>
            </div>

            <!-- Գաղտնաբառ և հաստատում -->
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="form-group position-relative">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <span class="position-absolute" style="right: 10px; top: 40px; cursor: pointer;" onclick="togglePasswordVisibility('password', 'password-icon')">
                            <i id="password-icon" class="fas fa-eye" style="color: #1d2f53;"></i>
                        </span>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group position-relative">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                        <span class="position-absolute" style="right: 10px; top: 40px; cursor: pointer;" onclick="togglePasswordVisibility('confirm_password', 'confirm-password-icon')">
                            <i id="confirm-password-icon" class="fas fa-eye" style="color: #1d2f53;"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Գրանցվել կոճակը -->
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>

            <!-- Գիծ գրանցման ֆորմայի տակ -->
            <div class="divider"></div>

            <!-- "Already have an account?" տեքստ և հղում -->
            <div class="no-credentials">
                Already have an account?<br>
                <a href="login" class="click-here-btn">Login here</a>
            </div>
        </form>
    </div>

    <!-- Գլխավոր էջ կոճակ, տեղադրված էջի ներքևում -->
    <a href="index" class="main-page-btn">Go to Main Page</a>

    <!-- Ավելացնում ենք Bootstrap-ի JS և зависимости -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- intl-tel-input JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/utils.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
 <script>
// Intl-Tel-Input Init
const phoneInput = document.querySelector("#phone_number");
const iti = window.intlTelInput(phoneInput, {
    separateDialCode: true, // Only show dial code and flag
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/utils.js",
    nationalMode: false, // Forces the user to enter the international dial code
    formatOnDisplay: false, // Disables formatting of the phone number while typing
    customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
        // Only show the dial code as placeholder
        return selectedCountryData.dialCode;
    },
    dropdownContainer: document.body, // Forces dropdown to be rendered inside the body
    excludeCountries: [], // Exclude countries if needed
    onlyCountries: ['am', 'ru', 'us', 'fr', 'de', 'ge', 'ir'], // Only allow specific countries (Armenia, Russia, USA, France, Germany, Georgia, Iran)
    localizedCountries: {
        'am': 'Armenia',
        'ru': 'Russia',
        'us': 'USA',
        'fr': 'France',
        'de': 'Germany',
        'ge': 'Georgia',
        'ir': 'Iran'
    }
});


// Select2 for Country Selection
$(document).ready(function() {
    const countryData = window.intlTelInputGlobals.getCountryData();
    const countryOptions = countryData.map(country => `<option value="${country.iso2}">${country.name}</option>`).join('');
    $('#country').html(countryOptions);

    // Select2 initialization for country dropdown
    $('#country').select2({
        templateResult: formatCountry,
        templateSelection: formatCountry,
        width: '100%'
    });

    // Set Armenia as the default selected country
    $('#country').val('am').trigger('change'); // Setting 'am' (Armenia) as default

    function formatCountry(country) {
        if (!country.id) {
            return country.text;
        }
        const flagUrl = `https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.6/flags/4x3/${country.element.value}.svg`;
        const $country = $(
            `<span><img src="${flagUrl}" class="img-flag" style="width:20px; margin-right:10px;" /> ${country.text}</span>`
        );
        return $country;
    }
});


        function togglePasswordVisibility(inputId, iconId) {
            const passwordField = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

</body>
</html>
