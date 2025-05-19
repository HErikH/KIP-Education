<?php // Base URL for accessing all medias from sub domain
define("MEDIA_BASE_URL", "https://media.kipeducationid.com"); ?>
<?php // All names of programs be sure that you add new program name here
define("ALL_PROGRAM_NAMES", ["K1"]); ?>
<?php if (strpos($_SERVER["HTTP_HOST"], "localhost") !== false) {
  // Physical path for moving uploaded files
  define("UPLOAD_DIR", ""); 
  // Base URL to be saved in the DB for image reference
  define("MEDIA_BASE_URL_FOR_DB", "");
} else {
  // Physical path for moving uploaded files
  define("UPLOAD_DIR", "/home2/admin12345/media.kipeducationid.com/"); 
  // Base URL to be saved in the DB for image reference
  define("MEDIA_BASE_URL_FOR_DB", "https://media.kipeducationid.com/");
} ?>
<?php define("PROGRAMS_ABOUT_INFO_NAMES", [
  "group lesson" => "group-lessons",
  "private lesson" => "private-lessons",
  "Let's learn alone" => "learn-alone-lessons",
]); ?>