<!-- Write Here Your Helper Functions Here -->

<?php
require_once 'constants.php';

function media($path = '') {
    return MEDIA_BASE_URL . '/' . ltrim($path, '/');
}