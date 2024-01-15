<?php

namespace App\Api;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class ApiAPI {

	private $db_ax = null;
	private $db_live = null;
	private $automail = null;

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
  }

	public function getsubjectBooking() {
		try {
			return 'Booking API ';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isBookingReviseinternal($filename){
		if (preg_match("/-REV/i", $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function ischeckDrefinternal($filename){
		if (preg_match("/DRAFT(?:....)/i", $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function getMailCustomer($projectId) {
		try {

			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsSender = [];
			$listinternalDref = [];

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) {
				if ($q['EmailType']==1 && $q['EmailCategory']==17) {
					$listsTo[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==17){
					$listsCC[] = $q['Email'];
				}else if($q['EmailType']==1 && $q['EmailCategory']==17){
					$listsInternal[] = $q['Email'];
				}else if($q['EmailType']==4 && $q['EmailCategory']==17){
					$listsSender[] = $q['Email'];
				}else if($q['EmailType']==1 && $q['EmailCategory']==32){
					$listinternalDref[] = $q['Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'internal_dref' => $listinternalDref,
				'sender' => $listsSender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getMailCustomerDref($customerCode,$projectId) {
		try {
			$listsTo = [];
			

			$_to = Database::rows(
				$this->db_ax,
				"SELECT DSG_EMAIL
				FROM DSG_CUSTOMEREMAILLIST
				WHERE DSG_ACCOUNTNUM = ?
				AND DSG_SENDTYPE = 3 -- sender
				AND DSG_INACTIVE = 0
				UNION ALL
				SELECT Email AS DSG_EMAIL FROM [MORMONT\DEVELOP].[AUTOMAIL_LIVE].[dbo].[EmailLists] 
				WHERE ProjectID= ? AND Status=1 AND EmailCategory = 32",
				[
					$customerCode,$projectId
				]
			);

			
			if (count($_to) > 0) {
				foreach ($_to as $t) {
					$listsTo[] = $t['DSG_EMAIL'] ;
				}
			} else {
				return ['to' => ['worawut_s']];
			}

		

			return [
				'to' => $listsTo
			];
		} catch (\Exception $e) {
			return ['to' => [], 'cc' => []];
		}
	}

	public function getSOFromFileBooking($filename) {
		preg_match_all('/SO(?:..-......)/i', $filename, $data);

		if ( count($data[0]) === 0) {
			return [[]];
		}

		return $data[0];
	}

	public function isBookingTFileMatchAx($filename) {
		try {
			$_SO = "S.SALESID IN ($filename)";
			//	preg_match_all('/SO(?:..-......)/i', $filename, $data);
 			$SO = [];
			$PO = [];
			$PI = [];
			$CY = [];
			$RTN = [];
			$SalName = [];
			$Cusref = [];
			$Loadingdate = [];
			$HC = [];
			$Booking_detail = [];
			$Booknum = [];
			$AGENT = [];
			$CON1X20 = [];
			$CON1X40 = [];
			$CON1X40HC = [];
			$CON1X45HC = [];
			$shipline = [];
			$booknumber = [];
			$feeder = [];
			$voyfeeder = [];
			$vessel = [];
			$voyvessel = [];
			$portoflanding = [];
			$toportdesc = [];
			$etddate = [];
			$etadate = [];
			$closingdate = [];
			$cutoffdate = [];

			$query = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID ,
				S.QUOTATIONID,
				S.NoYesId_AddPI,
				S.DSG_CY,
				S.DSG_RTN,
				CT.NAME,
				S.CUSTOMERREF,
				S.DSG_EDDDate,
				DS.DSG_SUBHC,
				DS.DSG_BOOKINGDETAIL,
				--S.DSG_PRIMARYAGENTID,
				A.DSG_DESCRIPTION[DSG_PRIMARYAGENTID],
				S.DSG_BOOKINGNUMBER,
				CASE WHEN CONVERT(INT,S.DSG_Container1X20) = 0 THEN '' ELSE CONVERT(NVARCHAR,CONVERT(INT,S.DSG_Container1X20)) + 'X20' END AS [CON1X20],
				CASE WHEN CONVERT(INT,S.DSG_Container1X40) = 0 THEN '' ELSE CONVERT(NVARCHAR,CONVERT(INT,S.DSG_Container1X40)) + 'X40' END AS [CON1X40],
				CASE WHEN CONVERT(INT,S.DSG_Container1X40HC) = 0 THEN '' ELSE CONVERT(NVARCHAR,CONVERT(INT,S.DSG_Container1X40HC)) + 'X''40HC' END AS [CON1X40HC],
				CASE WHEN CONVERT(INT,S.DSG_Container1X45HC) = 0 THEN '' ELSE CONVERT(NVARCHAR,CONVERT(INT,S.DSG_Container1X45HC)) + 'X''45HC' END AS [CON1X45HC],
				S.DSG_ShippingLineDescription,
				S.DSG_Feeder,			
				S.DSG_VoyFeeder,		
				S.DSG_Vessel,			
				S.DSG_VoyVessel,		
				S.DSG_PortOfLoadingDesc,	
				S.DSG_ToPortDesc,		
				S.DSG_ETDDate,		
				S.DSG_ETADATE,			
				CONVERT(NVARCHAR,S.DSG_ClosingDate,103) + ' @ ' + CONVERT(CHAR(5), DATEADD(SECOND, S.DSG_ClosingTime, ''),114) AS DSG_ClosingDateTime, 
				CONVERT(NVARCHAR,S.DSG_CutoffVGMDate,103) + ' @ ' + CONVERT(CHAR(5), DATEADD(SECOND, S.DSG_CutoffVGMTime, ''),114) AS DSG_CutoffVGMDateTime,
				S.CUSTACCOUNT
				FROM SALESTABLE S
				LEFT JOIN DSG_SALESTABLE DS ON DS.SALESID = S.SALESID AND DS.DATAAREAID=S.DATAAREAID
				LEFT JOIN CustTable CT ON CT.ACCOUNTNUM = S.CUSTACCOUNT AND CT.DATAAREAID = S.DATAAREAID
				LEFT JOIN DSG_AgentTable A ON A.DSG_AGENTID =  S.DSG_PRIMARYAGENTID
				AND A.DATAAREAID = S.DATAAREAID
				WHERE $_SO
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-2720','C-2782')
				AND S.DATAAREAID='DSC'"
			);

			foreach($query as $q) {

				$SO[] = $q['SALESID'];
				$PO[] = $q['CUSTOMERREF'];
				$PI[] = $q['QUOTATIONID'];
				$CY[] = date('d/m/Y',strtotime($q['DSG_CY']));
				$RTN[] =date('d/m/Y',strtotime($q['DSG_RTN']));
				$SalName[] = $q['NAME'];
				$Loadingdate[] = date('d/m/Y',strtotime($q['DSG_EDDDate']));
				$HC[] = $q['DSG_SUBHC'];
				$Booking_detail[] = $q['DSG_BOOKINGDETAIL'];
				$Booknum[] = $q['DSG_BOOKINGNUMBER'];
				$AGENT[] = $q['DSG_PRIMARYAGENTID'];
				$CON1X20[] = $q['CON1X20'];
				$CON1X40[] = $q['CON1X40'];
				$CON1X40HC[] = $q['CON1X40HC'];
				$CON1X45HC[] = $q['CON1X45HC'];
				$shipline[] = $q['DSG_ShippingLineDescription'];
				$booknumber[] = $q['DSG_BOOKINGNUMBER'];
				$feeder[] = $q['DSG_Feeder'];
				$voyfeeder[] = $q['DSG_VoyFeeder'];
				$vessel[] = $q['DSG_Vessel'];
				$voyvessel[] = $q['DSG_VoyVessel'];
				$portoflanding[] = $q['DSG_PortOfLoadingDesc'];
				$toportdesc[] = $q['DSG_ToPortDesc'];
				$etddate[] = date('d/m/Y',strtotime($q['DSG_ETDDate']));
				$etadate[] = date('d/m/Y',strtotime($q['DSG_ETADATE']));
				$closingdate[] = $q['DSG_ClosingDateTime'];
				$cutoffdate[] = $q['DSG_CutoffVGMDateTime'];
				$custaccount[] = $q['CUSTACCOUNT'];
			}

			return [
				"SO" => $SO,
				"PO" => $PO,
				"PI" => $PI,
				"CY" => $CY,
				"RTN" => $RTN,
				"SalName" => $SalName,
				"Cusref" => $Cusref,
				"Loadingdate" => $Loadingdate,
				"HC" => $HC,
				"Booking_detail" => $Booking_detail,
				"Numbook" => $Booknum,
				"AGENT" => $AGENT,
				"CON1X20" => $CON1X20,
				"CON1X40" => $CON1X40,
				"CON1X40HC" => $CON1X40HC,
				"CON1X45HC" => $CON1X45HC,
				"shipline" => $shipline,
				"booknumber" => $booknumber,
				"feeder" => $feeder,
				"voyfeeder" => $voyfeeder,
				"vessel" => $vessel,
				"voyvessel" => $voyvessel,
				"portoflanding" => $portoflanding,
				"toportdesc" => $toportdesc,
				"etddate" => $etddate,
				"etadate" => $etadate,
				"closingdate" => $closingdate,
				"cutoffdate" => $cutoffdate,
				"custaccount" => $custaccount
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	public function getBookingBody_v3($txtSo, $txtPo, $txtPI, $txtLd, $txtCy, $txtRtn, $txtHc, $txtBk, $AGENT, 
									  $volume, $shipline, $booknumber, $feeder, $voyfeeder, $vessel, $voyvessel, 
									  $portoflanding, $toportdesc, $etddate, $etadate, $closingdate, $cutoffdate,
									  $soreftext) {
			$text = '';
			$txtAgent1 = '';
			$txtAgent = '';
			$SALESNAME = 'AMERICAN PACIFIC INDUSTRIES, INC.';;

			foreach ($AGENT as $value) {
				if(count($AGENT) >1){
					$txtAgent1 .= $value.',' ;
					$txtAgent = substr($txtAgent1,0,-1);
				}

				else {
					$txtAgent .= $value;
				}
			}
			
			if($etddate == "01/01/1900"){
				$etddate = "";
			}

			if($etadate == "01/01/1900"){
				$etadate = "";
			}

			$text .= 'Dear EL, <br><br>';
			$text .= '<b>Customer name : </b>' . $SALESNAME. '<br>';
			$text .= '<b>P/I : </b>' . $txtPI . '<br>';
			// $text .= '<b>SO : </b>' . $txtSo . '<br>';
			$text .= $soreftext;
			$text .= '<b>PO : </b>' .$txtPo. '<br>';
			$text .= '<b>Loading date : </b>'. $txtLd;
			$text .= '<b> CY : </b>'. $txtCy;
			$text .= '<b> RTN : </b>'. $txtRtn.' <br>';
			$text .= '<b>VOLUME : </b>' . $volume. '<br>';
			$text .= '<b>Shipping Line : </b>' . $shipline. '<br>';
			$text .= '<b>Booking no : </b>' . $booknumber. '<br>';
			$text .= '<b>Feeder vessel  : </b>' . $feeder;
			$text .= '<b> V. : </b>'. $voyfeeder . '<br>';
			$text .= '<b>Mother vessel : </b>' . $vessel;
			$text .= '<b> V. : </b>'. $voyvessel . '<br>';
			$text .= '<b>Port of Loading : </b>' . $portoflanding. '<br>';
			$text .= '<b>Destination Port : </b>' . $toportdesc. '<br>';
			$text .= '<b>ETD : </b>' . $etddate. '<br>';
			$text .= '<b>ETA : </b>' . $etadate. '<br>';
			$text .= '<b>Closing date & Time : </b>' . $closingdate. '<br>';
			$text .= '<b>VGM cut off date & Time : </b>' . $cutoffdate. '<br><br>';
			$text .= '<b>Agent : </b>'. $txtAgent.'<br><br>';
			$text .= '<b>Sub\'HC : </b>'.$txtHc.' <br>';
			$text .= '<b>Booking Detail : </b><br>';

			$text .= '<ul>';

			if($txtBk !="") {
				$text .= '<li>'.$txtBk.'</li>';
			}
			else{
				$text .= '-';
			}
			$text .= '</ul><br>';

			return $text;
 	}

	public function getBookingSubject_internalAPI($SO, $name,$PO, $type, $Numbook ) {
		$text = '';
		//$name = 'AMERICAN PACIFIC INDUSTRIES, INC.';
		$numm = '';
		$numm1 = '';
		$NumbookDuplicate = array_unique( $Numbook ); // $NumbookDuplicate data array
		$arr_Numbook = array_filter( $NumbookDuplicate ); //cut array is null
		$nameTopic = '';
		foreach ($arr_Numbook as $value) {
			if(count($arr_Numbook) >1){
				$numm1 .= $value.',' ;
				$numm = substr($numm1,0,-1);
			}else {
							$numm .= $value;
						}
		}
		foreach ($name as $value) {
			$nameTopic .= $value;
		}
		if($type == 'New'){
			$text .= 'New Booking : ' . $nameTopic . ' / ' .
			$PO . ' / ' .$SO. ' / ' .$numm;
		}else{
			$text .= 'Revised Booking : ' . $nameTopic . ' / ' .
			$PO . ' / ' .$SO. ' / ' .$numm;
		}
		return $text;
	}

	public function getEmailList($projectId) {
		try {

			$listsToEx = [];
			$listsCCEx = [];
			$listsToIn = [];
			$listsToFailed = [];
			$sender = "";

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) 
			{
				if ($q['EmailType']== 1 && $q['EmailCategory'] == 16) 
				{
					$listsToEx[] = $q['Email'];
				}
				else if ($q['EmailType']== 2 && $q['EmailCategory'] == 16) 
				{
					$listsCCEx[] = $q['Email'];
				}
				else if ($q['EmailType']== 1 && $q['EmailCategory'] == 17) 
				{
					$listsToIn[] = $q['Email'];
				}
				else if ($q['EmailType']== 5 && $q['EmailCategory'] == 17) 
				{
					$listsToFailed[] = $q['Email'];
				}
				else if ($q['EmailType']== 4 && $q['EmailCategory'] == 17) 
				{
					$sender = $q['Email'];
				}
			}

			return [
				'toEX' => $listsToEx,
				'ccEX' => $listsCCEx,
				'toIN' => $listsToIn,
				'toFailed' => $listsToFailed,
				'sender' => $sender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isAPIFileMatchAx($pi,$inv) {
		try {

			$isExit = Database::rows(
				$this->db_ax,
				"SELECT TOP 1 C.VOUCHER_NO,C.SERIES,C.SALESID,S.QUOTATIONID,C.*
				FROM CUSTPACKINGSLIPJOUR C JOIN
				SALESTABLE S ON C.SALESID = S.SALESID
				WHERE C.VOUCHER_NO = ?
				AND C.DATAAREAID = 'DSC'
				--AND C.ORDERACCOUNT = 'C-2720'
				AND C.ORDERACCOUNT IN ('C-2720','C-2782')
				AND S.DATAAREAID = 'DSC'
				ORDER BY C.SERIES DESC ",
				[ $inv ]
			);
            
			if (count($isExit) <= 0) {
				return false;
			}

			if($isExit[0]['QUOTATIONID'] != $pi ){
				return false;
			}

			return true;
			
		} catch (\Exception $e) {
			return false;
		}	

    }
    
    public function Size($path) {
        $bytes = sprintf('%u', filesize($path));
        
        if ($bytes > 0){
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');

            if (array_key_exists($unit, $units) === true){
                return sprintf('%.2f %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        
        return $bytes;
	}

	public function getSubject_CDS($file = [], $type) {
		try 
		{	
			if ($type=="CDS") 
			{
				preg_match('/QA(a?.........)/i', $file, $qa);
				preg_match('/INV(.+\d)/', $file, $inv);
				preg_match('/PO#(.+\d)\b/', $file, $po1);
				preg_match('/PO#(.+)-/', $po1[0], $output_po);

				$po = substr($output_po[0], 0, -1);

				$text = 'TBC / Century Booking / ' . $qa[0] . '-' . $po . '-' . $inv[0] . '-VGM, INV,PL&INSP';
			}
			else if($type=="ERROR")
			{
				$text = 'ERROR TBC / Century Booking';
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
    }

	public function getBody_CDS($file = []) {
		try 
		{
			preg_match('/QA(a?.........)/i', $file, $qa);
			preg_match('/INV(.+\d)/', $file, $inv);
			preg_match('/PO#(.+\d)\b/', $file, $po1);
			preg_match('/PO#(.+)-/', $po1[0], $output_po);

			$po = substr($output_po[0], 0, -1);


			$text = '';
			$text .= '<b>Dear Sir / Madam,</b>'.'<br><br>';
			$text .= '<b>Please see VGM, INV., PL.& INSP for ' . $qa[0] . '-' . $po . '-' . $inv[0] . '  as attached file.</b>' . '<br><br>';
			$text .= '<table>';
			$text .= '<tr>';
			$text .= '<td><b>Customer Name</b></td>';
			$text .= '<td><b>:</b> AMERICAN PACIFIC INDUSTRIES, INC.</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>PI ID</b></td>';
			$text .= '<td><b>:</b> ' . $qa[0] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>PO</b></td>';
			$text .= '<td><b>:</b> ' . $output_po[1] . '</td>';
			$text .= '</tr>';
			$getDataCDS = self::getDataCDS($inv[1]);
			$text .= '<tr>';
			$text .= '<td><b>Invoice No</b></td>';
			$text .= '<td><b>:</b> DSC/' . $getDataCDS[0]['SERIES'] . '/' . $getDataCDS[0]['VOUCHER_NO'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>SO ID</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['SALESID'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETD</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_ETD'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETA</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_ETA'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Destination port</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_DESTINATION'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Agent</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_PRIMARYAGENTID'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Shipping Line</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_SHIPPINGLINEDESCRIPTION'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Container No.</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_CONTAINERNO'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Seal No.</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_SEALNO'].'</td>';
			$text .= '</tr>';
			$text .= '</table>';
			$text .= '<br><br><br>'.'<b>Best Regards,</b>';
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDataCDS($inv) {
		try {
            
			return Database::rows(
				$this->db_ax,

				"SELECT TOP 1 S.QUOTATIONID
				,C.SALESID
				,C.SERIES
				,C.VOUCHER_NO
				,CONVERT(VARCHAR,C.DSG_ETD,106) AS DSG_ETD
				,CONVERT(VARCHAR,C.DSG_ETA,106) AS DSG_ETA
				,C.DSG_DESTINATIONCODEDESCRI50019 AS DSG_DESTINATION
				,S.DSG_PRIMARYAGENTID
				,S.DSG_SHIPPINGLINEDESCRIPTION
				,C.DSG_CONTAINERNO
				,C.DSG_SEALNO
				FROM CUSTPACKINGSLIPJOUR C JOIN
				SALESTABLE S ON C.SALESID = S.SALESID
				WHERE C.VOUCHER_NO = ?
				AND C.DATAAREAID = 'DSC'
				AND C.ORDERACCOUNT IN ('C-2720','C-2782')
				AND S.DATAAREAID = 'DSC'
				ORDER BY C.SERIES DESC",
				[
					$inv
				]
			);
		} catch (\Exception $th) {
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

	public function getBodyCDS_Failed($file = [], $remark) {
		try {

			// $remark = 'Fail sending due to no date of sending.';
			$text = '';
			$text .= '<b>Dear Team,</b>'.'<br><br>';
			
			$text .= '<b>รายชื่อไฟล์ที่ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ</b><br><br>';

			$text .= '<b>สาเหตุ : ' . $remark . '</b><br>';

			$text .= '<ul>';

			foreach($file as $f) {
				$text .= '<li>' . $f . '</li>';
			}
			$text .= '</ul><br>';

			$text .= '';

			return $text;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSOSameBookingNumber($bookingnum, $customer) {
		$res = Database::rows(
			$this->db_ax,
				"SELECT CASE WHEN SALESIDREF IS NULL THEN SALESID ELSE SALESID + ',' + SALESIDREF END SALESID
				FROM
				(
					SELECT S.SALESID
						,SALESIDREF = STUFF((
							SELECT ',' + R2.DSG_SALESIDREF
							FROM DSG_SalesBookingRef R2
							WHERE S.SALESID = R2.DSG_SALESID
							GROUP BY R2.DSG_SALESIDREF
							FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 1, ''
							)
					FROM SALESTABLE S 
					WHERE S.DSG_BOOKINGNUMBER = ? 
					AND CUSTACCOUNT = ?
					AND S.QUOTATIONID is not null
					AND S.QUOTATIONID <> ''
					AND S.DATAAREAID = 'dsc'
				)T
				GROUP BY SALESID,SALESIDREF",
			[
				$bookingnum,
				$customer
			]
		);

		$so = [];
		
		foreach ($res as $v) {
			$so[] = $v;
		}
		return $so;

	}

	public function getSORef($bookingnum, $customer, $so, $soref1) {

		$soref = [];

		$res = Database::rows(
			$this->db_ax,
				"SELECT COUNT(S.SALESID) AS CHECKSALESID 
						FROM SALESTABLE S JOIN
						DSG_SalesBookingRef R ON S.SALESID = R.DSG_SALESID
						WHERE S.DSG_BOOKINGNUMBER = ?
						AND S.CUSTACCOUNT = ?
						AND S.SALESID = ?
						AND R.DSG_SALESIDREF = ?
						AND S.QUOTATIONID is not null
						AND S.QUOTATIONID <> ''
						AND S.DATAAREAID = 'dsc'",
			[
				$bookingnum,
				$customer,
				$so,
				$soref1
			]
		);
		
		foreach ($res as $v) {
			$soref[] = $v;
		}
		
		return $soref;
	}

}
