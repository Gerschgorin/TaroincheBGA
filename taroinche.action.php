<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Taroinche implementation : © Côme Dattin <c.dattin@gmail.com>
 * Implemented using parts of the coinche and tarot games
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * taroinche.action.php
 *
 * Taroinche main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/taroinche/taroinche/myAction.html", ...)
 *
 */
  
  
  class action_taroinche extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "taroinche_taroinche";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

    public function playCard() {
      self::setAjaxMode();
      $card_id = self::getArg("id", AT_posint, true);
      $this->game->playCard($card_id);
      self::ajaxResponse();
  }


  // BIDDING actions
  public function bid() {
		self::setAjaxMode();
		$value = self::getArg('value', AT_posint, true);
		$color = self::getArg('color', AT_posint, true);
    $dog = self::getArg('dog', AT_posint, true);
    $partner = self::getArg('partner', AT_posint, true);
		$this->game->bid($value, $color, $dog, $partner);
		self::ajaxResponse();
	}

  public function pass() {
		self::setAjaxMode();
		$this->game->pass();
		self::ajaxResponse();
	}

  public function coinche() {
		self::setAjaxMode();
		$this->game->coinche();
		self::ajaxResponse();
	}



  // DOG MAKING
  public function makeDog() {
    self::setAjaxMode();
    $cards_ids = self::getArg("cards_ids", AT_numberlist, true);
    
    // Convert the string (elements are separated by a comma) to an array
    str_replace(';', ',', $cards_ids); // Normally JS does not send any semi-colon. This is done to avoid cheating somehow
    if($cards_ids == '') {
        $cards_ids = array();
    }
    else {
        $cards_ids = explode(',', $cards_ids);
    }
    
    $this->game->discard($cards_ids);
    self::ajaxResponse();
}




  }
  

