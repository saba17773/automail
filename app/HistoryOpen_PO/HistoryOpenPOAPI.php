<?php

namespace App\HistoryOpen_PO;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class HistoryOpenPOAPI
{

    private $db_live = null;
    private $automail = null;

    public function __construct()
    {
        $this->db_live = Database::connect();
        $this->automail = new Automail;
    }

    public function getMail($projectId)
    {
        try {
            $listsToExternal = [];
            $listsCCExternal = [];
            $listsToInternal = [];
            $listsCCInternal = [];
            $sendby = "";

            $query = Database::rows(
                $this->db_live,
                "SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",
                [$projectId, 1]
            );

            foreach ($query as $q) {
                if ($q['EmailType'] == 1 && $q['EmailCategory'] == 16) {
                    $listsToExternal[] = $q['Email'];
                } else if ($q['EmailType'] == 1 && $q['EmailCategory'] == 17) {
                    $listsToInternal[] = $q['Email'];
                } else if ($q['EmailType'] == 2 && $q['EmailCategory'] == 16) {
                    $listsCCExternal[] = $q['Email'];
                } else if ($q['EmailType'] == 4 && $q['EmailCategory'] == 17) {
                    $sendby = $q['Email'];
                } else if ($q['EmailType'] == 2 && $q['EmailCategory'] == 17) {
                    $listsCCInternal[] = $q['Email'];
                }
                
            }

            return [
                'toExternal' => $listsToExternal,
                'ccExternal' => $listsCCExternal,
                'toInternal' => $listsToInternal,
                'ccInternal' => $listsCCInternal,
                'sender' => $sendby
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getMailFailed($projectId) {
		try {
			
			$listsFailed = [];
				
			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) {
				if ($q['EmailType']==5 && $q['EmailCategory']==17) {
					$listsFailed[] = $q['Email'];
				}
			}

			return [
				'failed' => $listsFailed
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

    public function pathTofile($files = [], $root) {
		try {
			$file = [];
            for ($x=0; $x < count($files); $x++) { 
                $file[] = $root.$files[$x];
            }
            return $file;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
    
    public function getSubject($datecheck)
    {
        return 'History open purchase order line (DSG 2022) Item Group (RM) :' . $datecheck;
    }

    public function getBody()
    {

        $text = '';
        $text .= 'Dear All,<br><br>';
        // $text .= 'Good Day to you.<br>';
        $text .= 'History open purchase order line (DSG 2022) Item Group (RM) <br>';

        // $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Krissana B. (Beer)</b></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Commercial Control</b></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b><a href='www.deestone.com'>www.deestone.com</a></b></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Tel: (+66 2) 420 0038 Ext. 146</b></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>mobile phone: 099-001-4834</b></font> </td></tr><BR>";

        return $text;
    }
}
