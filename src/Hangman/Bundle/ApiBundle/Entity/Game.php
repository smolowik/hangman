<?php

namespace Hangman\Bundle\ApiBundle\Entity;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Accessor;
/**
 * Game
 */
class Game {
  //busy|fail|success
  const STATUS_BUSY = "busy";
  const STATUS_FAIL = "fail";
  const STATUS_SUCCESS = "success";
  
    /**
     * The number of tries left to guess the word.
     * @var integer
     */
    private $triesLeft;

    /**
     * Representation of the word that is being guessed.
     * @var string
     * @Accessor(getter="getHiddenWord",setter="setWord")
     */
    private $word;

    /**
     * Current status of the game (busy|fail|success).
     * @var string
     */
    private $status;

    /**
     * @var array
     * @Exclude
     */
    private $charactersGuessed = array();

    /**
     * Game ID.
     * @var integer
     */
    private $id;


    /**
     * Set triesLeft
     *
     * @param integer $triesLeft
     *
     * @return Game
     */
    public function setTriesLeft($triesLeft)
    {
        $this->triesLeft = $triesLeft;

        return $this;
    }

    /**
     * Get triesLeft
     *
     * @return integer
     */
    public function getTriesLeft()
    {
        return $this->triesLeft;
    }

    /**
     * Set word
     *
     * @param string $word
     *
     * @return Game
     */
    public function setWord($word)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * Get word
     *
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }
    
    /**
     * Returns a word with dots in place of unknown letters.
     * 
     * @return string
     */
    public function getHiddenWord () {
      $result = "";
      $word = $this->getWord();
      for ($i = 0; $i < strlen($word); $i++) {
        if (in_array($word[$i], $this->getCharactersGuessed())) {
          $result .= $word[$i];
        } else {
          $result .= ".";
        }
      }
      return $result;
    }
    /**
     * Set status
     *
     * @param string $status
     *
     * @return Game
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_BUSY, self::STATUS_FAIL, self::STATUS_SUCCESS))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set charactersGuessed
     *
     * @param array $charactersGuessed
     *
     * @return Game
     */
    public function setCharactersGuessed($charactersGuessed)
    {
        $this->charactersGuessed = $charactersGuessed;

        return $this;
    }

    /**
     * Get charactersGuessed
     *
     * @return array
     */
    public function getCharactersGuessed()
    {
        return $this->charactersGuessed;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Add guessed letter
     *
     * @param string $letter Guessed letter
     * @return Game
     */
    public function addCharacterGuessed ($letter) {
      if (!preg_match("/^[a-z]{1}$/", $letter)) {
        throw new \Exception("An incorrect letter.");
      }
      if (strpos($this->getWord(), $letter) !== false) {
        array_push($this->charactersGuessed, $letter);
      } else {
        $this->setTriesLeft($this->getTriesLeft() - 1);
      }
      
      return $this;
    }
}

