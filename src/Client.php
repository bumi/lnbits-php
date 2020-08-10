<?php

namespace LNbits;

use GuzzleHttp;

class Client {

  private $address = 'https://lnbits.com';
  private $apiKey;
  private $client;

  public function setAddress($address) {
    $this->address = $address;
  }

  public function isConnectionValid() {
    return !empty($this->address) && !empty($this->apiKey);
  }

  public function getInfo() {
    return $this->request('GET', 'getinfo');
  }

  public function addInvoice($invoice) {
    $requestBody = [ "out" => false, "amount" => $invoice['value'], "memo" => $invoice['memo'] ];
    $invoice = $this->request('POST', '/api/v1/payments', json_encode($requestBody));
    $invoice['r_hash'] = $invoice['checking_id']; // kinda mimic lnd
    return $invoice;
  }

  public function getInvoice($checkingId) {
    $invoice = $this->request('GET', '/api/v1/payments' . $checkingId);
    $invoice['settled'] = $invoice['paid']; //kinda mimic lnd
  }

  public function isInvoicePaid($checkingId) {
    $invoice = $this->getInvoice($checkingId);
    return $invoice['settled'];
  }

  private function request($method, $path, $body = null) {
    $headers = [
      'X-Api-Key' => $this->apiKey,
      'Content-Type' => 'application/json'
    ];

    $request = new GuzzleHttp\Psr7\Request($method, $path, $headers, $body);
    $response = $this->client()->send($request);
    if ($response->getStatusCode() == 200) {
      $responseBody = $response->getBody()->getContents();
      return json_decode($responseBody, true);
    } else {
      // raise exception
    }
  }

  private function client() {
    if ($this->client) {
      return $this->client;
    }
    $options = ['base_uri' => $this->address];
    $this->client = new GuzzleHttp\Client($options);
    return $this->client;
  }
}

?>
