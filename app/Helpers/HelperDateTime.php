<?php
// In a helper file, e.g., app/Helpers/testing_helper.php
function get_current_datetime($format = 'Y-m-d H:i:s') {
    if (ENVIRONMENT === 'testing' && isset($_ENV['MOCK_DATETIME'])) {
        return date($format, strtotime($_ENV['MOCK_DATETIME']));
    }
    return date($format);
}

// Then use it in your controller:
$waktu_sekarang = strtotime(get_current_datetime());