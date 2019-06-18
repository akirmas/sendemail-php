<?php
require_once(__DIR__.'/vendor/autoload.php');

function sendEmail(
  string $username,
  string $password, 
  string $html,
  array $receivers,
  // Optional
  string $subject = null,
  string $name = null,
  string $sendBox = null,
  string $sendFolder = 'Sent Mail',
  //Enable SMTP debugging
  // 0 = off (for production use)
  // 1 = client messages
  // 2 = client and server messages
  int $SMTPDebug = 0,
  //Set the hostname of the mail server
  string $host = 'smtp.gmail.com',
  int $port = 587,
  string $SMTPSecure = 'tls',
  bool $SMTPAuth = true
) : array {
  $sendBox = is_null($sendBox) ? $username : $sendBox;
  $name = is_null($name) ? $username : $name;
  $mail = new PHPMailer\PHPMailer\PHPMailer;
  $mail->isSMTP();

  foreach(
    [
      'username', 'password',
      'subject', 'altBody',
      'host', 'port',
      'SMTPAuth', 'SMTPSecure', 'SMTPDebug'
    ]
    as $property
  )
    if (isset($$property) && !empty($$property))
      $mail->{ucfirst($property)} = $$property;
  
  foreach($receivers as $rBox => $rName) 
    $mail->addAddress($rBox, $rName);

  $mail->setFrom($sendBox, $name);  
  $mail->msgHTML($html);
  //$mail->addAttachment('images/phpmailer_mini.png');
  
  if (!$mail->send()) {
    $error = $mail->ErrorInfo;
    unset($mail);
    return compact('error');
  }
  $send = true;
  $imap = false;
  if (!function_exists('imap_open')) {
    unset($mail);
    return compact('send', 'imap');
  }
  //Section 2: IMAP
  //IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
  //Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
  //You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
  //be useful if you are trying to get this working on a non-Gmail IMAP server.

  //You can change 'Sent Mail' to any other folder or tag
  $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/{$sendFolder}";
  //Tell your server to open an IMAP connection using the same username and password as you used for SMTP      
  $imapStream = imap_open($path, $mail->Username, $mail->Password);
  $imap = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
  imap_close($imapStream);  
  unset($mail);
  return compact('send', 'imap');
}