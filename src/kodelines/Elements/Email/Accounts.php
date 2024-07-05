<?php

declare(strict_types=1);

namespace Elements\Email;

use Kodelines\Abstract\Decorator;
use PHPMailer\PHPMailer\PHPMailer;

class Accounts extends Decorator {





  /**
   * Fa test di connessione per account
   *
   * @param array $params
   * @return boolean
   */
  public static function test(array $params):bool {

    $test = new PHPMailer();
    $test->IsSMTP();
    $test->SMTPKeepAlive = false;
    $test->SMTPAuth     = true;
    $test->Password 	   = $params['password'];
    $test->Host         = $params['host'];
    $test->Port         = $params['port'];
    $test->SMTPSecure   = $params['secure'];
    $test->Username     = $params['username'];

    if(!$test->SmtpConnect()) {
      return false;
    }

    $test->SmtpClose();

    return true;
  }



}

?>