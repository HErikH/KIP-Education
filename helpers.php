<!-- Write Here Your Helper Functions Here -->

<?php
require_once "constants.php";

function addMediaBaseUrl($path = "")
{
  return MEDIA_BASE_URL . "/" . ltrim($path, "/");
}

function buildFileTree($flatList, $segmentCutLevel = 0)
// ! Be very careful if will make changes then check where does function used for avoiding function breaking

{
  $tree = [];
  if (is_string($flatList['files'])) {
    $flatList['files'] = json_decode($flatList['files'], true);
  }

  foreach ($flatList['files'] as $item) {
    $path = urldecode($item["file"]);
    $parts = array_slice(explode('/', $path), $segmentCutLevel);
    $current = &$tree;

    foreach ($parts as $index => $part) {
      if (!isset($current[$part])) {
        $current[$part] = [
          "_type" => $index === count($parts) - 1 ? "file" : "folder",
          "_data" => $index === count($parts) - 1 ? $item : [],
          "_program_name" => $flatList['program_name']
        ];
      }

      $current = &$current[$part]["_children"];
    }
  }

  return $tree;
}

function renderFileTree($tree, $pathPrefix = '')
{
  foreach ($tree as $name => $node) {
    $type = $node["_type"];
    $programName = $node["_program_name"];
    $safeId = $programName . $pathPrefix . $name;

    if ($type === "folder") {
      echo "
      <div
      class='file-item text-left' 
      onclick=\"toggleSection('$safeId')\"> 
      <i class='fas fa-folder' style='color: #c36f97'></i>$name
      </div>";

      echo "<div id='$safeId' style='display: none; padding-left: 20px;'>";

      if (!empty($node["_children"])) {
        renderFileTree($node["_children"], $pathPrefix . $name);
      }

      echo "</div>";
    } else {
      $fileTitle = $node["_data"]["title"];
      $filePath = $node["_data"]["file"];
      echo "
      <div class='file-item'
        onclick=\"loadFile('$filePath')\">
        <i class='fas fa-file'></i>
        $fileTitle
     </div>";
    }
  }
}
?>
