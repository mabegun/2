<?php
/**
 * The header for the theme
 *
 * @package ProKB
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <!-- Fallback for prokbData if wp_localize_script fails -->
    <script>
        if (typeof prokbData === 'undefined') {
            var prokbData = {
                ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo wp_create_nonce('prokb_nonce'); ?>'
            };
        }
    </script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
