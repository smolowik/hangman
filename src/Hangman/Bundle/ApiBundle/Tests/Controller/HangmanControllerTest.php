<?php
namespace Hangman\Bundle\ApiBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Hangman\Bundle\ApiBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class HangmanControllerTest extends WebTestCase
{
    public function testSuccessGame () {
      $game_id = $this->assertCreate();
      $this->assertSuccessGame($game_id);
    }
    public function testFailureGame () {
      $game_id = $this->assertCreate();
      $this->assertFailureGame($game_id);
    }
    public function testExceptionGame () {
      $game_id = $this->assertCreate();
      $result = $this->putRequest($game_id, "aa", 400, false);
      $result = $this->putRequest(98766544, "a", 404, false);
    }
    public function assertCreate()
    {
        $client = static::createClient();
        $client->request('POST', '/games', array('ACCEPT' => 'application/json'));
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertEquals(201, $response->getStatusCode());
        $json = json_decode($content);
        $this->assertInstanceOf("\stdClass", $json);
        $this->assertObjectHasAttribute("game", $json);
        $this->assertObjectHasAttribute("tries_left", $json->game);
        $this->assertObjectHasAttribute("word", $json->game);
        $this->assertObjectHasAttribute("status", $json->game);
        $this->assertObjectHasAttribute("id", $json->game);
        $this->assertEquals(Game::STATUS_BUSY, $json->game->status);
        return $json->game->id;
    }
    private function assertSuccessGame ($game_id) {
      $doctrine = $this->getContainer()->get('doctrine');
      $game = $doctrine->getRepository('HangmanApiBundle:Game')
        ->find($game_id);
      $word = $game->getWord();
      for ($i = 0; $i < strlen($word); $i++) {
        $json = $this->putRequest($game_id, $word[$i]);
        $this->assertEquals(11, $json->game->tries_left);
        if ($json->game->word === $game->getWord()) {
          $this->assertEquals(Game::STATUS_SUCCESS, $json->game->status);
          break;
        } else {
          $this->assertEquals(Game::STATUS_BUSY, $json->game->status);
        }
      }
    }
    
    private function assertFailureGame ($game_id) {
      $doctrine = $this->getContainer()->get('doctrine');
      $game = $doctrine->getRepository('HangmanApiBundle:Game')
        ->find($game_id);
      $word = str_split($game->getWord());
      $letter = "a";
      for ($i = 0; $i < 26; $i++) { 
        if (!in_array($letter, $word)) {
          break;
        }     
        $letter++;
      }
      for ($i = 10; $i >= 0; $i--) {
        $json = $this->putRequest($game_id, $letter);
        $this->assertEquals($i, $json->game->tries_left);
        if ($i === 0) {
          $this->assertEquals(Game::STATUS_FAIL, $json->game->status);
        } else {
          $this->assertEquals(Game::STATUS_BUSY, $json->game->status);
        }
      }
    }
    
    private function putRequest ($game_id, $letter, $status = 200, $tojson = true) {
      $client = static::createClient();
      $client->request('PUT', '/games/' . $game_id . "?letter=".$letter , array('ACCEPT' => 'application/json'));
      $response = new \Symfony\Component\HttpFoundation\Response();
      $response = $client->getResponse();
      $content = $response->getContent();
      $this->assertEquals($status, $response->getStatusCode());
      if ($tojson) {
        $json = json_decode($content);
        $this->assertInstanceOf("\stdClass", $json);
        $this->assertObjectHasAttribute("game", $json);
        $this->assertObjectHasAttribute("tries_left", $json->game);
        $this->assertObjectHasAttribute("word", $json->game);
        $this->assertObjectHasAttribute("status", $json->game);
        $this->assertObjectHasAttribute("id", $json->game);
        return $json;
      }
      return $content;
    }
}