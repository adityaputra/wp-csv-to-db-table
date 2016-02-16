<?php
/*
 * Plugin Name: CSV Importer to DB Table
 * Version: 0.1
 * Plugin URI: http://www.adityaputra.com/
 * Description: Import any CSV file to desired DB table.</em>.
 * Author: Aditya Putra
 * Author URI: http://adityaputra.com
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>


<?php
require __DIR__ . '/options.php'; 
?>

<?php // add the admin options page
add_action('admin_menu', 'plugin_admin_add_page');
function plugin_admin_add_page() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('datatables', plugins_url('/jquery.dataTables.min.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_style('datatables-style', plugins_url('/jquery.dataTables.min.css', __FILE__));
	
	add_menu_page('CSV to DB Upload Page', 'CSV to DB', 'manage_options', 'csv-to-db', 'csv_to_db_upload_page');
	add_submenu_page('csv-to-db', 'CSV to DB Upload', 'Upload', 'manage_options', 'csv-to-db');
	add_submenu_page('csv-to-db', 'CSV to DB Import', 'Import', 'manage_options', 'csv-to-db-import', 'csv_to_db_import_page');
// add_options_page('CSV to DB Upload Page', 'CSV to DB', 'manage_options', 'csv-to-db', 'csv_to_db_upload_page');
// add_options_page('CSV to DB Import Page', 'CSV to DB', 'manage_options', 'csv-to-db-import', 'csv_to_db_upload_page');
}
?>


<?php // display the admin options page
function csv_to_db_upload_page() {
?>
<div>
<h1>CSV to DB Plugin</h1><br/>

<form action="options.php" method="post" enctype="multipart/form-data">
<?php settings_fields('csv_to_db_options'); ?>
<?php do_settings_sections('section1'); ?>
 
<input name="Submit" type="submit" value="<?php esc_attr_e('Continue'); ?>" />
</form></div>
 
<?php
}?>

<?php // display the admin options page
function csv_to_db_import_page() {
?>
<div>
<h1>CSV to DB Plugin</h1><br/>

<form action="options.php" method="post">
<?php settings_fields('csv_to_db_options'); ?>
<?php do_settings_sections('section2'); ?>
 
<input name="Submit" type="submit" value="<?php esc_attr_e('Continue'); ?>" />
</form></div>
 
<?php
}?>


<?php // add the admin settings and such
add_action('admin_init', 'plugin_admin_init');
function plugin_admin_init(){
register_setting( 'csv_to_db_options', 'csv_to_db_options', 'csv_to_db_options_validate' );
add_settings_section('plugin_main', 'Upload CSV', 'plugin_section_text', 'section1');
add_settings_field('csv_to_db_file', 'Select CSV', 'csv_to_db_setting_1_file', 'section1', 'plugin_main');
add_settings_field('csv_to_db_checkbox_first_row_header', 'First row header', 'csv_to_db_setting_1_checkbox', 'section1', 'plugin_main');
// add_settings_field('plugin_text_string', 'Plugin Text Input', 'plugin_setting_string', 'section1', 'plugin_main');

add_settings_section('csv_to_db_setting_db', 'Import Setting', 'plugin_section_text_2', 'section2');
add_settings_field('csv_to_db_table_name', 'Target Table Name', 'csv_to_db_setting_2_table_name', 'section2', 'csv_to_db_setting_db');
add_settings_field('csv_to_db_use_prefix', 'Use WP Table Prefix', 'csv_to_db_setting_2_checkbox_use_prefix', 'section2', 'csv_to_db_setting_db');

add_settings_section('plugin_second', 'Secondary Settings', 'plugin_section_text_2', 'plugin_2');
add_settings_field('plugin_text_string_2', 'Plugin Text Input 2', 'plugin_setting_string_2', 'plugin_2', 'plugin_second');
}?>


<?php function plugin_section_text() {
echo '<p>Upload your desired CSV file</p>';
} ?>

<?php function plugin_section_text_2() {
echo '<p>Main description of this section 2 here.</p>';
echo '<br/>';
$header = get_option('csv-to-db-section1-header');
$content = get_option('csv-to-db-section1-content');
$datatype = get_option('csv-to-db-section1-datatype');
// print_r($header);

?>

	<table class="datatable display" cellspacing="0" width="100%">
		<thead>
			<tr>
				<?php
				if(!empty($header)){
					foreach ($header as $key => $value) {
						echo "<th>".$value." [".$datatype[$key][0]."(".$datatype[$key][1].")]"."</th>";
					}
				}
				else {
					$cols = count($content[0]);
					
					for($i = 0; $i < $cols ; $i++){
						echo "<th>col-".($i+1)." [".$datatype[$key][0]."(".$datatype[$key][1].")]"."</th>";
					}
				}
				
				?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($content as $key => $value) {
				echo "<tr>";
				foreach ($value as $key2 => $value2) {
					echo "<td>".$value2."</td>";
				}
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<script>
		jQuery(document).ready(function() {
		    jQuery('.datatable').DataTable();
		} );
	</script>
<?php

} ?>

<?php
function csv_to_db_setting_1_file(){
	$options = get_option('csv_to_db_options');
	echo "<input id='plugin_hidden_csv_to_db' name='csv_to_db_options[hidden_csv_to_db]' size='40' type='hidden' value='TRUE' />";
	echo "<input id='plugin_check_file' name='csv_to_db_options[file_csv_to_db]' type='file' value='{$options['file_csv_to_db']}' />";
}
function csv_to_db_setting_1_checkbox(){
	$options = get_option('csv_to_db_options');
	echo "<input id='plugin_check_is_header' name='csv_to_db_options[check_is_header]' type='checkbox' value='{$options['check_is_header']}' />";
}
?>

<?php function plugin_setting_string() {
$options = get_option('csv_to_db_options');
echo "<input id='plugin_hidden_csv_to_db' name='csv_to_db_options[hidden_csv_to_db]' size='40' type='hidden' value='TRUE' />";


echo "<input id='plugin_text_string' name='csv_to_db_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
} ?>

<?php function csv_to_db_setting_2_table_name() {
$options = get_option('csv_to_db_options');
echo "<input id='plugin_text_table_name' name='csv_to_db_options[text_table_name]' size='40' type='text' value='{$options['text_table_name']}' />";
echo "<input id='plugin_hidden_csv_to_db_table_setting' name='csv_to_db_options[hidden_csv_to_db_table_setting]' size='40' type='hidden' value='TRUE' />";
}
function csv_to_db_setting_2_checkbox_use_prefix(){
	$options = get_option('csv_to_db_options');
	echo "<input id='plugin_check_use_prefix' name='csv_to_db_options[check_use_prefix]' type='checkbox' value='{$options['check_use_prefix']}' />";
	global $wpdb;

	echo "<p>Current WP table prefix is <strong>".$wpdb->prefix."</strong></p>";
} 
?>

<?php function plugin_setting_string_2() {
$options = get_option('csv_to_db_options');
echo "<input id='plugin_text_string' name='csv_to_db_options[text_string_2]' size='40' type='text' value='{$options['text_string']}' />";
} ?>

<?php // validate our options
function csv_to_db_options_validate($input) {
$options = get_option('csv_to_db_options');
$options['text_string'] = trim($input['text_string']);
if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
$options['text_string'] = '';
}
return $options;
}
?>

