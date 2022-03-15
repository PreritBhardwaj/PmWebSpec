<?php

function downlondEsubft($data) {

		$path = str_replace(pkms_path, '', $data['dataset_path']);
		$path = str_replace(pkms_path2, '', $path);
		$path           = str_replace('/', '_', $path);
		$path           = str_replace('prd', 'dev', $path);
		$path           = str_replace('final_derived', 'doc', $path);
		$specid 		= str_replace(':', '-', $data['spec_id']);
		$file_name       = $specid . '_'. $data['dataset_name'] . '_v' . $data['version_id'] . '_eSub_spec' .'.csv';
		$target_path    = str_replace('final/derived', 'doc', $data['dataset_path']);
		$target_path    = str_replace('prd', 'dev', $target_path);

		$source_path = s3_bucket_path;
		//date_default_timezone_set('US/Eastern');
		$status = "Pending";

		$fileData = [
			'spec_id' => $specid,
			'file_name' => $file_name,
			'source_path' => $source_path,
			'target_path' => $target_path,
			'status' => $status,
		];

		$files_details =& get_instance();
		$files_details->load->model('CIModSpec');
		$files_details->CIModSpec->insert_file("file_transfer", $fileData);

		if(!is_dir(exportcsvpath)){
			mkdir(exportcsvpath, 0755, true);
		}

		$csv_data = NULL;
		$csv_filename = exportcsvpath . $file_name;

		$fd         = fopen($csv_filename, "w");

		function print_titles($row){
			echo implode(array_keys($row), ",") . "\n";
		}

		$firstrow = array('Variable', 'Label', 'Comment','Codes');

		fputs($fd, implode($firstrow, ",") . "\n");
		$csv_data .= implode($firstrow, ",") . "\n";

		fputs($fd, implode(array('*', 'Some random text'), ",") . "\n");
		$csv_data .= implode(array('*', 'Some random text'), ",") . "\n";

		fputs($fd, implode(array('*', 'More random text'), ",") . "\n");
		$csv_data .= implode(array('*', 'More random text'), ",") . "\n";

		 fputs($fd, implode(array('DSLABEL', $data['dataset_label']), ",") . "\n");
    	 $csv_data .= implode(array('DSLABEL',  $data['dataset_label']), ",") . "\n";


		$lname = $_REQUEST["passvalue"];
		$pieces = explode("@@", $lname);

		for( $i = 0; $i<count($pieces)/4-1; $i++ ) {
			$row = array_slice($pieces, $i*4, 4);
			$row = array_map(function($value) { return '"'. str_replace('"', "'", str_replace("\n", "", str_replace("\r", " ", $value))) .'"';}, $row);

			fputs($fd, implode(array_values($row), ",") . "\n");
			$csv_data .= implode(array_values($row), ",") . "\n";
		}

		//fputcsv($fd, $csv_data);
		//fwrite($fd, $csv_data);

		ob_flush();
		ob_clean();

		fclose($fd);

		include "S3connection.php";
		$file_Path = $csv_filename;
		file_transfer($file_Path, s3_bucket_path);

	unset($csv_data);

}

?>
