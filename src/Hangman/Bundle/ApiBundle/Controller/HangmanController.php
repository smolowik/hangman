<?php

namespace Hangman\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Hangman\Bundle\ApiBundle\Entity\Game;

class HangmanController extends FOSRestController {

  /**
   * Method creates a new game.
   *
   * @ApiDoc(
   *  resource=true,
   *  output={
   *      "class"="Hangman\Bundle\ApiBundle\Entity\Game"
   *  },
   *  statusCodes={
   *      201="The new game is created.",
   *      500="Application error."
   *  }
   * )
   */
  public function createAction(Request $request) {
    $view = $this->view("", 201);
    
    $repository = $this->getDoctrine()
            ->getRepository('HangmanApiBundle:Word');

    $word = $repository->createQueryBuilder('q')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    
    $game = new Game();
    $game->setTriesLeft(11);
    $game->setWord($word->getWord());
    $game->setStatus(Game::STATUS_BUSY);
    
    $em = $this->getDoctrine()->getManager();
    $em->persist($game);
    $em->flush();
    
    $view->setData(array("game" => $game));
    return $this->handleView($view);
  }
  
  /**
   * If the letter is correct, it does not reduce amount of tries left. 
   * If it is bad it reduces amount of tries left. After winning status changes to `success`. After losing to `fail`.
   *
   * @ApiDoc(
   *  resource=true,
   * description="This method is used to playing.",
   * requirements={
   *      {
   *          "name"="letter",
   *          "dataType"="string",
   *          "requirement"="a-z",
   *          "description"="Guessing letter."
   *      },
   *      {
   *          "name"="id",
   *          "dataType"="integer",
   *          "requirement"="\d+",
   *          "description"="Game ID."
   *      }
   *  },
   *  output={
   *      "class"="Hangman\Bundle\ApiBundle\Entity\Game"
   *  },
   *  statusCodes={
   *      200="The correct result.",
   *      400="An incorrect letter.",
   *      404="No games were found for the given id.",
   *      500="Application error."
   *  }
   * )
   */
  public function playAction(Request $request, $id) {
    $view = $this->view("", 200);
    $game = $this->getDoctrine()
            ->getRepository('HangmanApiBundle:Game')
            ->find($id);
    if (!$game) {
      throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, "No games were found for the given id.");
    }
    $letter = $request->get('letter');
    
    try {
      if ($game->getStatus() === Game::STATUS_BUSY) {
        $game->addCharacterGuessed($letter);
      }
    } catch (\Exception $ex) {
      throw new \Symfony\Component\HttpKernel\Exception\HttpException(400, $ex->getMessage());
    }
    if ($game->getWord() == $game->getHiddenWord()) {
      $game->setStatus(Game::STATUS_SUCCESS);
    } elseif ($game->getStatus() === Game::STATUS_BUSY && $game->getTriesLeft() <= 0) {
      $game->setStatus(Game::STATUS_FAIL);
    }
    
    
    $em = $this->getDoctrine()->getManager();
    $em->persist($game);
    $em->flush();
    $view->setData(array("game" => $game));
    return $this->handleView($view);
  }

}
