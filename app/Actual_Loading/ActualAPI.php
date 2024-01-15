<?php

namespace App\Actual_Loading;

use App\Common\Database;
use App\Common\CSRF;
use DateTime;


class ActualAPI
{

    private $db_ax = null;
    private $db_live = null;
    private $csrf = null;

    public function __construct()
    {
        $this->db_ax = Database::connect("ax");
        $this->db_live = Database::connect();
        $this->csrf = new CSRF;
    }


    public function getTrucking()
    {
        try {

            $date = date("Y-m-d");

            $logapprove = Database::rows(
                $this->db_ax,
                "SELECT 
                CONVERT(varchar,T2.DATE_,103) [DATE_]
                ,T2.TIME_
                ,T2.DSG_CREATEDBY
                ,T2.DSG_PrimaryPlant
                ,T2.DSGBandID
                ,T2.DSG_ITEMID
                ,T2.ItemName
                ,T2.DSG_ActualLoad20
                ,T2.DSG_ActualLoad40
                ,T2.DSG_ActualLoad40HC
                ,T2.DSG_ActualLoad45HC
                ,T2.DSG_ActualRemark
                ,U.NAME
                ,B.DSGBRANDNAME
            FROM(
            SELECT
                    *,
                    CONVERT(TIME,T.CREATEDDATETIME) [TIME_],
                    CONVERT(DATE,T.CREATEDDATETIME) [DATE_],
                    ROW_NUMBER() OVER (PARTITION BY T.DSG_ITEMID ORDER BY T.CREATEDDATETIME DESC) AS ROW#
                        FROM(
                    SELECT 
                    dateadd(second ,RIM.DSG_CREATEDTIME, RIM.DSG_CREATEDDATE) as CREATEDDATETIME,
                    convert(nvarchar,convert(date,GETDATE()))+' 07:59:59' [T_DATE],
                    convert(nvarchar,convert(date,dateadd(day, -1, GETDATE())))+' 08:00:01' [Y_DATE]
                    ,RIM.* 
                    --,RIM.DSG_ITEMID
                    ,IT.DSG_PrimaryPlant
                    ,IT.DSGBandID
                    ,IT.ItemName
                    ,IT.DSG_ActualLoad20
                    ,IT.DSG_ActualLoad40
                    ,IT.DSG_ActualLoad40HC
                    ,IT.DSG_ActualLoad45HC
                    ,IT.DSG_ActualRemark
                    FROM DSG_ReferenceItem RIM
                    LEFT JOIN InventTable IT ON IT.ITEMID = RIM.DSG_ITEMID --AND IT.DATAAREAID = RIM.DATAAREAID
                    )T WHERE T.CREATEDDATETIME BETWEEN T.Y_DATE  AND T.T_DATE
            
            )T2  
            LEFT JOIN UserInfo U ON U.ID = T2.DSG_CREATEDBY
            LEFT JOIN DSGBRAND B ON B.DSGBRANDID = T2.DSGBandID
             WHERE T2.ROW# = 1
            
            GROUP BY 
                 T2.DATE_
                ,T2.TIME_
                ,T2.DSG_CREATEDBY
                ,T2.DSG_PrimaryPlant
                ,T2.DSGBandID
                ,T2.DSG_ITEMID
                ,T2.ItemName
                ,T2.DSG_ActualLoad20
                ,T2.DSG_ActualLoad40
                ,T2.DSG_ActualLoad40HC
                ,T2.DSG_ActualLoad45HC
                ,T2.DSG_ActualRemark
                ,U.NAME
                ,B.DSGBRANDNAME
                "

            );

            if ($logapprove) {
                return $logapprove;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getEmailSend($projectID)
    {
        $query = Database::rows(
            $this->db_live,
            "  SELECT 
            Email, 
            EmailType, 
            EmailCategory
            FROM EmailLists 
            WHERE ProjectID = ?
            AND [Status] = 1 
			AND EmailType = 4",
            [$projectID,]
        );

        $sendby = $query[0]['Email'];

        return ["sendby" => $sendby];
    }
    public function getEmail($projectID)
    {

        $sql = "SELECT 
        Email, 
        EmailType, 
        EmailCategory
        FROM EmailLists 
        WHERE ProjectID = $projectID 
        AND [Status] = 1";

        $rows = Database::rows(
            $this->db_live,
            $sql
        );

        $to = [];
        $cc = [];
        $tocomplete = [];

        foreach ($rows as $row) {
            if ($row["EmailType"] === 1) {
                $to[] = $row["Email"];
            } else if ($row["EmailType"] === 2) {
                $cc[] = $row["Email"];
            } else if ($row["EmailType"] === 4) {
                $tocomplete[] = $row["Email"];
            }
        }

        return ["to" => $to, "cc" => $cc, "tocomplete" => $tocomplete];
    }

    public function getSubject()
    {
        $date = date("d/m/Y");
        return  " AutoMail: Item Update Actual load";
    }

    public function deletedata()
    {
        $create = Database::query(
			$this->db_ax,
			"DELETE
            FROM DSG_ReferenceItem
           WHERE dateadd(second ,DSG_CREATEDTIME,DSG_CREATEDDATE) BETWEEN 
           convert(nvarchar,convert(date,dateadd(day, -1, GETDATE())))+' 08:00:01' 
           AND
           convert(nvarchar,convert(date,GETDATE()))+' 07:59:59' "
		);

    }

    

    public function getBody_v2(

        $getApprove
    ) {
        $chooseuse = "";
        $dateToppic = date("d/m/Y");



        $text = "";
        // $text .= "เรียน Shipping Team";
        // $text .= "<BR><BR>";
        // $text .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; เพื่อให้มั่นใจว่าบริษัทหัวลากได้ทำการคืนตู้ตามกำหนดเวลาที่บริษัทกำหนด  ";
        // $text .= "<BR><BR>";
        // $text .= "ขอให้ตรวจสอบสถานะใบขนสินค้าขาออกว่าขึ้นสถานะ 03 หรือยัง และขอให้ยืนยันกลับทางอีเมล์ค่ะ";
        $text .= "<BR><BR>";
        $text .= "<table  width='130%' border = '1'>
                    <tr bgcolor='#99CCFF'>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>วันที่ Update</font></font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>เวลา</font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>ผู้บันทึกข้อมูล</font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>Primary Plant(ITEM)</font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>Brand</font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>ItemID</font></b></th>
                    <th style='text-align:center' rowspan='2'><font size='2px'><b>Item Name</font></b></th>
                    <th style='text-align:center'  colspan='5'><font size='2px'><b>Actual Loading</font></b></th>
                  
                       
                    </tr>
                    <tr>
              
               
                <th>1X20'FCL</th>
                <th >1X40'FCL</th>
                <th >1X40'HC</th>
                <th >1X45'HC</th>
                <th >Remark</th>
            </tr>
                    ";
        foreach ($getApprove as $log) {

            // $str = $log["DSG_ClosingTime"];
            // $Timset = explode(":", $str);
            // $TimeClosing = $Timset[1] . "." . $Timset[2];

            $text .= "<tr>
                         <td ><font size='2px'>" . $log["DATE_"] . "</font></td>
                         <td><font size='2px'>" . $log["TIME_"] . "</font></td>
                         <td><font size='2px'>" . $log["NAME"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_PrimaryPlant"] . "</font></td>
                         <td><font size='2px'>" . $log["DSGBRANDNAME"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ITEMID"] . "</font></td>
                         <td><font size='2px'>" . $log["ItemName"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ActualLoad20"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ActualLoad40"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ActualLoad40HC"] . "</font></td>
                        <td><font size='2px'>" . $log["DSG_ActualLoad45HC"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ActualRemark"] . "</font></td>
                         
                         
                         </tr>";
        }

        $text .=  "</table>";

        // $signature = self::getsignatureTrucking($customer);

        // $text .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr><BR><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Global Customer Services Manager</font> </td></tr><BR><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: (+66 2) 420 0038 Ext. 506</font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Fax: (+66 2) 420 5680</font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Mob: (+66 8) 1378 4384</font> </td></tr>";



        return $text;
    }
}
