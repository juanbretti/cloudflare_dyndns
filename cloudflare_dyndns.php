<?php

$curl = curl_init();

# Get parameters
$ip = $_GET['ip'];
$hostname = $_GET['hostname'];
$email = $_GET['email'];
$key = $_GET['key'];
$mail_to = 'Your Name <you@domain.com  >';
$mail_from = 'Cloudflare DynDNS <noreply@domain.com>';

# GET `zone_id`
curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "X-Auth-Email: {$email}",
    "X-Auth-Key: {$key}"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  $message = "cURL Error #:" . $err;
  echo $message;
} else {
  // echo $response;
}

$data = json_decode($response, true);

function extractDomainName($hostname) {
  $parts = explode('.', $hostname);
  return implode('.', array_slice($parts, 1));
}
$extractedDomain = extractDomainName($hostname);

foreach ($data['result'] as $entry) {
  if ($entry['name'] === $extractedDomain) {
      $zone_id = $entry['id'];
      break;
  }
}

# GET `dns_record`
curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/{$zone_id}/dns_records",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "X-Auth-Email: {$email}",
    "X-Auth-Key: {$key}"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  $message = "cURL Error #:" . $err;
  echo $message;
} else {
  // echo $response;
}

$data = json_decode($response, true);

foreach ($data['result'] as $entry) {
    if ($entry['name'] === $hostname) {
        $dns_records = $entry['id'];
        break;
    }
}

# GET DNS Record Details
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/{$zone_id}/dns_records/{$dns_records}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "X-Auth-Email: {$email}",
    "X-Auth-Key: {$key}"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  $message = "cURL Error #:" . $err;
  echo $message;
} else {
  $data = json_decode($response, true);
  $ip_current = $data['result']['content'];
  // echo "ip: " . $ip_current;
}

if ($ip_current == $ip) {
  $response_json = array(
      "errors" => array(),
      "result" => array(),
      "messages" => array("Same IP. Current " . $ip_current),
      "success" => true
  );
  
  $response = json_encode($response_json);
  echo $response;

} else {
  # PUT the DNS record
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/{$zone_id}/dns_records/{$dns_records}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => "{\n  \"content\": \"{$ip}\",\n  \"name\": \"{$hostname}\",\n  \"proxied\": false,\n  \"type\": \"A\",\n  \"comment\": \"DynDNS\",\n \"ttl\": 60\n}",
    CURLOPT_HTTPHEADER => [
      "Content-Type: application/json",
      "X-Auth-Email: {$email}",
      "X-Auth-Key: {$key}"
    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    $message = "cURL Error #:" . $err;
    echo $message;
  } else {
    echo $response;
    $message = "Changed IP. Previous `" . $ip_current . "` Current `" . $ip . "`\r\n\r\n\r\n" . json_encode(json_decode($response), JSON_PRETTY_PRINT);
  }
  
}

# Send report mail
if ($message) {
  $subject = "Cloudflare: DynDNS: IP replacement";
  $headers = "From: " . $mail_from  . "\r\n" .
            "Reply-To: " . $mail_from . "\r\n" .
            "X-Mailer: PHP/" . phpversion();

  mail($mail_to, $subject, $message, $headers);
}

?>
