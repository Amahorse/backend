<?php

declare(strict_types=1);

namespace Kodelines;

use Exception;
use Kodelines\Views\Template;
use Kodelines\Tools\Validate;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;


class Mailer {

  //php mailer Instance
  private $PhpMailer = null;

  //Currenct account connected
  private $currentConnection = null;

  //Counters
  private $sent = 0;

  private $errors = 0;

  /**
   * Il destruct resetta la connsessione alla mail
   */
  public function __destruct() {
    $this->disconnect();
  }


  /**
   * Connette ad un account smtp e ritorna istanza php mailer connessa
   *
   * @param array $account
   * @return PHPMailer
   */
  public function connect(array $account): PHPMailer {

    //Already connected return phpmailer instance or close the connection
    if($this->currentConnection !== null) {

      //If already exists a connection and is the same does not connect again, else disconnect to the old connection
      if($this->currentConnection == $account['account']) {

        return $this->PhpMailer;

      } else {

        $this->disconnect();

      }

    }


    $this->PhpMailer = new PHPMailer();
    $this->PhpMailer->IsSMTP();
    $this->PhpMailer->SMTPKeepAlive = true;
    $this->PhpMailer->SMTPAuth     = true;
    $this->PhpMailer->Password 	   = $account['password'];
    $this->PhpMailer->Host         = $account['host'];
    $this->PhpMailer->Port         = $account['port'];
    $this->PhpMailer->SMTPSecure   = $account['secure'];
    $this->PhpMailer->Username     = $account['username'];

    if(!$this->PhpMailer->SmtpConnect()) {

      $this->debug('Failed SMTP connection to account: '.$account['account'],true);
      
    }

    $this->currentConnection = $account['account'];

    return $this->PhpMailer;
  }

  /**
   * Disconnette php mailer da connessione smtp corrente
   *
   * @return void
   */
  private function disconnect() {

    if($this->PhpMailer !== null && $this->currentConnection !== null) {

      $this->PhpMailer->SmtpClose();
    }

  }

  /**
   * Manda email singola
   *
   * @param string $subject
   * @param string $content
   * @param string $to
   * @param array $account
   * @return void
   */
  public function send(string $subject, string $content, string $to, array $account) {

    try{

      //If development mode is active the email will be sent always to administrator with real recipient aftere the subject
      if(dev()) {
        $subject = '['.$to.'] ' . $subject;
        $to = config('app','administrator');
      }

      //Email validation
      if(!Validate::isEMail($to)) {
        throw new Exception('Wrong email address ' .$to);
      }


      $mode = 'undefined';

    	//if is able to connect to smtp server, the system send with smtp, else send with php
    	if(!$connection = $this->connect($account)) {

  			$header = "From: <". $account['address'].">\n";
  			$header .= "X-Mailer: ".phpversion()."\n";
  			$header .= "MIME-Version: 1.0\n";
  			$header .= "Content-Type: text/html; charset=\"utf-8\"\n";
  			$header .= "Content-Transfer-Encoding: 7bit\n\n";


  			if(!@mail($to,  $subject,  $content, $header)) {
  				throw new Exception('Email send failed on php mode to: ' . $to);
  			}

    		$mode = 'php';

      } else {

  			$connection->ClearAllRecipients();
  			$connection->CharSet = 'UTF-8';
  			$connection->SetFrom($account['address'], $account['sender']);
  			$connection->Subject = $subject;
  			$connection->AltBody = 'Email Message on Html, Please check your configuration for see this message';
  			$connection->msgHTML($content);
  			$connection->addAddress($to);

  			//send
  			if(!$connection->send()) {

          $this->debug('Connection Error: ' . $connection->ErrorInfo);

          return false;

  			}

  		  $mode = 'smtp';
      }

    	$status = true;

    } catch (Exception $e) {

  		$status = false;

    }

  	//Write log
  	new Log('email',$to,array('status' => (string)$status,'mode' => $mode, 'account' => $account['account'], 'subject' => $subject));

  	return $status;
  }

  /**
   * Elabora coda email da tabella database
   *
   * @return array
   */
  public function elabQueue():array {


    //Start microtime
    $language_select = "CASE WHEN e.id_users IS NOT NULL THEN IFNULL((SELECT language FROM users WHERE id = e.id_users),e.language) ELSE e.language END";

    //loop accounts
    foreach(Db::getArray("SELECT * FROM email_accounts") as $account) {

      foreach(Db::getArray("SELECT
				e.*,
				CASE WHEN e.email IS NULL THEN IFNULL((SELECT email FROM users WHERE id = e.id_users),'not_found') ELSE e.email END AS email,
				". $language_select ." AS language,
				t.administrator_copy,
        t.subject
			FROM email_queue e
			JOIN email_templates t ON t.email = e.name 
			WHERE e.status = 0 AND e.single = 0 AND t.lang = ".$language_select." AND t.account = ".Db::encode($account['account'])." ORDER BY e.priority,e.date_ins") as $email) {

        try {

          if(!Validate::isEmail($email['email'])) {
            throw new Exception('Address Not Valid ('.$email['email'].')');
          }

          //Creo contenuto html
          if(!$content = self::create($email['name'],$email['language'],unserialize(base64_decode($email['data'])))) {
            throw new Exception('Builder Error');
          }

          //Invio copia amministratore se necessario
					if(isset($email['administrator_copy']) && $email['administrator_copy'] == 1 && $email['email'] <> config('app','administrator')) {
						$this->send($email['subject'], $content, config('app','administrator'), $account);
					}

          if(!$this->send($email['subject'], $content, $email['email'], $account)) {
            throw new Exception('Unable to Send');
          }

          //update email status to 1 (sent)
          Db::query("UPDATE email_queue SET status = 1, date_send = NOW(), email = ".Db::encode($email['email']).", account = ".Db::encode($account['account'])." WHERE id = " . id($email['id']));

          $this->sent++;

        } catch (Exception $e) {

          //Write log
          new Log('email', 'ERROR: '. $e->getMessage());

          //update email status to 2 (error)
          Db::query("UPDATE email_queue SET status = 2, date_send = NOW(), email = ".Db::encode($email['email']).", account = ".Db::encode($account['account']).", error = ".Db::encode($e->getMessage())." WHERE id = " . id($email['id']));

          $this->errors++;

        }

      }
    }

   return ['sent' => $this->sent, 'errors' => $this->errors];

  }

  /**
   * Crea 
   *
   * @param string $template
   * @param string $language
   * @param array  $data
   * @return string
   */
  public static function create(string $model, string $language, array $data) {


    $template = new Template;

    $template->language = $language;

    if(!$html = $template->draw($model, ['data' => $data], _DIR_TEMPLATES_ . 'email/',true)) {
        return false;
    }

    return $html;

  }


  /**
   * Inserisce una email in coda
   *
   * @param string $name
   * @param string|integer $to
   * @param array $values
   * @param boolean $language
   * @param integer $priority
   * @return void
   */
  public static function queue(string $name, string|int $to, $values = array(), string|bool $language = false, $priority = 2):int|bool {

    $insert = array('priority' => $priority, 'name' => $name);

  	if(is_numeric($to)) {

  		$insert['id_users'] = $to;

  	} else {

  		$insert['email'] = $to;

  	}

  	$insert['data'] = base64_encode(serialize($values));

  	if(!$language) {
  		$insert['language'] = language();
  	} else {
  		$insert['language'] = $language;
  	}
   
    return Db::insert('email_queue',$insert);

  }


  /**
   * Fa funzione di debug
   *
   * @param string $message
   * @return void
   */
  private function debug(string $message) {


      //Write log
      new Log('email', 'ERROR: '. $message);

      //In modalità dev se da errore muore
      throw new RuntimeException($message);

  
  }

  /**
   * Svuota tabella coda email da email inviate prima di X giorni
   *
   * @param integer $days
   * @return void
   */
  public static function clearQueue($days = 7) {
    return Db::query("DELETE FROM email_queue WHERE status <> 0 AND date_ins < DATE(NOW() - INTERVAL ".$days." DAY) ");
  }




}


?>