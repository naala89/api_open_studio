<?php

namespace Datagator\Output;

use Datagator\Config;
use Datagator\Core;

class Email extends Output
{
  private $defaults = array(
    'subject' => 'Datagator resourve result',
    'from' => 'resource@datagator.com.au',
    'format' => 'json',
  );
  protected $details = array(
    'name' => 'Email',
    'description' => 'Output in email format.',
    'menu' => 'Output',
    'application' => 'All',
    'input' => array(
      'to' => array(
        'description' => 'A single or array of emails to send the results to.',
        'cardinality' => array(1, '*'),
        'accepts' => array('processor', 'literal'),
      ),
      'from' => array(
        'description' => 'From email address for the email.',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'literal'),
      ),
      'subject' => array(
        'description' => 'Subject for the email.',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', 'literal'),
      ),
      'format' => array(
        'description' => 'Format for the results to be formatted into.',
        'cardinality' => array(1, 1),
        'accepts' => array('processor', '"json"', '"html"', '"text"'),
      ),
    ),
  );

  /**
   * Override the parent class process(), because we want to generate the data in another Output processor
   * and then send it in emails.
   *
   * @return bool
   * @throws \Datagator\Core\ApiException
   * @throws \Exception
   * @throws \phpmailerException
   */
  public function process() {
    Core\Debug::message("Output Email");

    $mail = new \PHPMailer();
    $to = $this->val($this->meta->to);
    $from = !empty($this->meta->from) ? $this->val($this->meta->from) : $this->defaults['from'];
    $subject = !empty($this->meta->subject) ? $this->val($this->meta->subject) : $this->defaults['subject'];
    $format = $this->val($this->meta->format);
    $class = '\\Datagator\\Output\\' . $format;
    $obj = new $class($this->data, 200, '');
    $data = $obj->getData();
    $altData = $data;
    $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $html .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
    $html .= '<title>PHPMailer Test</title></head><body>';
    $html .= '<div style="width: 640px; font-family: Arial, Helvetica, sans-serif; font-size: 11px;"><p>';// . $data . '</p></div></body></html>';
    switch ($format) {
      case 'html':
      default:
        $html .= $data . '</p>';
        break;
      case 'json':
      case 'text':
      default:
        $html .= $data . '</p></div></body></xml>';
        break;
    }

    // email params
    switch (Config::$emailService) {
      case 'smtp':
        $mail->isSMTP();
        $mail->Host = Config::$emailHost;
        $mail->SMTPAuth = Config::$emailAuth;
        $mail->Username = Config::$emailHost;
        $mail->Password = Config::$emailPass;
        $mail->SMTPSecure = Config::$emailSecure;
        $mail->Port = Config::$emailPort;
        break;
      case 'sendmail':
        $mail->isSendmail();
        break;
      case 'qmail':
        $mail->isQmail();
        break;
      case 'mail':
      default:
        break;
    }

    $mail->setFrom($from);
    if (!is_array($to)) {
      $mail->addAddress($to);
    } else {
      foreach ($to as $email) {
        $mail->addAddress($email);
      }
    }
    $mail->Subject = $subject;
    $mail->msgHTML($html);
    $mail->AltBody = $altData;

    if(!$mail->send()) {
      throw new Core\ApiException('email could not be sent. Mailer Error: ' . $mail->ErrorInfo, 1, $this->id, 500);
    }

    return true;
  }

  /**
   * No need for getData() in this class, because it is only a delivery mechanism.
   */
  protected function getData()
  {
  }
}
