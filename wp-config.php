<?php
define( 'DB_NAME', 'aster_stays_showcase' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '127.0.0.1:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'AUTH_KEY', '4EpZJTYy+cx6qMkG3dSY4/BOShPP+HE8WhR7i9kEUazmPZ404VzcXQMHfNeDM2Vo' );
define( 'SECURE_AUTH_KEY', 'qbHk5dw4HwQPAzmi86yIvGNuJ3eK9Ry/tr4zq28dLJyHpRQMAFqRVB5XtxDnsUcc' );
define( 'LOGGED_IN_KEY', 'u0mjmhsO8ekmFHU5GzFyjQO9jykpM7IgRutd770w2aBQ3VzSxZY6M86fXhLIwd0N' );
define( 'NONCE_KEY', 'YtEiIAPE32eTlNEC8Zyh+bmICqDHZnPEE4rGYQJI4Vx8eJAQnfTGllgPD0+2Cih/' );
define( 'AUTH_SALT', 'IQ+3uCEYL0IHcHc1OcPZMCwbP+RP0ZGUaUMhrf6gp9aRil66M2de4CY37OaJWwNt' );
define( 'SECURE_AUTH_SALT', '81Om5tMf4gFkEJO+x3aSn1vjVy9h5BOhYixd/pAPeMKrsaciYEBXqed3Z8pNUDN7' );
define( 'LOGGED_IN_SALT', 'AgZvIydkJsXSNMFpio0xAimd3kExJK0xkPIOT3XRO8iihASZRJ71zTcNMkeAoe86' );
define( 'NONCE_SALT', 'cNcxBFR+dWxPNxXf/kP7kdVFamA0kscE3T6KHOtf7EE5PW03XE0xGPMipej8Izvh' );
$table_prefix = 'wp_aster_';
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_MEMORY_LIMIT', '256M' );
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';