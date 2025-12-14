<?php

// Function to create folders within a given directory
function createFolders($start, $end, $path)
{
  // Check if the path exists, if not create it
  if (!file_exists($path)) {
    mkdir($path, 0777, true);
    echo "Directory '$path' created.\n";
  }

  // Loop through the range and create each folder
  for ($i = $start; $i <= $end; $i++) {
    $folderName = $path . DIRECTORY_SEPARATOR . $i;

    if (!file_exists($folderName)) {
      mkdir($folderName, 0777, true);
      echo "Folder '$folderName' created.\n";
    } else {
      echo "Folder '$folderName' already exists.\n";
    }
  }
}

// Example usage
$start = 274; // Start number
$end = 337; // End number
$path = "../uploads/lessons"; // Folder where to create the subfolders

// Call the function
createFolders($start, $end, $path);
?>
