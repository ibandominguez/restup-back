<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;
use GuzzleHttp\Client;

class PostsResourceTest extends TestCase
{

  public function setUp()
  {
    $this->client = new Client();
  }

  public function testItCreatesAToken()
  {
    $params = ['email' => 'admin@email.com', 'password'  => 'admin'];
    $response = $this->client->request('POST', 'http://localhost:8000/tokens', ['form_params' => $params]);

    $this->assertEquals($response->getStatusCode(), 201);
    $this->assertContains('"token"', (string) $response->getBody());

    return json_decode($response->getBody())->token;
  }

  public function testBookResourceIsProtected()
  {
    $this->expectException(GuzzleHttp\Exception\ClientException::class);
    $params = ['title' => 'Post Title', 'date'  => '2016-12-12'];
    $response = $this->client->request('POST', 'http://localhost:8000/posts', ['form_params' => $params]);
    $this->assertEquals($response->getStatusCode(), 401);
  }

  public function testBookResourceIsCreatedWithJWTToken()
  {
    $params = ['title' => 'Post Title', 'date'  => '2016-12-12'];
    $headers = ['Authorization' => 'Bearer '.$this->testItCreatesAToken()];
    $response = $this->client->request('POST', 'http://localhost:8000/posts', ['form_params' => $params, 'headers' => $headers]);
    $this->assertContains('"id"', (string) $response->getBody());
  }

}
