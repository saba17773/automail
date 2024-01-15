<?php

namespace App\Weekly_Report_OTR_Group;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class OTR_GroupAPI
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
                }
            }

            return [
                'toExternal' => $listsToExternal,
                'ccExternal' => $listsCCExternal,
                'toInternal' => $listsToInternal,
                'sender' => $sendby
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSubject($datecheck)
    {
        return 'Weekly Report : OTR Group : ' . $datecheck;
    }
    public function getBody($datecheck)
    {
        $text = '';
        $text .= 'Dear All,<br><br>';
        $text .= 'Good Day to you.<br>';
        $text .= 'We enclosed weekly report [' . $datecheck . '] as attached file for your information.<br><br>';
        $text .= 'kindly review.<br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='3px' color='#86c8e9'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Pimonpun L. (Mink)</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Global Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b><a href='www.deestone.com'>www.deestone.com</a></b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Tel: (+66 2) 420 0038 Ext. 146</b></font> </td></tr><BR>";

        return $text;
    }

    public function getBodyinter()
    {
        $text = '';
        $text .= 'Dear Team,<br><br>';
        $text .= 'Please find attached the weekly report for your reference.<br><br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Nutch C.</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Office: +66 2420 0038 Ext#130</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Deestone Corporation Limited</b></font> </td></tr><BR>";

        return $text;
    }
    public function getSubjectTireco($datecheck)
    {

        return 'DS ' . $datecheck . ' Daily Email : Tireco weekly report';
    }
    public function getBodyTireco($typebody)
    {
        $text = '';

        if ($typebody == "External") {
            $text .= 'Dear Lee,<br><br>';
            $text .= 'Please find attached the weekly report for your reference.<br><br><br>';
        } else {
            $text .= 'Dear Team,<br><br>';
            $text .= 'Please find weekly report as attached file.<br><br><br>';
        }

        $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Nutch C.</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Office: +66 2420 0038 Ext#130</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Deestone Corporation Limited</b></font> </td></tr><BR>";

        return $text;
    }

    public function getSubjectSm($datecheck)
    {
        return 'Weekly Report : Blacksmith : ' . $datecheck;
    }
}
