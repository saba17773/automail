<?php

namespace App\Weekly_Report_OTR_Group;

use App\Common\View;
use App\Weekly_Report_OTR_Group\OTR_GroupAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OTR_GroupController
{

	public function __construct()
	{
		$this->view = new View;
		$this->api = new OTR_GroupAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}
	public function sendmail($request, $response, $args)
	{
		try {

			$projectId = 57;

			//$root = 'D:\automail\\Shipment_Plan_Camso\\';
			// $root = 'D:\automail\OTR_Group\\';
			$root = 'D:\automail\weekly_report_OTR_Group\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$files = $this->automail->getDirRoot($root);

			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($getMail, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			//$bodyInternal = $this->api->getBodyinter();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

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
			// echo "<pre>" . print_r($allFiles[0], true) . "</pre>";
			// exit;
			//s	foreach ($allFiles as $file) {
			//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
			$subject = $this->api->getSubject($datecheck);
			$body = $this->api->getBody($datecheck);
			$file1 = $allFiles[0];
			$file1 = implode(" ", $file1);
			//$bodyInternal = $this->api->getBodyinter();
			// print_r($file1); exit();
			// echo $body;

			$sendEmailExternal = $this->email->sendEmail(
				$subject,
				$body,
				$getMail['toExternal'],
				$getMail['ccExternal'],
				[],
				[$root . $file1],
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
					[$root . $file1],
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
					$file1,
					'File'
				);

				$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
				$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

				if ($sendEmailInternal == true) {

					echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
				}
				$this->automail->initFolder($root, 'temp');
				$this->automail->moveFile($root, $root, 'temp/', $file1);
			} else {
				//echo $sendEmail;
				//	sendfailed movefile

				$this->automail->initFolder($root, 'failed');
				$this->automail->moveFile($root, $root, 'failed/', $file1);
			}
			//	}
			//	}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function sendmailBlacksmith($request, $response, $args)
	{
		try {

			$projectId = 60;

			//$root = 'D:\automail\\Shipment_Plan_Camso\\';
			// $root = 'D:\automail\OTR_Group\\';
			$root = 'D:\automail\OTR_Group\Blacksmith\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$files = $this->automail->getDirRoot($root);

			$getMail = $this->api->getMail($projectId);
			//echo "<pre>" . print_r($getMail, true) . "</pre>";
			//exit();
			$inputfile  = $root . $files[0];
			//echo $inputfile;
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
			// $filetype = PHPExcel_IOFactory::identify($inputfile);

			// $excel2 = PHPExcel_IOFactory::createReader($filetype);
			// $excel2 = $excel2->load($inputfile);

			// $excel2->setActiveSheetIndex(0);
			// $excel2->getActiveSheet()->setTitle('Weekly Report Blacksmith');
			// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
			// $objWriter->save($inputfile);
			//exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			//$bodyInternal = $this->api->getBodyinter();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();




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
			// echo "<pre>" . print_r($allFiles[0], true) . "</pre>";
			// exit;
			//s	foreach ($allFiles as $file) {
			//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
			$subject = $this->api->getSubjectSm($datecheck);
			$body = $this->api->getBody($datecheck);
			$file1 = $allFiles[0];
			$file1 = implode(" ", $file1);
			//$bodyInternal = $this->api->getBodyinter();
			// print_r($file1); exit();
			// echo $body;

			$sendEmailExternal = $this->email->sendEmail(
				$subject,
				$body,
				$getMail['toExternal'],
				$getMail['ccExternal'],
				[],
				[$root . $file1],
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
					[$root . $file1],
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
					$file1,
					'File'
				);

				$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
				$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

				if ($sendEmailInternal == true) {

					echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
				}
				$this->automail->initFolder($root, 'temp');
				$this->automail->moveFile($root, $root, 'temp/', $file1);
			} else {
				//echo $sendEmail;
				//	sendfailed movefile

				$this->automail->initFolder($root, 'failed');
				$this->automail->moveFile($root, $root, 'failed/', $file1);
			}
			//	}
			//	}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function sendmailOTR($request, $response, $args)
	{
		try {

			$projectId = 61;

			//$root = 'D:\automail\\Shipment_Plan_Camso\\';
			// $root = 'D:\automail\OTR_Group\\';
			$root = 'D:\automail\OTR_Group\OTRGroup\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$files = $this->automail->getDirRoot($root);

			$getMail = $this->api->getMail($projectId);
			//echo "<pre>" . print_r($getMail, true) . "</pre>";
			//exit();
			$inputfile  = $root . $files[0];
			//echo $inputfile;
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
			// $filetype = PHPExcel_IOFactory::identify($inputfile);

			// $excel2 = PHPExcel_IOFactory::createReader($filetype);
			// $excel2 = $excel2->load($inputfile);

			// $excel2->setActiveSheetIndex(0);
			// $excel2->getActiveSheet()->setTitle('Weekly Report OTR');
			// $objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype);
			// $objWriter->save($inputfile);
			//exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('d S F Y');
			//$bodyInternal = $this->api->getBodyinter();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();




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
			// echo "<pre>" . print_r($allFiles[0], true) . "</pre>";
			// exit;
			//s	foreach ($allFiles as $file) {
			//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
			$subject = $this->api->getSubject($datecheck);
			$body = $this->api->getBody($datecheck);
			$file1 = $allFiles[0];
			$file1 = implode(" ", $file1);
			//$bodyInternal = $this->api->getBodyinter();
			// print_r($file1); exit();
			// echo $subject;
			// exit();

			$sendEmailExternal = $this->email->sendEmail(
				$subject,
				$body,
				$getMail['toExternal'],
				$getMail['ccExternal'],
				[],
				[$root . $file1],
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
					[$root . $file1],
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
					$file1,
					'File'
				);

				$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
				$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

				if ($sendEmailInternal == true) {

					echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
				}
				$this->automail->initFolder($root, 'temp');
				$this->automail->moveFile($root, $root, 'temp/', $file1);
			} else {
				//echo $sendEmail;
				//	sendfailed movefile

				$this->automail->initFolder($root, 'failed');
				$this->automail->moveFile($root, $root, 'failed/', $file1);
			}
			//	}
			//	}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}
