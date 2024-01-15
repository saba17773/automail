<?php

namespace App\TriegroupAndatturo;

use App\Common\View;
use App\TriegroupAndatturo\TriegroupAndatturoAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TriegroupAndatturoController
{

	public function __construct()
	{
		$this->view = new View;
		$this->api = new TriegroupAndatturoAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}
	public function sendmail($request, $response, $args)
	{
		try {

			$projectId = 51;
			$root = 'D:\automail\Tiregroup_Atturo\\';
			//$root = 'D:\automail\Tiregroup_Atturo\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$file = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($getMail, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			$body = $this->api->getBody();
			$subject = $this->api->getSubject($datecheck);


			// echo $subject;
			
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


			$inputfile2  = $root . $file[0];

			$Filecheck = explode(".", $file[0]);
			rename($inputfile2, $root . $Filecheck[0] . $datecheck . "." . $Filecheck[1]);
			$files = $this->automail->getDirRoot($root);
			$inputfile  = $root . $files[0];

			// echo $inputfile;
			// exit();

			// $filetype = PHPExcel_IOFactory::identify($inputfile);

			// $excel2 = PHPExcel_IOFactory::createReader($filetype);
			// $excel2 = $excel2->load($inputfile);

			// $excel2->setActiveSheetIndex(0);
			// $excel2->getActiveSheet()->setCellValue('A1', '');
			// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
			// $objWriter->save($inputfile);
			// echo "<pre>" . print_r($files, true) . "</pre>";

			// exit();


			// exit();
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {

						$allFiles[] = [
							'file_name' => $file
						];
					}
				}
			}
			sort($allFiles);
			// echo "<pre>".print_r($allFiles,true)."</pre>";
			// exit;
			foreach ($allFiles as $file) {
				//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
				$subject = $this->api->getSubject($datecheck);
				$body = $this->api->getBody();

				// echo "<pre>"."sender :".print_r($getMail['sender'],true)."</pre>";
				// echo "<pre>"."to External :".print_r($getMail['toExternal'],true)."</pre> <br/>";
				// echo "<pre>"."cc External :".print_r($getMail['ccExternal'],true)."</pre> <br/>";
				// echo "<pre>"."to Internal :".print_r($getMail['toInternal'],true)."</pre> <br/>";
				// echo "<pre>".print_r($root . $a['file_name'],true)."</pre>";
				// echo "<pre>".print_r($subject,true)."</pre>";
				// echo "<pre>".print_r($body,true)."</pre>";
				// exit;

				$sendEmailExternal = $this->email->sendEmail(
					$subject,
					$body,
					$getMail['toExternal'],
					$getMail['ccExternal'],
					[],
					[$root . $file['file_name']],
					'',
					$getMail['sender']
				);
			//	exit();
				
				if ($sendEmailExternal == true) {
					echo "<pre>" . print_r("Message has been sent to External !!", true) . "</pre>";

					$sendEmailInternal = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['toInternal'],
						[],
						[],
						[$root . $file['file_name']],
						'',
						$getMail['sender']
					);

					$logging = $this->automail->logging(
						$projectId,
						'Message has been sent',
						null,
						null,
						null,
						null,
						null,
						$files,
						'File'
					);

					$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
					$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

					if ($sendEmailInternal == true) {

						echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
					}
					$this->automail->initFolder($root, 'temp');
					$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);
				} else {
					//echo $sendEmail;
					// sendfailed movefile
					$this->automail->initFolder($root, 'failed');
					$this->automail->moveFile($root, $root, 'failed/', $file['file_name']);
				}
				
		 	}
		 } catch (\Exception $e) {
		 	echo $e->getMessage();
		 }
	}

	public function sendmail_V2($request, $response, $args)
	{
		try {

			$projectId = 56;
			// $root = 'D:\automail\\Shipment_Plan_Camso\\';
			$root = 'D:\automail\Tiregroup_Atturo\autorov2\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$file = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($getMail, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			// $body = $this->api->getBody_v2();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


			$inputfile2  = $root . $file[0];

			$Filecheck = explode(".", $file[0]);
			rename($inputfile2, $root . $Filecheck[0] . $datecheck . "." . $Filecheck[1]);
			$files = $this->automail->getDirRoot($root);
			$inputfile  = $root . $files[0];

			// echo $inputfile;
			// exit();

			// $filetype = PHPExcel_IOFactory::identify($inputfile);

			// $excel2 = PHPExcel_IOFactory::createReader($filetype);
			// $excel2 = $excel2->load($inputfile);

			// $excel2->setActiveSheetIndex(0);
			// $excel2->getActiveSheet()->setCellValue('A1', '');
			// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
			// $objWriter->save($inputfile);
			// echo "<pre>" . print_r($files, true) . "</pre>";

			// exit();


			// exit();
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {

						$allFiles[] = [
							'file_name' => $file
						];
					}
				}
			}
			sort($allFiles);
			// echo "<pre>".print_r($allFiles,true)."</pre>";
			// exit;
			foreach ($allFiles as $file) {
				//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
				$subject = $this->api->getSubject_v2($datecheck);
				$body = $this->api->getBody_v2($datecheck);

				// echo "<pre>"."sender :".print_r($getMail['sender'],true)."</pre>";
				// echo "<pre>"."to External :".print_r($getMail['toExternal'],true)."</pre> <br/>";
				// echo "<pre>"."cc External :".print_r($getMail['ccExternal'],true)."</pre> <br/>";
				// echo "<pre>"."to Internal :".print_r($getMail['toInternal'],true)."</pre> <br/>";
				// echo "<pre>".print_r($root . $a['file_name'],true)."</pre>";
				// echo "<pre>".print_r($subject,true)."</pre>";
				// echo "<pre>".print_r($body,true)."</pre>";
				// exit;

				$sendEmailExternal = $this->email->sendEmail(
					$subject,
					$body,
					$getMail['toExternal'],
					$getMail['ccExternal'],
					[],
					[$root . $file['file_name']],
					'',
					$getMail['sender']
				);

				if ($sendEmailExternal == true) {
					echo "<pre>" . print_r("Message has been sent to External !!", true) . "</pre>";

					$sendEmailInternal = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['toInternal'],
						[],
						[],
						[$root . $file['file_name']],
						'',
						$getMail['sender']
					);

					$logging = $this->automail->logging(
						$projectId,
						'Message has been sent',
						null,
						null,
						null,
						null,
						null,
						$files,
						'File'
					);

					$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
					$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

					if ($sendEmailInternal == true) {

						echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
					}
					$this->automail->initFolder($root, 'temp');
					$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);
				} else {
					//echo $sendEmail;
					// sendfailed movefile
					$this->automail->initFolder($root, 'failed');
					$this->automail->moveFile($root, $root, 'failed/', $file['file_name']);
				}
				//	}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function sendmail_internal($request, $response, $args)
	{
		try {

			$projectId = 58;
			//$root = 'D:\automail\Tiregroup_Atturo\Atturo_internal\\';
			$root = 'D:\automail\Tiregroup_Atturo\Atturo_internal\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$file = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($getMail, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			$body = $this->api->getBody_internal();
			$subject = $this->api->getSubject_internal($datecheck);


			// echo $subject;
			
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


			$inputfile2  = $root . $file[0];

			$Filecheck = explode(".", $file[0]);
			rename($inputfile2, $root . $Filecheck[0] . $datecheck . "." . $Filecheck[1]);
			$files = $this->automail->getDirRoot($root);
			$inputfile  = $root . $files[0];

			// echo $inputfile;
			// exit();

			// $filetype = PHPExcel_IOFactory::identify($inputfile);

			// $excel2 = PHPExcel_IOFactory::createReader($filetype);
			// $excel2 = $excel2->load($inputfile);

			// $excel2->setActiveSheetIndex(0);
			// $excel2->getActiveSheet()->setCellValue('A1', '');
			// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
			// $objWriter->save($inputfile);
			
			// echo "<pre>" . print_r($files, true) . "</pre>";

			// exit();


			// exit();
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {

						$allFiles[] = [
							'file_name' => $file
						];
					}
				}
			}
			sort($allFiles);
			// echo "<pre>".print_r($allFiles,true)."</pre>";
			// exit;
			foreach ($allFiles as $file) {
				$subject = $this->api->getSubject_internal($datecheck);
				$body = $this->api->getBody_internal();

				$sendEmailInternal = $this->email->sendEmail(
					$subject,
					$body,
					$getMail['toInternal'],
					$getMail['ccInternal'],
					[],
					[$root . $file['file_name']],
					'',
					$getMail['sender']
				);
				// echo $body;
				// exit();
				
				if ($sendEmailInternal == true) {
					echo "<pre>" . print_r("Message has been sent to External !!", true) . "</pre>";

					$logging = $this->automail->logging(
						$projectId,
						'Message has been sent',
						null,
						null,
						null,
						null,
						null,
						$files,
						'File'
					);

					$this->automail->loggingEmail($logging, $getMail['toInternal'], 1); //1To
					$this->automail->loggingEmail($logging, $getMail['ccInternal'], 2); //cc

					
					$this->automail->initFolder($root, 'temp');
					$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);
				} else {
					//echo $sendEmail;
					// sendfailed movefile
					$this->automail->initFolder($root, 'failed');
					$this->automail->moveFile($root, $root, 'failed/', $file['file_name']);
				}
				
		 	}
		 } catch (\Exception $e) {
		 	echo $e->getMessage();
		 }
	}

	public function sendmail_internal_test($request, $response, $args) {
		$projectId = 58;
		//$root = 'D:\automail\Tiregroup_Atturo\Atturo_internal\\';
		$root = 'D:\automail\saba\\';
		// $name = "Weekly Report Tire group and Atturo.xls";
		$file = $this->automail->getDirRoot($root);
		$getMail = $this->api->getMail($projectId);
		// echo "<pre>" . print_r($getMail, true) . "</pre>";
		// exit();

		$date2 = date("Y-m-d");
		$date = date_create($date2);
		$datecheck = $date->format('d S F Y');
		$body = $this->api->getBody_internal();
		$subject = $this->api->getSubject_internal($datecheck);


		// echo $subject;
		
		// echo $body;
		// exit();
		// echo "<pre>" . print_r($getMail, true) . "</pre>";

		// exit();


		require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
		require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


		$inputfile2  = $root . $file[0];

		$Filecheck = explode(".", $file[0]);
		rename($inputfile2, $root . $Filecheck[0] . $datecheck . "." . $Filecheck[1]);
		$files = $this->automail->getDirRoot($root);
		$inputfile  = $root . $files[0];

		// echo $inputfile;
		// exit();

		// $filetype = PHPExcel_IOFactory::identify($inputfile);

		// $excel2 = PHPExcel_IOFactory::createReader($filetype);
		// $excel2 = $excel2->load($inputfile);

		// $excel2->setActiveSheetIndex(0);
		// $excel2->getActiveSheet()->setCellValue('A1', '');
		// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
		// $objWriter->save($inputfile, __FILE__, ['Xlsx', 'Xls', 'Ods']);
		
		echo "<pre>" . print_r($files, true) . "</pre>";
		exit();
	
	}
}
