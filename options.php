<?php
if($_POST['csv_to_db_options']['hidden_csv_to_db'] == TRUE){
	// print_r($_POST) ; print_r($_FILES['csv_to_db_options']);exit;
	
	$param = $_POST['csv_to_db_options'];
	$fParam = $_FILES['csv_to_db_options'];
	
	$is_first_title = 0;
	if(isset($param['check_is_header'])){
		$is_first_title = 1;
	}
	// print_r($param);echo $is_first_title; exit;
	$csv = array();
	$header = array();
	$dataType = array();
	// print_r($fParam); exit;
	// check there are no errors
	if($fParam['error']['file_csv_to_db'] == 0){
	    $name = $fParam['name']['file_csv_to_db'];
	    $ext = strtolower(end(explode('.', $fParam['name']['file_csv_to_db'])));
	    $type = $fParam['type']['file_csv_to_db'];
	    $tmpName = $fParam['tmp_name']['file_csv_to_db'];
		
		
	
	    // check the file is a csv
	    if($ext === 'csv' && $type == 'text/csv'){
	        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
	            // necessary if a large csv file
	            set_time_limit(0);
	
	            $row = 0;
	
	            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
	                // number of fields in the csv
	                $col_count = count($data);
	
	                if($row == 0 && $is_first_title == 1) {
	                	foreach ($data as $key => $value) {
							$header[$key] = trim($value);
						}
						$row++;
	                	continue;
					}
					else{
						foreach ($data as $key => $value) {
							$header[$key] = 'col_'.($key+1);
						}
					}
					foreach ($data as $key => $value) {
						
						$csv[$row][$key] = trim($value);
					}
	
	                // inc the row
	                $row++;
	            }
	            fclose($handle);
	        }
	    }
	}
	// print_r($header);
	// print_r($csv);
// 	
	// exit;
	
	foreach ($csv as $key => $value) {
		foreach ($value as $key2 => $value2) {
			$dataType[$key2][0] = "num";
			$dataType[$key2][1] = 0;
		}
		break;
	}
	
	foreach ($csv as $key => $value) {
		foreach ($value as $key2 => $value2) {
			if(get_numeric($value2)[0] == "str") $dataType[$key2][0] = "str";
			if(get_numeric($value2)[1] > $dataType[$key2][1]) $dataType[$key2][1] = get_numeric($value2)[1];
		}
	}
	
	// print_r($dataType); exit;
	// print_r($csv[0][3]); print_r(get_numeric($csv[0][3])); exit;
	
	update_option('csv-to-db-section1-header', $header);
	update_option('csv-to-db-section1-content', $csv);
	update_option('csv-to-db-section1-datatype', $dataType);
	
	update_option('csv-to-db-section1', TRUE);
	return;
}

if($_POST['csv_to_db_options']['hidden_csv_to_db_table_setting'] == TRUE){
	// print_r($_POST); exit;
	
	
	
	global $jal_db_version;
	$jal_db_version = '1.0';
	jal_install();

	
	$col_name = array();
	$data = array();
	
	$header = get_option('csv-to-db-section1-header');
	$content = get_option('csv-to-db-section1-content');
	foreach ($header as $key => $value) {
		array_push($col_name, str_replace(' ', '_', $header[$key]));
	}
	
	foreach ($content as $key => $value) {
		$datum = array();
		foreach ($value as $key2 => $value2) {
			$datum[$col_name[$key2]] = $value2;
		}
		jal_install_data($datum);
		// print_r($datum);exit;
		array_push($data, $datum);
		// break;
	}
	
	/*
	$data = array( 
				'time' => current_time( 'mysql' ), 
				'name' => $welcome_name, 
				'text' => $welcome_text, 
			) ;*/
	
	jal_install_data($data);
	
	
	// exit;
}

function jal_install() {
	global $wpdb;
	global $jal_db_version;
	
	//print_r($_POST['csv_to_db_options']);
	//echo $_POST['csv_to_db_options']['check_use_prefix']; exit;
	if(isset($_POST['csv_to_db_options']['check_use_prefix'])){
		$table_name = $wpdb->prefix . $_POST['csv_to_db_options']['text_table_name'];
	}
	else{
		$table_name = $_POST['csv_to_db_options']['text_table_name'];
	}
	
	
	$charset_collate = $wpdb->get_charset_collate();
	
	$header = get_option('csv-to-db-section1-header');
	$datatype = get_option('csv-to-db-section1-datatype');
	

	$sql = "CREATE TABLE $table_name (
		CSV_id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
	
	foreach ($datatype as $key => $value) {
		if($value[0] == 'str'){
			$type = 'VARCHAR';
			$len = $value[1];
		}
		else{
			if($value[1] > 0){
				$type = "DOUBLE";
				$len = 11;
			}
			else{
				$type = "INT";
				$len = 11;
			}
		}
		 
		$sql .= str_replace(' ', '_', $header[$key])." ".$type."(".$len."),\n";
	}
		
	$sql .= "
		UNIQUE KEY CSV_id (CSV_id)
	) $charset_collate;";
	// echo $sql; exit;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

function jal_install_data($data) {
	global $wpdb;
	
	if(isset($_POST['csv_to_db_options']['check_use_prefix'])){
		$table_name = $wpdb->prefix . $_POST['csv_to_db_options']['text_table_name'];
	}
	else{
		$table_name = $_POST['csv_to_db_options']['text_table_name'];
	}
	
	$wpdb->insert( 
		$table_name, 
		$data
	);
}

function get_numeric($val) {
	if(!($val)) return array("num", 0);
	else if(!is_numeric($val)) return array("str", strlen($val));
	else return array("num", strlen(substr(strrchr($val, "."), 1)));
}
?> 

