<?php
// Registration form handler for livinginconnectionretreat.nl (/register/).
// E-mails the registration to the hosts and a copy to the participant. Nothing is stored on the server.

date_default_timezone_set('Europe/Amsterdam');
$form   = '/register/';
$thanks = '/register/thanks/';
$to     = 'info@livinginconnectionretreat.nl';           // shared mailbox, also the reply-to for participants
$hosts  = [$to, 'caroline@leadershipimpulse.com', 'rdegaayfortman@gmail.com']; // everyone who gets the registration
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
  'company' => line('company'), 'vatnr' => line('vatnr', 50),
  'diet' => para('diet'), 'medical' => para('medical'), 'hope' => para('hope'),
];
$ok = (bool)$email && (($_POST['agree'] ?? '') === 'yes');
$business = (($_POST['booking_as'] ?? '') === 'business');
if (!in_array($_POST['booking_as'] ?? '', ['private', 'business'], true)) { $ok = false; }
foreach ($f as $k => $v) { if ($v === '' && !in_array($k, ['company', 'vatnr'], true)) { $ok = false; } }
if ($business && $f['company'] === '') { $ok = false; }
if (!$ok) { header('Location: ' . $form . '?error=1'); exit; }

$now = date('Y-m-d H:i');
$early = time() < strtotime('2027-05-01 00:00:00');
if ($business) { $price = $early ? 'EUR 595 excl. 21% VAT (business, early bird)' : 'EUR 695 excl. 21% VAT (business)'; }
else           { $price = $early ? 'EUR 645 incl. 21% VAT (private, early bird)' : 'EUR 745 incl. 21% VAT (private)'; }

$summary = "INVOICE TYPE: " . ($business ? 'BUSINESS / SELF-EMPLOYED (plus 21% VAT)' : 'PRIVATE (incl. 21% VAT)') . "\n"
  . ($business ? "Company: {$f['company']}\nVAT number: " . ($f['vatnr'] !== '' ? $f['vatnr'] : '-') . "\n" : '')
  . "Price: $price\n\nName: {$f['fullname']}\nEmail: $email\nPhone: {$f['phone']}\nDate of birth: {$f['dob']}\n\n"
  . "Address:\n{$f['address']}\n\nInvoice address:\n{$f['invoice']}\n\n"
  . "Dietary allergies or wishes:\n{$f['diet']}\n\nMedical condition:\n{$f['medical']}\n\n"
  . "Emergency contact: {$f['emergency']}\n\nWhat they hope to get out of the retreat:\n{$f['hope']}\n\n"
  . "Agreed with terms and conditions: yes\nSubmitted on: $now\n";

$from = "From: Living in Connection <website@livinginconnectionretreat.nl>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$sub = function($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; };

// To the hosts
$toHosts = @mail(implode(', ', $hosts), $sub("New registration (" . ($business ? 'BUSINESS' : 'PRIVATE') . "): {$f['fullname']}"),
  "New registration via the website.\nConfirm the place within 3 working days, then send the invoice.\n\n" . $summary,
  $from . "Reply-To: $email\r\n");

// Confirmation to the participant: thank you and next steps only (no copy of their answers)
$first = explode(' ', $f['fullname'])[0];
$msg = "Dear $first,\n\nThank you for registering for the Living in Connection nature retreat, 21 to 23 May 2027 at Landgoed Welna in Epe. We have received your registration.\n\nNext steps:\n1. Within 3 working days we confirm your place by email.\n2. You then receive an invoice, payable within 14 days by bank transfer or iDEAL. Your place is guaranteed once payment has been received.\n3. After that you receive the route, the packing list and a short welcome from us.\n\nTerms and conditions: https://livinginconnectionretreat.nl/terms-and-conditions/\n\nQuestions? Just reply to this email.\n\nWarm regards,\nRick de Gaay Fortman and Caroline Hamrit\nLiving in Connection\n";
@mail($email, $sub('We received your registration: Living in Connection, 21-23 May 2027'), $msg,
  $from . "Reply-To: $to\r\n");

header('Location: ' . ($toHosts ? $thanks : $form . '?error=1'));
exit;
