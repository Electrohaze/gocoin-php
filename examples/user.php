<?php

require_once('../src/api.php');
require_once('../src/auth.php');
require_once('../src/client.php');

session_start();

/*
  Id      : your app client_id
  Secret  : your app secret id
  scope   : token scope
*/

/// sample headers
$headers = array(
  "'Content-Type' => 'application/json'",
  "'Content-Type' => 'application/text'",
);

$client = new Client(
  array(
    'client_id' => "PLACE_YOUR_CLIENT_ID_HERE",
    'client_secret' => "PLACE_YOUR_CLIENT_SECRET_HERE",
    'scope' => "user_read_write",
    'headers' => $headers,
  )
);

$b_auth = $client -> authorize_api();

if ($b_auth)
{
  $user = $client -> api -> user -> self();
  if (!$user)
  {
    echo $client -> getError();
  }
  // get the exchange rate from the gocoin web service
  $get_the_xrate = $client -> get_xrate();
  if (!$get_the_xrate)
  {
    echo $client -> getError();
  }
}
else
{
  echo $client -> getError();
}

?>

<html>
  <body>
    <?php if ($user) { ?>
    <ul>
        <li>User Id:      <?php echo $user -> id?></li>
        <li>User Email:   <?php echo $user -> email?></li>
        <li>First Name:   <?php echo $user -> first_name?></li>
        <li>Last Name:    <?php echo $user -> last_name?></li>
        <li>Created Date: <?php echo $user -> created_at?></li>
        <li>Updated Date: <?php echo $user -> updated_at?></li>
        <li>Image Url:    <?php echo $user -> image_url?></li>
        <li>Merchant Id:  <?php echo $user -> merchant_id?></li>
    </ul>
    <?php } ?>
    <?php if ($get_the_xrate) { ?>
      <span><b>Timestamp:</b> <?php echo $get_the_xrate -> timestamp; ?></span>
      <br/>
      <span><b>Exchange Rate:</b> <?php echo $get_the_xrate -> prices -> BTC -> USD; ?></span>
    <?php } ?>
  </body>
</html>
