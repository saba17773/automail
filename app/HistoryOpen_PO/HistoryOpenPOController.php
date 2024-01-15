<?php

namespace App\HistoryOpen_PO;

use App\Common\View;
use App\HistoryOpen_PO\HistoryOpenPOAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HistoryOpenPOController
{

	public function __construct()
	{
		$this->view = new View;
		$this->api = new HistoryOpenPOAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function sendmailPO ($request, $response, $args) 
	{
		try 
		{
			$projectId = 63;
			$root = 'D:/automail/HistoryOpen_PO/';
            $rootTemp = 'D:/automail/HistoryOpen_PO/';

            $getMail = $this->api->getMail($projectId);
			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
            }

			foreach ($files as $file) {
                if (gettype($file) !== 'array') {
                    if ($file !== 'Thumbs.db') {
                        preg_match_all('/[^\d_.]+/i', $file, $output_type);
                        $allFiles[] = [
                            'file_name' => $file,
                            'file_type' => strtoupper($output_type[0][1])
                        ];
                    }
                }
            }

            sort($allFiles);

			// echo "<pre>".print_r($allFiles,true)."</pre>";
			// exit;

            $tmp_file = [];
            $count_file = 0;
            $counter_tmp_file = 0;
            $formatType = ['DSI','DSR','STR','DSL','DRB'];


			foreach ($allFiles as $value) 
			{
				if (!in_array($value['file_type'],$formatType)) 
				{
					// exit();
                }
				else
				{
					$tmp_file[$counter_tmp_file][] = $value['file_name'];
                }
            }

			// echo "<pre>".print_r($tmp_file,true)."</pre>";
			// echo count($tmp_file[0]);
			// exit;
			$date = date("Y/m/d");
			for ($i=0; $i < count($tmp_file); $i++) 
			{
				if (count($tmp_file[$i]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }
				else if (count($tmp_file[$i]) === 5) 
				{

					$subject = $this->api->getSubject($date);
                    $body = $this->api->getBody();
                    $files = $this->api->pathTofile($tmp_file[$i],$root);

					// echo "<pre>".print_r($subject,true)."</pre>";
					// echo "<pre>".print_r($body,true)."</pre>";
				 	// echo "<pre>".print_r($files,true)."</pre>";
					// echo "<pre>".print_r($getMail['toInternal'],true)."</pre>";
					// echo "<pre>".print_r($getMail['sender'],true)."</pre>";
					// exit();
                    // send External
                    $sendEmailInternal = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['toInternal'],
						$getMail['ccInternal'],
						[],
						$files,
                        $getMail['sender'],
                        $getMail['sender']
						
					);
					
                    if($sendEmailInternal == true) {
                        echo "Message has been sent !\n";

                        // $this->automail->loggingEmail($logging,$getMail['toExternal'],1);
                        // $this->automail->loggingEmail($logging,$getMail['ccExternal'],2);
						
                        if ($sendEmailInternal == true) 
						{
                            $this->automail->loggingEmail($logging,$getMail['toInternal'],1);
							echo "Message has been sent Internal\n";
                        }

                        foreach ($tmp_file[$i] as $file) {
                            // sendSucess movefile
                            $this->automail->initFolder($rootTemp, 'temp');
                            $this->automail->moveFile($root, $rootTemp, 'temp/', $file);
                        }

                    }
				}
				else{
					exit('Failed' . PHP_EOL);
				}
            }
		} 
		catch (\Exception $e) 
		{
			echo $e->getMessage();
		}
	}
}
