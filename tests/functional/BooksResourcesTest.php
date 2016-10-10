<?php

use PHPUnit\Framework\TestCase;
use IbanDominguez\RestUp\App;
use GuzzleHttp\Client;

class BooksResourceTest extends TestCase
{

  public function setUp()
  {
    $this->client = new Client();
  }

  public function testItFetchesAListOfPosts()
  {
    $response = $this->client->request('GET', 'http://localhost:8000/books');

    $this->assertEquals($response->getStatusCode(), 200);
    $this->assertTrue(is_array((array) json_decode($response->getBody())));
  }

  public function testItCreatesANewPost()
  {
    $params = ['title' => 'Sample title', 'date'  => '2016-01-10'];
    $response = $this->client->request('POST', 'http://localhost:8000/books', ['form_params' => $params]);
    $decodesResponse = json_decode($response->getBody());

    $this->assertEquals('Sample title', $decodesResponse->title);
    $this->assertEquals('2016-01-10', $decodesResponse->date);
  }

}
