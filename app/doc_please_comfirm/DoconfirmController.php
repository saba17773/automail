<?php

namespace App\doc_please_comfirm;

use App\Common\View;
use App\doc_please_comfirm\DoconfirmAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Shipping\ShippingAPI;

class DoconfirmController
{

	public function __construct()
	{
		$this->view = new View;
		$this->api = new DoconfirmAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
		$this->shipping = new ShippingAPI;
	}

	public function sendmail($request, $response, $args)
	{
		try {
			$projectId = 59;
			$root = 'files/docpleaseconfirm/';
			//$root = 'D:\automail\Weekly_Tireco_Greenball\\';
			$rootTemp = 'temp/doc_confirm/';
			$fileOkay = [];
			$SOSend = "";
			$files = $this->automail->getDirRoot($root);


			if (count($files) === 0) {
				echo "The file does not exist";
				exit();
			}
			// echo "<pre>";
			// //print_r($getMail);
			// //echo $getMail['sender'][0];
			// echo 1234;
			// echo "</pre>";
			// exit();

			foreach ($files as $f) {
				// get customer code	
				$customerCode = $this->api->getCustomerCode($f);
				//$getMail = $this->api->getMailCustomer($projectId, $customerCode);

				// echo $customerCode . PHP_EOL;
				// get qa
				$quantation = $this->api->getCustomerQuantationV2($f);
				$qa_converted = $this->api->convertToInSql($quantation);
				// get inv
				$voucher = $this->api->isIncludeINV($f);
				$voucherstr = substr($voucher, 3, 8);

				// var_dump([$customerCode, $quantation, $voucherstr]);
				// exit;

				if ($this->api->isSURRENDER($f) === false) {
					// check map data
					if ($this->api->mapQuantationManyQA($customerCode, $qa_converted, $voucherstr) === true) {
						// if(true){
						// good file
						$stackFileByCustCode[] = [
							'customer' => $customerCode,
							'file' => $f
						];
					} else {
						// wrong format
						$stackFileFailed[] = $f;
					}
				}

			}
			// echo "<pre>";
			// print_r($getMail);
			// //echo $getMail['sender'][0];

			// echo "</pre>";
			// exit();
			// var_dump($stackFileByCustCode);
			// print_r($stackFileFailed);
			// exit();

			// ########################### check have file to send to customer ###########################
			if (count($stackFileByCustCode) !== 0) {
				// Loop get customer data
				foreach ($stackFileByCustCode as $value) {
					// mail customer

					//$email = ['to' => ['worawut_s@deestone.com'], 'cc' => ['worawut_s@deestone.com']];
					//$group_internal = $this->api->getEmailFromCustomerCode($value['customer']);
					//$group_internal = $getMail['sender'][0];

					// saved!
					$listOfMailDetail[] = [
						'customer' => $value['customer'],
						//'email' => $email,
						'file' => $value['file']
						// 'sender' => $group_internal,
						// // 'internal' => 'wandee_h@deestone.com',
						// 'internal' => $group_internal
					];
				}
				// echo "<pre>";
				// var_dump($listOfMailDetail);
				// echo "</pre>";
				// exit;

				// Loop send email
				foreach ($listOfMailDetail as $m) {
					// echo $this->api->getShippingBodyConfirm($m['file']);
					// exit();
					$getMail = $this->api->getMailCustomer($projectId, $m['customer']);
					// print_r($getMail);
					// print_r($m['file']);
					if ($this->api->getShippingBodyConfirm($m['file']) !== '-') {

						$mail = $this->email->sendEmail(
							$this->api->getShippingSubjectConfirm($m['file']),
							$this->api->getShippingBodyConfirm($m['file']),
							$getMail['to'],
							$getMail['cc'],
							[],
							[$root . $m['file']],
							$getMail['sender'][0],
							$getMail['sender'][0]
						);

						$sendInternal = $this->email->sendEmail(
							$this->api->getShippingSubjectConfirm($m['file']),
							$this->api->getShippingBodyConfirm($m['file']),
							$getMail['Internal'],
							[],
							[],
							[$root . $m['file']],
							$getMail['sender'][0],
							$getMail['sender'][0]
						);

						if ($sendInternal == true) {
							$logging = $this->automail->logging(
								$projectId,
								'Message has been sent',
								null,
								null,
								null,
								null,
								null,
								$m['file'],
								'File'
							);

							$this->automail->loggingEmail($logging, $getMail['to'], 1);
							$this->automail->loggingEmail($logging, $getMail['cc'], 2);
							echo "Message has been sent internal\n";
							$this->automail->initFolder($rootTemp, 'logs');
							$this->automail->moveFile($root, $rootTemp, 'logs/', $m['file']);
						} else {
							echo $sendInternal;
							// sendfailed movefile
							$this->automail->initFolder($root, 'failed');
							$this->automail->moveFile($root, $root, 'failed/', $m['file']);
						}
					}
				}
				
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}
