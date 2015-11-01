<?php

namespace Hangman\Bundle\ApiBundle\Tests\Entity;

use Hangman\Bundle\ApiBundle\Entity\Game;

class GameTest extends \PHPUnit_Framework_TestCase {

  private $word = "test";

  public function testBadLetter() {
    $game = new Game();
    $game->setWord($this->word);
    try {
      $game->addCharacterGuessed(2);
      $this->assertEquals(1, 2);
    } catch (\Exception $ex) {
      $this->assertEquals("An incorrect letter.", $ex->getMessage());
    }
    try {
      $game->addCharacterGuessed("aa");
      $this->assertEquals(1, 2);
    } catch (\Exception $ex) {
      $this->assertEquals("An incorrect letter.", $ex->getMessage());
    }
  }
  
  public function testIncorrectLetter() {
    $game = new Game();
    $game->setWord($this->word);
    $game->addCharacterGuessed("a");
    $this->assertEquals(0, count($game->getCharactersGuessed()));
  }

  public function testCorrectLetter() {
    $game = new Game();
    $game->setWord($this->word);
    $this->assertEquals($this->word, $game->getWord());
    $game->addCharacterGuessed("t");
    $this->assertEquals(1, count($game->getCharactersGuessed()));
    $game->addCharacterGuessed("e");
    $this->assertEquals(2, count($game->getCharactersGuessed()));
    $game->addCharacterGuessed("s");
    $this->assertEquals(3, count($game->getCharactersGuessed()));
    $this->assertEquals($game->getWord(), $game->getHiddenWord());
  }

}
