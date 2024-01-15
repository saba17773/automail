<?php

namespace App\doc_please_comfirm;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class DoconfirmAPI
{

	private $db_ax = null;
	private $db_live = null;
	private $automail = null;

	public function __construct()
	{
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function getsubjectBooking()
	{
		try {
			return 'Booking API ';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isBookingReviseinternal($filename)
	{
		if (preg_match("/-REV/i", $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function getMailCustomer($projectId, $cuscode)
	{
		try {

			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsSender = [];

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=? AND CustomerCode = ? ",
				[$projectId, 1, $cuscode]
			);

			foreach ($query as $q) {
				if ($q['EmailType'] == 1 && $q['EmailCategory'] == 16) {
					$listsTo[] = $q['Email'];
				} else if ($q['EmailType'] == 2 && $q['EmailCategory'] == 16) {
					$listsCC[] = $q['Email'];
				} else if ($q['EmailType'] == 1 && $q['EmailCategory'] == 17) {
					$listsInternal[] = $q['Email'];
				} else if ($q['EmailType'] == 4 && $q['EmailCategory'] == 17) {
					$listsSender[] = $q['Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'Internal' => $listsSender,
				'sender' => $listsSender
			];
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSOFromFileBooking($filename)
	{
		preg_match_all('/SO(?:..-......)/i', $filename, $data);

		if (count($data[0]) === 0) {
			return [[]];
		}

		return $data[0];
	}

	public function isBookingTFileMatchAx($filename)
	{
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
				S.DSG_PRIMARYAGENTID,
				S.DSG_BOOKINGNUMBER
				FROM SALESTABLE S
				LEFT JOIN DSG_SALESTABLE DS ON DS.SALESID = S.SALESID AND DS.DATAAREAID=S.DATAAREAID
				LEFT JOIN CustTable CT ON CT.ACCOUNTNUM = S.CUSTACCOUNT AND CT.DATAAREAID = S.DATAAREAID
				WHERE $_SO
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-2720')
				AND S.DATAAREAID='DSC'"
			);

			foreach ($query as $q) {

				$SO[] = $q['SALESID'];
				$PO[] = $q['CUSTOMERREF'];
				$PI[] = $q['QUOTATIONID'];
				$CY[] = date('d/m/Y', strtotime($q['DSG_CY']));
				$RTN[] = date('d/m/Y', strtotime($q['DSG_RTN']));
				$SalName[] = $q['NAME'];
				$Loadingdate[] = date('d/m/Y', strtotime($q['DSG_EDDDate']));
				$HC[] = $q['DSG_SUBHC'];
				$Booking_detail[] = $q['DSG_BOOKINGDETAIL'];
				$Booknum[] = $q['DSG_BOOKINGNUMBER'];
				$AGENT[] = $q['DSG_PRIMARYAGENTID'];
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
				"AGENT" => $AGENT
			];
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBookingBody_v3($txtSo, $txtPo, $txtPI, $txtLd, $txtCy, $txtRtn, $txtHc, $txtBk, $AGENT)
	{
		$text = '';
		$txtAgent1 = '';
		$txtAgent = '';
		$SALESNAME = 'AMERICAN PACIFIC INDUSTRIES, INC.';;

		//	preg_match_all('/SO(?:..-......)/i', $so, $output_array);
		//
		// if (count($output_array[0])>1) {
		// 	for ($i=0; $i < count($output_array[0]); $i++) {
		// 		$dataso .= $output_array[0][0].",";
		// 		$textSO  = substr($dataso, 0, -1);
		// 	}
		// }else{
		// 	$textSO= $output_array[0][0];
		// }
		//  ของพี่เจด้ของพี่เจด้านบน
		foreach ($AGENT as $value) {
			if (count($AGENT) > 1) {
				$txtAgent1 .= $value . ',';
				$txtAgent = substr($txtAgent1, 0, -1);
			} else {
				$txtAgent .= $value;
			}
		}
		$text .= 'Dear EL, <br><br>';
		$text .= '<b>Customer name : </b>' . $SALESNAME . '<br>';
		$text .= '<b>P/I : </b>' . $txtPI . '<br>';
		$text .= '<b>SO : </b>' . $txtSo . '<br>';
		$text .= '<b>PO : </b>' . $txtPo . '<br>';
		$text .= '<b>Loading date : </b>' . $txtLd;
		$text .= '<b> CY : </b>' . $txtCy;
		$text .= '<b> RTN : </b>' . $txtRtn . ' <br>';
		$text .= '<b>Agent : </b>' . $txtAgent . '<br><br>';
		$text .= '<b>Sub\'HC : </b>' . $txtHc . ' <br>';
		$text .= '<b>Booking Detail : </b><br>';

		$text .= '<ul>';

		if ($txtBk != "") {
			$text .= '<li>' . $txtBk . '</li>';
		} else {
			$text .= '-';
		}
		$text .= '</ul><br>';

		return $text;
	}

	public function getBookingSubject_internalAPI($SO, $name, $PO, $type, $Numbook)
	{
		$text = '';
		//$name = 'AMERICAN PACIFIC INDUSTRIES, INC.';
		$numm = '';
		$numm1 = '';
		$NumbookDuplicate = array_unique($Numbook); // $NumbookDuplicate data array
		$arr_Numbook = array_filter($NumbookDuplicate); //cut array is null
		$nameTopic = '';
		foreach ($arr_Numbook as $value) {
			if (count($arr_Numbook) > 1) {
				$numm1 .= $value . ',';
				$numm = substr($numm1, 0, -1);
			} else {
				$numm .= $value;
			}
		}
		foreach ($name as $value) {
			$nameTopic .= $value;
		}
		if ($type == 'New') {
			$text .= 'New Booking : ' . $nameTopic . ' / ' .
				$PO . ' / ' . $SO . ' / ' . $numm;
		} else {
			$text .= 'Revised Booking : ' . $nameTopic . ' / ' .
				$PO . ' / ' . $SO . ' / ' . $numm;
		}
		return $text;
	}

	public function getEmailList($projectId)
	{
		try {

			$listsToEx = [];
			$listsCCEx = [];
			$listsToIn = [];
			$listsToFailed = [];
			$sender = "";

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",
				[$projectId, 1]
			);

			foreach ($query as $q) {
				if ($q['EmailType'] == 1 && $q['EmailCategory'] == 16) {
					$listsToEx[] = $q['Email'];
				} else if ($q['EmailType'] == 2 && $q['EmailCategory'] == 16) {
					$listsCCEx[] = $q['Email'];
				} else if ($q['EmailType'] == 1 && $q['EmailCategory'] == 17) {
					$listsToIn[] = $q['Email'];
				} else if ($q['EmailType'] == 5 && $q['EmailCategory'] == 17) {
					$listsToFailed[] = $q['Email'];
				} else if ($q['EmailType'] == 4 && $q['EmailCategory'] == 17) {
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

	public function isAPIFileMatchAx($pi, $inv)
	{
		try {

			$isExit = Database::rows(
				$this->db_ax,
				"SELECT TOP 1 C.VOUCHER_NO,C.SERIES,C.SALESID,S.QUOTATIONID,C.*
				FROM CUSTPACKINGSLIPJOUR C JOIN
				SALESTABLE S ON C.SALESID = S.SALESID
				WHERE C.VOUCHER_NO = ?
				AND C.DATAAREAID = 'DSC'
				AND C.ORDERACCOUNT = 'C-2720'
				AND S.DATAAREAID = 'DSC'
				ORDER BY C.SERIES DESC ",
				[$inv]
			);

			if (count($isExit) <= 0) {
				return false;
			}

			if ($isExit[0]['QUOTATIONID'] != $pi) {
				return false;
			}

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function Size($path)
	{
		$bytes = sprintf('%u', filesize($path));

		if ($bytes > 0) {
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB');

			if (array_key_exists($unit, $units) === true) {
				return sprintf('%.2f %s', $bytes / pow(1024, $unit), $units[$unit]);
			}
		}

		return $bytes;
	}

	public function getSubject_CDS($file = [], $type)
	{
		try {
			if ($type == "CDS") {
				preg_match('/QA(a?.........)/i', $file, $qa);
				preg_match('/INV(.+\d)/', $file, $inv);
				preg_match('/PO#(.+\d)\b/', $file, $po1);
				preg_match('/PO#(.+)-/', $po1[0], $output_po);

				$po = substr($output_po[0], 0, -1);

				$text = 'TBC / Century Booking / ' . $qa[0] . '-' . $po . '-' . $inv[0] . '-VGM, INV,PL&INSP';
			} else if ($type == "ERROR") {
				$text = 'ERROR TBC / Century Booking';
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBody_CDS($file = [])
	{
		try {
			preg_match('/QA(a?.........)/i', $file, $qa);
			preg_match('/INV(.+\d)/', $file, $inv);
			preg_match('/PO#(.+\d)\b/', $file, $po1);
			preg_match('/PO#(.+)-/', $po1[0], $output_po);

			$po = substr($output_po[0], 0, -1);


			$text = '';
			$text .= '<b>Dear Sir / Madam,</b>' . '<br><br>';
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
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['SALESID'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETD</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_ETD'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETA</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_ETA'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Destination port</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_DESTINATION'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Agent</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_PRIMARYAGENTID'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Shipping Line</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_SHIPPINGLINEDESCRIPTION'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Container No.</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_CONTAINERNO'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Seal No.</b></td>';
			$text .= '<td><b>:</b> ' . $getDataCDS[0]['DSG_SEALNO'] . '</td>';
			$text .= '</tr>';
			$text .= '</table>';
			$text .= '<br><br><br>' . '<b>Best Regards,</b>';
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDataCDS($inv)
	{
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
				AND C.ORDERACCOUNT = 'C-2720'
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

	public function pathTofile($files = [], $root)
	{
		try {
			$file = [];
			for ($x = 0; $x < count($files); $x++) {
				$file[] = $root . $files[$x];
			}
			return $file;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBodyCDS_Failed($file = [], $remark)
	{
		try {

			// $remark = 'Fail sending due to no date of sending.';
			$text = '';
			$text .= '<b>Dear Team,</b>' . '<br><br>';

			$text .= '<b>รายชื่อไฟล์ที่ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ</b><br><br>';

			$text .= '<b>สาเหตุ : ' . $remark . '</b><br>';

			$text .= '<ul>';

			foreach ($file as $f) {
				$text .= '<li>' . $f . '</li>';
			}
			$text .= '</ul><br>';

			$text .= '';

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}


	//  automail v2

	public function getCustomerCode($file)
	{
		preg_match('/C.*?(\\d+)/i', $file, $data);
		return substr_replace($data[0], '-', 1, 0);
	}

	public function getCustomerQuantationV2($file)
	{
		preg_match_all('/QA(?:..-......)/i', $file, $data);

		if (count($data) === 0) {
			return '';
		}

		return $data[0];
	}

	public function convertToInSql($arr)
	{
		$txt = '';
		if (is_array($arr)) {
			foreach ($arr as $v) {
				$txt .= '\'' . $v . '\',';
			}
			return trim($txt, ',');
		} else {
			return '';
		}
	}

	public function isIncludeINV($file)
	{
		preg_match('/INV.*?(\\d+)/i', $file, $data);

		if (count($data) === 0) {
			return '';
		}
		return $data[0];
	}

	public function isSURRENDER($filename)
	{
		if (preg_match("/-SURRENDER/i", $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function mapQuantationManyQA($customerCode, $quantation, $voucher)
	{
		return Database::hasRows(
			$this->db_ax,
			"SELECT Q.custaccount, Q.quotationid, Q.DATAAREAID, Q.CREATEDDATE, CJ.VOUCHER_NO
			from SalesQuotationTable Q
			JOIN SALESTABLE SO
			ON SO.QUOTATIONID = Q.QUOTATIONID
			AND SO.DATAAREAID = Q.DATAAREAID
			JOIN CustPackingSlipJour CJ
			ON CJ.SALESID = SO.SALESID
			AND CJ.DATAAREAID = SO.DATAAREAID
			where Q.DATAAREAID = 'dsc'
			and Q.quotationid IN ($quantation)
			--and Q.custaccount = ?
			AND SO.CUSTACCOUNT = ?
			and CJ.VOUCHER_NO = ?
			order by Q.CREATEDDATE desc",
			[$customerCode, $voucher]
		);
	}

	public function getEmailFromCustomerCode($customerCode)
	{
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT DSG_EMAIL 
				FROM DSG_CUSTOMEREMAILLIST 
				WHERE DSG_ACCOUNTNUM = ?
				AND DSG_SENDTYPE = 3 -- sender
				AND DSG_INACTIVE = 0",
				[
					$customerCode
				]
			);

			if (count($data) === 0) {
				return '';
			} else {
				return $data[0]['DSG_EMAIL'];
			}
		} catch (\Exception $e) {
			return '';
		}

		// $conn = self::connectLive();
		// $data = Sqlsrv::rows(
		// 	$conn,
		// 	"SELECT E.Email 
		// 	FROM EmailMapping E
		// 	WHERE E.CustomerCode = ?",
		// 	[
		// 		$customerCode
		// 	]
		// );

		// if (count($data) === 0) {
		// 	return '';
		// } else {
		// 	return $data[0]['Email'];
		// }
	}

	public function getShippingBodyConfirm($file)
	{


		$txt = '';

		preg_match('/INV.*?(\\d+)/i', $file, $matched_inv);

		if (count($matched_inv) === 0) {
			$inv = '-';
		}

		$custcode = self::getCustomerCode($file);

		if ($custcode === '' || $custcode === null) {
			$_customerCode = '-';
		} else {
			$_customerCode = $custcode;
		}

		$getData = Database::rows(
			$this->db_ax,
			"SELECT 
			CT.NAME [Customer],
			ST.QUOTATIONID [PI],
			CC.CUSTOMERREF [PO],
			ST.SALESID [SO],
			CPS.DSG_ETD [ETD],
			CPS.DSG_ETA [ETA],
			ST.DSG_ToPortDesc [ToPort],
			CC.DSG_VOUCHERSERIES [Year],
			ST.DATAAREAID [Company]
			FROM SALESTABLE ST
			-- LEFT JOIN CustConfirmJour CC ON ST.CUSTACCOUNT = CC.INVOICEACCOUNT
			LEFT JOIN CustConfirmJour CC ON ST.SALESID = CC.SALESID AND CC.DATAAREAID = 'dsc'
			LEFT JOIN CUSTTABLE CT ON CT.ACCOUNTNUM = ST.CUSTACCOUNT AND CT.DATAAREAID = 'dsc'
			LEFT JOIN CustPackingSlipJour CPS ON ST.SALESID = CPS.SALESID AND CPS.DATAAREAID = 'dsc'
			WHERE ST.SALESID = CC.SALESID
			AND ST.CUSTACCOUNT = ?
			AND ST.DATAAREAID = 'dsc'
			AND CC.DSG_VoucherNo = ?
			AND CPS.SalesId = ST.SALESID
			ORDER BY CPS.PackingSlipId DESC",
			[
				$_customerCode,
				substr($matched_inv[0], 3)
			]
		);

		if (count($getData) === 0) {
			return '-';
		}

		$txt .= 'Dear Sir / Madam,<br/><br/>';
		$txt .= 'Please see shipping document as attached. <br/>';
		$txt .= '<u><i><font color="green">Kindly review and confirm all document to us by return.</font></i></u>
				Awaiting for kind feedback. Thank you. <br/><br/>';
		$txt .= '<b>Customer name : </b>' . $getData[0]['Customer'] . '<br>';
		$txt .= '<b>PI ID : </b>DSC-' . $getData[0]['PI'] . '<br>';
		$txt .= '<b>PO : </b>' . $getData[0]['PO'] . '<br>';
		$txt .= '<b>SO ID : </b>' . $getData[0]['SO'] . '<br>';
		$txt .= '<b>ETD : </b>' . self::isDateNull($getData[0]['ETD']) . '<br>';
		$txt .= '<b>ETA : </b>' . self::isDateNull($getData[0]['ETA']) . '<br>';
		$txt .= '<b>Invoice No : </b>' . strtoupper($getData[0]['Company']) . '/' . $getData[0]['Year'] . '/' . substr($matched_inv[0], 3) . '<br>';
		$txt .= '<b>Destination port : </b>' . $getData[0]['ToPort'] . '<br>';
		$txt .= '<b>Agent : </b>' . self::getAgent($getData[0]['SO'], $_customerCode) .  '<br>';
		$txt .= '<b>Shipping Line : </b>' . self::getShippingLine($getData[0]['SO'], $_customerCode);

		return $txt;
	}

	public function isDateNull($date)
	{

		if (date('Y-m-d', strtotime($date)) === '1900-01-01' || date('Y-m-d', strtotime($date)) === '1970-01-01') {
			return '-';
		} else {
			return date('d M Y', strtotime($date));
		}
	}

	public function getAgent($so, $customer)
	{
		$res = Database::rows(
			$this->db_ax,
			"SELECT A.DSG_DESCRIPTION [Agent]
			FROM Salestable ST
			LEFT JOIN DSG_AgentTable A ON A.DSG_AGENTID = ST.DSG_PRIMARYAGENTID AND A.DATAAREAID = 'dsc'
			where ST.salesid = ?
			AND ST.custaccount = ?
			AND ST.DATAAREAID = 'dsc'",
			[
				$so,
				$customer
			]
		);

		if (count($res) === 0) {
			return '-';
		} else {
			return $res[0]['Agent'];
		}
	}

	public function getShippingSubjectConfirm($file)
	{
		$tempTxt = '';
		$a = explode('.', $file);

		if (count($a) > 2) {
			for ($i = 0; $i < count($a) - 1; $i++) {
				$tempTxt .= $a[$i];
			}
		} else {
			$tempTxt = $a[0];
		}
		return "Shipping document  [Please confirm] : " . $tempTxt;
	}

	public function getShippingLine($so, $customer)
	{

		$res = Database::rows(
			$this->db_ax,
			"SELECT ST.DSG_ShippingLineDescription [ShippingLine]
			FROM Salestable ST
			where ST.salesid = ?
			AND ST.custaccount = ?
			AND ST.DATAAREAID = 'dsc'",
			[
				$so,
				$customer
			]
		);

		if (count($res) === 0) {
			return '-';
		} else {
			if ($res[0]['ShippingLine'] === '') {
				return '-';
			}
			return $res[0]['ShippingLine'];
		}
	}

	public function getCustomerMail($customerCode)
	{


		$listsTo = [];
		$listsCC = [];

		$_to =  Database::rows(
			$this->db_ax,
			"SELECT DSG_Email FROM DSG_CustomerEmailList 
			WHERE DSG_ACCOUNTNUM = ? 
			AND DSG_SENDTYPE = 0 
			AND DSG_INACTIVE = 0
			AND DATAAREAID = 'dsc'
			GROUP BY DSG_Email",
			[
				$customerCode
			]
		);

		$_cc  = Database::rows(
			$this->db_ax,
			"SELECT DSG_Email FROM DSG_CustomerEmailList 
			WHERE DSG_ACCOUNTNUM = ? 
			AND DSG_SENDTYPE = 1 
			AND DSG_INACTIVE = 0
			AND DATAAREAID = 'dsc'
			GROUP BY DSG_Email",
			[
				$customerCode
			]
		);

		foreach ($_to as $t) {
			$listsTo[] = $t['DSG_Email'];
		}

		foreach ($_cc as $c) {
			$listsCC[] = $c['DSG_Email'];
		}

		return [
			'to' => $listsTo,
			'cc' => $listsCC
		];
	}
}
