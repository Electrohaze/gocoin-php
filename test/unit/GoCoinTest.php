<?php

class GoCoinTest extends PHPUnit_Framework_TestCase
{
  const EXPECTED_LIBRARY_VERSION = '0.7';
  const AUTH_CODE = '45a717d1b40a35c0a1ea4aed638f20eb94add2763a951a81ac421005cdb56d6d';
  const MINIMAL_TEST = TRUE;

  private $testMethods = NULL;

  public function setUp()
  {
    if (GoCoinTest::MINIMAL_TEST)
    {
      $this -> testMethods = array(
        'testDoNothing',
        'testSettings',
        'testExchange',
        'testAuthUrl',
        //'testGetSelf',
      );
    }
    else
    {
      $this -> testMethods = array(
        'testDoNothing',
        'testSettings',
        'testExchange',
        'testAuthUrl',
        //'testGetToken',
        'testGetSelf',
        'testGetUser',
        //'testUpdateUser',
        //'testPassword',
        'testGetAccounts',
        'testGetInvoices',
        'testGetMerchant',
        //'testUpdateMerchant',
        //'testGetMerchantPayouts',
        //'testGetMerchantConversions',
        //'testGetMerchantUsers',
      );
    }
  }

  public function doTest($testMethodName)
  {
    $test = empty($this -> testMethods) || in_array($testMethodName, $this -> testMethods);
    if ($test) { echo "\n[WARNING]: $testMethodName()\n"; }
    return $test;
  }

  public function testDoNothing()
  {
    //make phpunit happy since it doesn't like where there are no tests
  }

  public function testSettings()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    $version = GoCoin::getVersion();
    $mode = GoCoin::getApiMode();
    echo '[DEBUG]: VERSION: ' . $version . "\n";
    echo '[DEBUG]: API MODE: ' . $mode . "\n";
    echo '[DEBUG]: CLIENT_ID: ' . CLIENT_ID . "\n";
    echo '[DEBUG]: CLIENT_SECRET: ' . CLIENT_SECRET . "\n";
    echo '[DEBUG]: TOKEN: ' . TOKEN . "\n";

    //verify test settings
    $client = GoCoin::getClient(TOKEN);
    echo '[WARNING]: ==== Settings for [' . $mode . "] ====\n";
    echo '[DEBUG]: API URL: ' . $client -> options['host'] . "\n";
    echo '[DEBUG]: DASHBOARD URL: ' . $client -> options['dashboard_host'] . "\n";

    //assertions
    $this -> assertEquals($client -> options['host'], GoCoin::PRODUCTION_HOST);
    $this -> assertEquals($client -> options['dashboard_host'], GoCoin::PRODUCTION_DASHBOARD_HOST);

    //verify test settings
    $mode = GoCoin::setApiMode('test');
    $client = GoCoin::getClient(TOKEN);
    echo '[WARNING]: ==== Settings for [' . $mode . "] ====\n";
    echo '[DEBUG]: API URL: ' . $client -> options['host'] . "\n";
    echo '[DEBUG]: DASHBOARD URL: ' . $client -> options['dashboard_host'] . "\n";

    //assertions
    $this -> assertEquals($client -> options['host'], GoCoin::TEST_HOST);
    $this -> assertEquals($client -> options['dashboard_host'], GoCoin::TEST_DASHBOARD_HOST);

    //make sure we're at the expected client library version
    $this -> assertEquals($version, GoCoinTest::EXPECTED_LIBRARY_VERSION);

    //make sure to put the mode back to test for the rest of the tests
    $mode = GoCoin::setApiMode('test');
  }

  public function testExchange()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    //get the exchange rate from the gocoin web service
    $exchange = GoCoin::getExchangeRates();
    //perform assertion
    $this -> assertTrue(property_exists($exchange, 'prices'));
    $this -> assertTrue(property_exists($exchange -> prices, 'BTC'));
    echo '[DEBUG]: SUCCESS:' . $exchange -> prices -> BTC -> USD . "\n";
  }

  public function testAuthUrl()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    $auth_url = GoCoin::requestAuthorizationCode(
      CLIENT_ID, CLIENT_SECRET, SCOPE, REDIRECT_URL
    );
    echo '[DEBUG]: SUCCESS: [' . $auth_url . "]\n";
  }

  public function testGetToken()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    $token = GoCoin::requestAccessToken(
      CLIENT_ID, CLIENT_SECRET, GoCoinTest::AUTH_CODE, REDIRECT_URL
    );
    echo '[DEBUG]: SUCCESS: [' . $token . "]\n";
  }

  public function testGetSelf()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    echo '[DEBUG]: API MODE:' . GoCoin::getApiMode() . "\n";
    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);
    //get the current user
    $user = GoCoin::getUser(TOKEN);
    //perform assertion
    $this -> assertEquals($user -> id, USER_ID);
    echo '[DEBUG]: SUCCESS:' . $user -> id . "\n";
  }

  public function testGetUser()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }
    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);
    //get the current user
    $user = GoCoin::getUser(TOKEN, USER_ID);
    //perform assertion
    $this -> assertEquals($user -> id, USER_ID);
    echo '[DEBUG]: SUCCESS:' . $user -> id . "\n";
  }

  public function testUpdateUser()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get the current user
    $user = GoCoin::getUser(TOKEN, USER_ID);

    //show the results
    echo '[DEBUG]: Updating :' . $user -> id . "\n";

    //create an array of fields to update, NOTE: id is required
    $last_name = $user -> last_name;
    $updates = array(
      'id' => $user -> id,
      'last_name' => $last_name . ' updated',
    );

    //update the user
    $updated = GoCoin::updateUser(TOKEN,$updates);

    //make sure we got a successful response back
    $this -> assertTrue(property_exists($updated, 'last_name'));

    //show the results
    echo '[DEBUG]: Updated last name: ' . $updated -> last_name . "\n";

    //perform assertion
    $this -> assertEquals($updated -> last_name, $last_name . ' updated');

    //reset the last name
    $updates['last_name'] = $last_name;
    $updated = GoCoin::updateUser(TOKEN,$updates);

    //make sure we got a successful response back
    $this -> assertTrue(property_exists($updated, 'last_name'));

    //show the results
    echo '[DEBUG]: Reset last name: ' . $updated -> last_name . "\n";

    //perform assertion
    $this -> assertEquals($updated -> last_name, $last_name);

    echo '[DEBUG]: SUCCESS' . "\n";
  }

  public function testPassword()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //an array to update the password
    $pw_array = array(
      "current_password" => "passw0rd",
      "password" => "newpassw0rd",
      "password_confirmation" => "newpassw0rd",
    );

    //update the password
    $pw_update = GoCoin::updatePassword(TOKEN,USER_ID,$pw_array);
    $this -> assertEquals($pw_update -> code, '204');

    //put the password back
    $pw_array = array(
      "current_password" => "newpassw0rd",
      "password" => "passw0rd",
      "password_confirmation" => "passw0rd",
    );

    //update the password back
    $pw_update = GoCoin::updatePassword(TOKEN,USER_ID,$pw_array);
    $this -> assertEquals($pw_update -> code, '204');
    echo '[DEBUG]: SUCCESS' . "\n";
  }

  public function testGetAccounts()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get accounts for this merchant
    $accounts = GoCoin::getAccounts(TOKEN,MERCHANT_ID);
    $this -> assertNotEmpty(sizeof($accounts));
    $this -> assertGreaterThan(0, sizeof($accounts));

    //example search criteria array
    $criteria = array(
      //'start_time' =>  '2014-03-14T00:00:00.000Z',
      //'end_time' => $end_time,
      //'page' => $page_number,
      'per_page' => 10,
    );

    //search transactions with criteria
    $xactions = GoCoin::getAccountTransactions(TOKEN,ACCOUNT_ID,$criteria);
    $this -> assertEquals($xactions -> status, '200');
    echo '[DEBUG]: Found ' . $xactions -> paging_info -> total . ' total transactions' . "\n";
    echo '[DEBUG]: SUCCESS:' . sizeof($accounts) . "\n";
  }

  public function testGetInvoices()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //search invoices with no criteria, returns all of em
    $invoices = GoCoin::searchInvoices(TOKEN);
    $this -> assertEquals($invoices -> status, '200');
    echo '[DEBUG]: Found ' . $invoices -> paging_info -> total . ' total invoices' . "\n";
    echo '[DEBUG]: Currently have ' . sizeof($invoices -> invoices) . ' invoices' . "\n";

    //example search criteria array
    $criteria = array(
      'merchant_id' => MERCHANT_ID,
      //'status' => $status,
      'start_time' =>  '2014-03-14T00:00:00.000Z',
      //'end_time' => $end_time,
      //'page' => $page_number,
      'per_page' => 10,
    );

    //search invoices with criteria
    $invoices = GoCoin::searchInvoices(TOKEN,$criteria);

    echo '[DEBUG]: Found ' . $invoices -> paging_info -> total . ' total recent invoices' . "\n";
    echo '[DEBUG]: Currently have ' . sizeof($invoices -> invoices) . ' recent invoices' . "\n";

    //get a specific invoice
    $specific = GoCoin::getInvoice(TOKEN,INVOICE_ID);

    $this -> assertEquals($specific -> id, INVOICE_ID);

    echo '[DEBUG]: SUCCESS: ' . $specific -> id . "\n";
  }

  public function testGetMerchant()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get a merchant
    $merchant = GoCoin::getMerchant(TOKEN,MERCHANT_ID);
    $this -> assertEquals($merchant -> id, MERCHANT_ID);

    echo '[DEBUG]: SUCCESS: ' . $merchant -> id . "\n";
  }

  public function testUpdateMerchant()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get a merchant
    $merchant = GoCoin::getMerchant(TOKEN,MERCHANT_ID);
    $this -> assertEquals($merchant -> id, MERCHANT_ID);

    //create an array of fields to update, NOTE: id is required
    $name = $merchant -> name;
    $updates = array(
      'id' => $merchant -> id,
      'name' => $name . ' (UPDATED)',
    );

    //update the merchant
    $updated = GoCoin::updateMerchant(TOKEN,$updates);

    //make sure we got a successful response back
    $this -> assertTrue(property_exists($updated, 'name'));

    //show the results
    echo '[DEBUG]: Updated name: ' . $updated -> name . "\n";

    //perform assertion
    $this -> assertEquals($updated -> name, $name . ' (UPDATED)');

    //reset the name
    $updates['name'] = $name;
    $updated = GoCoin::updateMerchant(TOKEN,$updates);

    //make sure we got a successful response back
    $this -> assertTrue(property_exists($updated, 'name'));

    //show the results
    echo '[DEBUG]: Reset name: ' . $updated -> name . "\n";

    //perform assertion
    $this -> assertEquals($updated -> name, $name);

    echo '[DEBUG]: SUCCESS: ' . "\n";
  }

  public function testGetMerchantPayouts()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get all payouts
    $payouts = GoCoin::getMerchantPayouts(TOKEN,MERCHANT_ID);
    //var_dump($payouts);
    $this -> assertGreaterThanOrEqual(0,sizeof($payouts));
    echo '[DEBUG]: SUCCESS: ' . sizeof($payouts) . "\n";
  }

  public function testGetMerchantConversions()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    $conversions = GoCoin::getCurrencyConversions(TOKEN,MERCHANT_ID);
    //var_dump($conversions);
    $this -> assertGreaterThanOrEqual(0,sizeof($conversions));
    echo '[DEBUG]: SUCCESS: ' . sizeof($conversions) . "\n";
  }

  public function testGetMerchantUsers()
  {
    if (!$this -> doTest(__FUNCTION__)) { return; }

    //perform assertion
    $this -> assertEquals(GoCoin::getApiMode(), 'test');
    $this -> assertNotEmpty(TOKEN);

    //get a list of all merchant users
    $users = GoCoin::getMerchantUsers(TOKEN,MERCHANT_ID);
    $this -> assertGreaterThanOrEqual(1,sizeof($users));
    $this -> assertEquals($users[0] -> id,USER_ID);
    echo '[DEBUG]: SUCCESS: ' . sizeof($users) . ' - ' . $users[0] -> id . "\n";
  }

}
