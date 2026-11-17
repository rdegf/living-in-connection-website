<?php
// Registration form handler for livinginconnectionretreat.nl (/register/).
// E-mails the registration to the hosts and a copy to the participant. Nothing is stored on the server.

date_default_timezone_set('Europe/Amsterdam');
$form   = '/register/';
$thanks = '/register/thanks/';
$to     = 'info@livinginconnectionretreat.nl';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . $form); exit; }
if (trim((string)($_POST['website'] ?? '')) !== '') { header('Location: ' . $thanks); exit; } // honeypot: bots

function line($k, $max = 200) {
    $v = (string)($_POST[$k] ?? '');
    $v = trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($v)));
    return mb_substr($v, 0, $max);
}
function para($k, $max = 2000) {
    $v = trim(strip_tags((string)($_POST[$k] ?? '')));
    return mb_substr(str_replace("\r", '', $v), 0, $max);
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$f = [
  'fullname' => line('fullname'), 'phone' => line('phone'), 'dob' => line('dob', 20), 'emergency' => line('emergency'),
  'address' => para('address', 500), 'invoice' => para('invoice', 500),
  'diet' => para('diet'), 'medical' => para('medical'), 'hope' => para('hope'),
];
$ok = (bool)$email && (($_POST['agree'] ?? '') === 'yes');
foreach ($f as $v) { if ($v === '') { $ok = false; } }
if (!$ok) { header('Location: ' . $form . '?error=1'); exit; }

$now = date('Y-m-d H:i');
$early = time() < strtotime('2027-05-01 00:00:00');
$price = $early ? 'Early bird: EUR 645 incl. VAT (private) / EUR 595 excl. VAT (business)'
                : 'EUR 745 incl. VAT (private) / EUR 695 excl. VAT (business)';

$summary = "Name: {$f['fullname']}\nEmail: $email\nPhone: {$f['phone']}\nDate of birth: {$f['dob']}\n\n"
  . "Address:\n{$f['address']}\n\nInvoice address:\n{$f['invoice']}\n\n"
  . "Dietary allergies or wishes:\n{$f['diet']}\n\nMedical condition:\n{$f['medical']}\n\n"
  . "Emergency contact: {$f['emergency']}\n\nWhat they hope to get out of the retreat:\n{$f['hope']}\n\n"
  . "Agreed with terms and conditions: yes\nPrice applicable at submission: $price\nSubmitted on: $now\n";

$from = "From: Living in Connection <website@livinginconnectionretreat.nl>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$sub = function($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; };

// To the hosts
$toHosts = @mail($to, $sub("New registration: {$f['fullname']} (21-23 May 2027)"),
  "New registration via the website.\nConfirm the place within 3 working days, then send the invoice.\n\n" . $summary,
  $from . "Reply-To: $email\r\n");

// Copy to the participant
$first = explode(' ', $f['fullname'])[0];
$intro = "Dear $first,\n\nThank you for registering for the Living in Connection nature retreat, 21 to 23 May 2027 at Landgoed Welna in Epe. Below is a copy of what you submitted.\n\nWe will confirm your place within 3 working days. You then receive an invoice by email, payable within 14 days by bank transfer or iDEAL. Your place is guaranteed once payment has been received.\n\nTerms and conditions: https://livinginconnectionretreat.nl/terms-and-conditions/\n\n";
$outro = "\nWarm regards,\nRick de Gaay Fortman and Caroline Hamrit\nLiving in Connection\n";
@mail($email, $sub('Your registration: Living in Connection, 21-23 May 2027'), $intro . $summary . $outro,
  $from . "Reply-To: $to\r\n");

header('Location: ' . ($toHosts ? $thanks : $form . '?error=1'));
exit;
