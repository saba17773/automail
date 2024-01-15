<?php

namespace App\Actual_Loading;

use App\Actual_Loading\ActualAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\CSRF;
use App\Common\View;

class ActualController
{
  private $actualAPI = null;
  private $automail = null;
  private $emailApi = null;
  private $csrf = null;
  private $view = null;


  public function __construct()
  {
    $this->automail = new Automail();
    $this->actualAPI = new ActualAPI();
    $this->emailApi = new EmailAPI();
    $this->csrf = new CSRF;
    $this->view = new View;
  }

  // 
  public function approvemail($request, $response, $args)
  {

    $sendby = $this->actualAPI->getEmailSend(65);
    $emails = $this->actualAPI->getEmail(65);

    $getApprove =  $this->actualAPI->getTrucking();
    $subject = $this->actualAPI->getSubject();
    $body = $this->actualAPI->getBody_v2(
      $getApprove

    );

    //  print_r($getApprove);
    //  exit();

    

   // echo "<pre>" . print_r($emails['cc'], true) . "</pre>";
   //  echo "<pre>" . print_r($emails, true) . "</pre>";
    //echo "<pre>" . print_r($subject, true) . "</pre>";
    //echo "<pre>" . print_r($emails, true) . "</pre>";
    //exit();

    // send Mail SMILE
    // if (count($getApprove) > 0) {
    //   $emails = $this->actualAPI->getEmail(46);
    //   $body = $this->actualAPI->getBody_v2(
    //     $getApprove

    //   );

      //   echo $subject;
      //   echo $body;
      //  exit();

      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $body,
        $emails['to'],
        $emails['cc'],
        // ['worawut_s@deestone.com'],
        // ['worawut_s@deestone.com'],
        [],
        [],
        $sendby['sendby'],
        $sendby['sendby']
      );

      if ($sendMail === true) {

        $logging = $this->automail->logging(
          65,
          'Message has been sent',
          null,
          null,
          null,
          null,
          null,
          null,
          'Ax'
        );

         $this->automail->loggingEmail($logging, $emails['to'], 1);
         $this->actualAPI->deletedata();

        echo "Send Email Success. <br>";
      } else {
        echo $sendMail;
      }
   // }
  }
}



