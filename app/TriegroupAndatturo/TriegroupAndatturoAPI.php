<?php

namespace App\TriegroupAndatturo;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class TriegroupAndatturoAPI
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

    public function getSubject($datecheck)
    {
        return 'Deestone : Mile stones Daily Report :' . $datecheck;
    }

    public function getSubject_v2($datecheck)
    {
        return 'Atturo Tire : Weekly report  :[' . $datecheck . ']';
    }

    

    public function getBody()
    {

        $text = '';
        $text .= 'Dear Steven, Rita,<br><br>';
        $text .= 'Good Day to you.<br>';
        $text .= 'Please find daily report as attached file for your reference.<br><br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Krissana B. (Beer)</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Commercial Control</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b><a href='www.deestone.com'>www.deestone.com</a></b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Tel: (+66 2) 420 0038 Ext. 146</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>mobile phone: 099-001-4834</b></font> </td></tr><BR>";

        return $text;
    }

    public function getBody_v2($datecheck)
    {
        $text = '';
        $text .= 'Dear Michael,<br><br>';
        $text .= 'Good Day to you.<br>';
        $text .= 'We just enclosed weekly [' . $datecheck . '] report as attached file for your information, kindly review.<br><br>';
        $text .= 'Thank you.<br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='3px' color='#86c8e9'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Pimonpun L. (Mink)</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Global Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b><a href='www.deestone.com'>www.deestone.com</a></b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#86c8e9'><b>Tel: (+66 2) 420 0038 Ext. 146</b></font> </td></tr><BR>";

        return $text;
    }

    public function getSubject_internal($datecheck)
    {
        return 'Update Tire group and Atturo Report : [' . $datecheck . '] ';
    }

    //internal

    public function getBody_internal()
    {

        
        $text = '';
        $text .= 'Dear P\'Dew,<br><br>';
        $text .= 'please find update Tire group and Atturo report for your referance.<br><br><br>';

        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Krissana B. (Beer)</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Commercial Control</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b><a href='www.deestone.com'>www.deestone.com</a></b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Tel: (+66 2) 420 0038 Ext. 146</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>mobile phone: 099-001-4834</b></font> </td></tr><BR>";

        return $text;
    }
}
