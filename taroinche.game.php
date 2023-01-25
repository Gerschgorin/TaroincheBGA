<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Taroinche implementation : © Côme Dattin <c.dattin@gmail.com>
  * Implemented using parts of the coinche and tarot games
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * taroinche.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Taroinche extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            // variables for a full hand
            "first_player_id" => 10,    // Jean-Claude entame
            "contractTaker" => 11,      // a pris le contrat
            "contractPartner" => 12,    // partenaire
            "deadPlayer" => 13,
            "pointsTaker" => 15,        // points gagnés pour contrat
            "pointsDefense" => 16,      // points gagnés par la defense
            "coinche" => 17,
            "turn_count" => 19,
            "passCount" => 20,
            'biddingDone' => 21,

            "contractValue" => 30,
            "contractColor" => 31,     
            "contractDog" => 32,
            "coincher" => 33,            // id of the coincher
            "surCoincher" => 34,
            "beloteId" => 35,
            "belotePlayed" => 36,

            // Variables pour le pli
            "trickColor" => 22,         // couleur du pli
            "strongestCardPlayed" => 25,  // plus grand atout joué
            "trickWinner" => 26,

            // Options
            'numberHands' => 100,

        ) );        

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );


	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "taroinche";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        self::setGameStateInitialValue( 'contractValue', 0 );
        self::setGameStateInitialValue( 'contractColor', 0 );
        self::setGameStateInitialValue( 'contractDog', 0 );
        self::setGameStateInitialValue( 'contractTaker', 0 );
        self::setGameStateInitialValue( 'deadPlayer', 0 );
        self::setGameStateInitialValue( 'coincher', 0 );
        self::setGameStateInitialValue( 'surCoincher', 0 );
        self::setGameStateInitialValue( 'beloteId', 0 );
        self::setGameStateInitialValue( 'belotePlayed', 0 );
        self::setGameStateInitialValue( 'contractPartner', 0 );
        self::setGameStateInitialValue( 'pointsTaker', 0 );
        self::setGameStateInitialValue( 'pointsDefense', 0 );
        self::setGameStateInitialValue( 'coinche', 0 );
        self::setGameStateInitialValue( 'trickColor', 0 );
        self::setGameStateInitialValue( 'strongestCardPlayed', 0 );
        self::setGameStateInitialValue( 'trickWinner', 0 );
        self::setGameStateInitialValue( 'turn_count', 1 );
        self::setGameStateInitialValue( 'passCount', 0 );
        self::setGameStateInitialValue( 'biddingDone', 0 );
        


        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        self::initStat( 'player', 'total_points', 0 ); 




        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
        $first_player_id = self::activeNextPlayer(); // This player will be the first player
        self::setGameStateInitialValue('first_player_id', $first_player_id);


        // Create cards
        $cards = array ();
        foreach ( $this->colors as $color_id => $color ) {
            if ($color_id > 4) {
				continue;
			}
            // spade, heart, diamond, club
            for ($value = 7; $value <= 14; $value ++) {
                //  7,8, ... K, A
                $cards [] = array ('type' => $color_id,'type_arg' => $value,'nbr' => 1);
            }
        }
        
        $this->cards->createCards( $cards, 'deck' );






        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );
        
        // Cards in the deck (ie dog)
        $result['deck'] = $this->cards->getCardsInLocation( 'deck' );

        // Cards played on the table
        $result['cardsontable'] = $this->cards->getCardsInLocation( 'cardsontable' );

        // Cards in the previous trick
        $result['cardsprevioustrick'] = $this->cards->getCardsInLocation( 'previousTrick' );

        // Game state values
        $result['first_player_id'] = self::getGameStateValue('first_player_id');
        $result['contractTaker'] = self::getGameStateValue('contractTaker');
        $result['contractPartner'] = self::getGameStateValue('contractPartner');
        $result['pointsTaker'] = self::getGameStateValue('pointsTaker');
        $result['pointsDefense'] = self::getGameStateValue('pointsDefense');
        $result['coinche'] = self::getGameStateValue('coinche');
        $result['contractValue'] = self::getGameStateValue('contractValue');
        $result['contractColor'] = self::getGameStateValue('contractColor');
        $result['contractDog'] = self::getGameStateValue('contractDog');
        $result['trickColor'] = self::getGameStateValue('trickColor');
        $result['strongestCardPlayed'] = self::getGameStateValue('strongestCardPlayed');
        $result['trickWinner'] = self::getGameStateValue('trickWinner');
        $result['coincher'] = self::getGameStateValue('coincher');
        $result['surCoincher'] = self::getGameStateValue('surCoincher');
        $result['turn_count'] = self::getGameStateValue('turn_count');
        $result['biddingDone'] = self::getGameStateValue( 'biddingDone' );
        $result['numberHands'] = self::getGameStateValue( 'numberHands' ); 
        $result['deadPlayer'] = self::getGameStateValue('deadPlayer');


        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $turn = self::getGameStateValue('turn_count');
        $hands = self::getGameStateValue( 'numberHands' );
        return (($turn-1)*100/$hands) ;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */


    function getPlayerName($player_id) {
        $players = self::loadPlayersBasicInfos();
        return $players[$player_id]['player_name'];
    }
    
        
    function getPlayerRelativePositions()
    {
        $result = array();
    
        $players = self::loadPlayersBasicInfos();
        $nextPlayer = self::createNextPlayerTable(array_keys($players));

        $current_player = self::getCurrentPlayerId();
        
        if(!isset($nextPlayer[$current_player])) {
            // Spectator mode: take any player for south
            $player_id = $nextPlayer[0];
        }
        else {
            // Normal mode: current player is on south
            $player_id = $current_player;
        }
        $result[$player_id] = 0;
        
        for($i=1; $i<count($players); $i++) {
            $player_id = $nextPlayer[$player_id];
            $result[$player_id] = $i;
        }
        return $result;
    }


    function _discard($discard_info) {
        // move then to the dog
        $this->cards->moveCards($discard_info['discarded_cards_ids'], 'dog');


        self::notifyAllPlayers(
          'log', 
            clienttranslate('${player_name} discarded.'), 
            array( 'player_name' => self::getActivePlayerName()  )
        );
    
        self::notifyPlayer(
            $discard_info['player_id'], 
            'discardCards', 
            '', 
            array(
                'discarded_cards_ids' => $discard_info['discarded_cards_ids']
        ));
    }
    


   	/**
	 * Returns array[color][arg] => strength according to current trick
	 */
	private function getCardsStrengths() {
		$trickColor = self::getGameStateValue('trickColor');
		$trumpColor = self::getGameStateValue('contractColor');

		$strengths = [];
		foreach ($this->colors as $colorId => $color) {
			if ($trumpColor == 5) {
				// All trump
				if ($colorId == $trickColor) {
					// Current trick color, stronger and specific order
					$strengths[$colorId] = [
						7 => 1,
						8 => 2,
						9 => 7,
						10 => 5,
						11 => 8,
						12 => 3,
						13 => 4,
						14 => 6,
					];
				} else {
					// No strength otherwise
					$strengths[$colorId] = [
						7 => 0,
						8 => 0,
						9 => 0,
						10 => 0,
						11 => 0,
						12 => 0,
						13 => 0,
						14 => 0,
					];
				}
			} else {
				// Normal or no trump
				if ($colorId == $trumpColor) {
					// Trump, stronger and specific order
					$strengths[$colorId] = [
						7 => 11,
						8 => 12,
						9 => 17,
						10 => 15,
						11 => 18,
						12 => 13,
						13 => 14,
						14 => 16,
					];
				} elseif ($colorId == $trickColor) {
					// Current trick color, stronger
					$strengths[$colorId] = [
						7 => 1,
						8 => 2,
						9 => 3,
						10 => 7,
						11 => 4,
						12 => 5,
						13 => 6,
						14 => 8,
					];
				} else {
					// No strength otherwise
					$strengths[$colorId] = [
						7 => 0,
						8 => 0,
						9 => 0,
						10 => 0,
						11 => 0,
						12 => 0,
						13 => 0,
						14 => 0,
					];
				}
			}
		}
		return $strengths;
	}

	/**
	 * Returns the strength of a card array
	 */
	private function getCardStrength($card) {
		$cardsStrengths = $this->getCardsStrengths();
		$cardStrength = $cardsStrengths[$card['type']][$card['type_arg']];
		return $cardStrength;
	}
    

    function assertCardPlay($cardId) {
        $card = $this->cards->getCard($cardId);
		$cardColor = $card['type'];
        $playerId = self::getActivePlayerId();
        $trickColor = self::getGameStateValue('trickColor');

        //first card of the trick
        if ($trickColor == 0) {
            self::setGameStateValue('trickWinner', $playerId);
            self::setGameStateValue('trickColor', $cardColor);

            $cardStrength = $this->getCardStrength($card);
            self::setGameStateValue('strongestCardPlayed', $cardStrength);
			return;
		}

        $cardStrength = $this->getCardStrength($card); //can't be earlier, it might be the first card
        $playerCards = $this->cards->getCardsInLocation('hand', $playerId);
        $contractColor = self::getGameStateValue('contractColor');
        $contractTaker= self::getGameStateValue('contractTaker');
        $contractPartner= self::getGameStateValue('contractPartner');
        $trickWinner = self::getGameStateValue('trickWinner');
        //determine trump color (0 if no trump, trick color is all trump)
        if ($contractColor==5) { $trumpColor = $trickColor;  }
        else if ($contractColor==6) { $trumpColor = 0;  }
        else {  $trumpColor = $contractColor;  }
        
        $strongestCardPlayed = self::getGameStateValue('strongestCardPlayed'); //strenght of the best card on the table
		$hasTrickColorInHand = false;           // has trick color in hand
        $hasTrumpInHand = false;                // has trump in hand
		$hasStrongerTrump = false;              // has bettertrump in hand
		$isPartnerTheStrongest = false;         // Is the partner the currently strongest of this trick ?

		// Loop on player's cards to set the 'inhand' values
		foreach ($playerCards as $playerCard) {
			if ($playerCard['type'] === $trickColor) {
				$hasTrickColorInHand = true;
			}
			$strength = $this->getCardStrength($playerCard);
			if ($playerCard['type'] === $trumpColor) {
				$hasTrumpInHand = true;
				if ($strength > $strongestCardPlayed) {
					$hasStrongerTrump = true;
				}
			}
		}

        //Determine if the team is winning
        if ($playerId == $contractPartner || $playerId == $contractTaker) {
            //the player is part of the attack
            if ($trickWinner == $contractPartner || $trickWinner == $contractTaker) {
                //the winner is part of the attack 
                $isPartnerTheStrongest = true;
            }
        }
        else {//the player is part of the defense
            if ($trickWinner != $contractPartner && $trickWinner != $contractTaker) {
                // the winner is not part of the attack
                $isPartnerTheStrongest = true;
            }
        }

        if ($hasTrickColorInHand) { //has the color
            if ($cardColor != $trickColor) {  //didn't play the color
                throw new BgaUserException(self::_('You must play the color'));
            }

            if ($trickColor == $trumpColor) {   //either TA or trump trick
                if ($hasStrongerTrump) {        // has better trump
                    if ($cardStrength < $strongestCardPlayed) {
                        throw new BgaUserException(self::_('You must play higher'));
                    }
                }
            }
        }

        else {      // doesn't have trick color
            if (!$isPartnerTheStrongest) { // partner is not winning    RK:can play smaller trump if partner is winning
                if ($hasTrumpInHand) {      // has trump
                    if ($cardColor != $trumpColor) {// didn't play a trump
                        throw new BgaUserException(self::_('You must play a trump'));
                    }
                    if ($hasStrongerTrump) {        // has a better trump
                        if ($cardStrength < $strongestCardPlayed) { // played a lower trump than the best one
                            throw new BgaUserException(self::_('You must play higher'));
                        }
                    }
                }
            }
        }
    

        if ($cardStrength > $strongestCardPlayed) { // played a better card
            self::setGameStateValue('trickWinner', $playerId);
            self::setGameStateValue('strongestCardPlayed', $cardStrength);
        }
    }

    function pointsTrump($value) { //value in 7-14
        if ($value == 7) {return 0;}
        if ($value == 8) {return 0;}
        if ($value == 9) {return 14;}
        if ($value == 10) {return 10;}
        if ($value == 11) {return 20;}
        if ($value == 12) {return 3;}
        if ($value == 13) {return 4;}
        if ($value == 14) {return 11;}
    }

    function pointsUsual($value) {
        if ($value == 7) {return 0;}
        if ($value == 8) {return 0;}
        if ($value == 9) {return 0;}
        if ($value == 10) {return 10;}
        if ($value == 11) {return 2;}
        if ($value == 12) {return 3;}
        if ($value == 13) {return 4;}
        if ($value == 14) {return 11;}
    }

	private function notifyScores() {
		$newScores = self::getCollectionFromDb(
			'SELECT player_id, player_score FROM player',
			true
		);
		self::notifyAllPlayers('newScores', '', [
			'newScores' => $newScores,
		]);
	}



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in taroinche.action.php)
    */

    
    function playCard($card_id) {
        self::checkAction("playCard");
        $player_id = self::getActivePlayerId();

        // Check Rules
		$this->assertCardPlay($card_id);

        // check if it's a belote card
        $beloteId = self::getGameStateValue('beloteId');
        if ($player_id == $beloteId) {
            $card = $this->cards->getCard($card_id);
	    	$cardColor = $card['type'];
            $cardValue = $card['type_arg'];
            $contractColor = self::getGameStateValue('contractColor');
            if ($cardColor == $contractColor      // it's a trump
                && ($cardValue == 12 || $cardValue == 13)) {
                // notify belote
                $belotePlayed = self::getGameStateValue('belotePlayed');
                if ($belotePlayed == 0) {
                    $msg = 'BELOTE !';
                }
                else {
                    $msg = 'Rebelote';
                }
                self::setGameStateValue('belotePlayed', $belotePlayed+1);
                self::notifyAllPlayers('playBelote', clienttranslate('${player_name} : ${msg}'),
                    [//'i18n' => ['msg'],
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                    'msg' => $msg
                    ]
                );
            }
        }


        $this->cards->moveCard($card_id, 'cardsontable', $player_id);
        $currentCard = $this->cards->getCard($card_id);

        // And notify
        self::notifyAllPlayers('playCard', '', array (
            'card_id' => $card_id,
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'value' => $currentCard ['type_arg'],
            'color' => $currentCard ['type'],)
        );

        // Next player
        $this->gamestate->nextState('');
    }



    function bid($value, $color, $dog, $partner){
        self::checkAction("bid");

        $player_id = self::getActivePlayerId();
        $previousValue = self::getGameStateValue('contractValue');
        $previousDog = self::getGameStateValue('contractDog');
        $previousValue = self::getGameStateValue('contractValue');
        $coinche = self::getGameStateValue('coinche');

        // Bid must go up
        if (($value + $dog) <=  ($previousValue + $previousDog)) {
			throw new BgaUserException(
                sprintf(
					self::_('You must bid higher than current bid (%s)'),
					$previousValue+$previousDog
				)
            );
		}

        // Capot only for TA/SA
        if ($color >= 5 && $value != 250) {
			throw new BgaUserException(self::_('SA and TA are for capot'));
		}

        // If coinche can only surcoinche
        if ($coinche==1) {
            throw new BgaUserException(self::_('You can only surcoinche or pass'));
        }

 		self::setGameStateValue('contractValue', $value);
		self::setGameStateValue('contractColor', $color);
        self::setGameStateValue('contractDog', $dog);
		self::setGameStateValue('contractTaker', $player_id);
        self::setGameStateValue('contractPartner', $partner);

        $dog_written = null ;
        if ($dog==1) {
            $dog_written = "";
        }
        if ($dog==2) {
            $dog_written = " WITHOUT";
        }
        if ($dog==3) {
            $dog_written = " AGAINST";
        }

        
		self::notifyAllPlayers(
			'updateBid',
			clienttranslate('${player_name} : ${value} ${color_written}${dog_translated}, with ${partner_name}' ), //
			[   'i18n' => ['dog_translated'],
                'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
                'value' => $value,
                'color' => $color,//_written,
                'color_written' => $this->colors[$color]['name']  ,
                'dog' => $dog_written,
                'dog_translated' => $dog_written,
                'partner' => $partner,
                'partner_name' => self::getPlayerName($partner),
            ]);
        
        // reset passCount
        self::setGameStateValue('passCount', 0);
		$this->gamestate->nextState('');
    }



    function pass() {
        self::checkAction("pass");
        $player_id = self::getActivePlayerId();
		// And notify
		self::notifyAllPlayers(
			'updatePass',
			clienttranslate('${player_name} passes'),
			[
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
			]
		);

		// Increase pass_count and next player
		$passCount = self::getGameStateValue('passCount');
		self::setGameStateValue('passCount', $passCount + 1);
		$this->gamestate->nextState('');
    }

    function coinche() {
        self::checkAction("coinche");
        $playerId = self::getActivePlayerId();		
        $bidValue = self::getGameStateValue('contractValue');
		$takerId = self::getGameStateValue('contractTaker');
		$partnerId = self::getGameStateValue('contractPartner');
        $coinche = self::getGameStateValue('coinche');
		if ($bidValue==0) {
			throw new BgaUserException(self::_('Cannot coinche nothing'));
		}
        if ($coinche == 0) {
            if ($playerId == $takerId) {
			    throw new BgaUserException(self::_('You are part of this bid'));
    		}
	    	if ($playerId == $partnerId) {
		    	throw new BgaUserException(	self::_('You are part of this bid'));
            }
		}

        if ($coinche == 0) {
            self::setGameStateValue('coincher', $playerId);
            self::setGameStateValue('passCount', 0);
            self::notifyAllPlayers(
                'updateCoinche',
                clienttranslate('${player_name} coinches !'),
                [
                    'player_id' => $playerId,
                    'player_name' => self::getActivePlayerName(),
                ]
            );
        }

        if ($coinche == 1) {
            self::setGameStateValue('surCoincher', $playerId);

            // surcoincher become taker
            if ($playerId == $partnerId) {
                self::setGameStateValue('contractTaker', $playerId);
                self::setGameStateValue('contractPartner', $takerId);

            }  

            self::notifyAllPlayers(
                'updateSurCoinche',
                clienttranslate('${player_name} surcoinches !!!'),
                [
                    'player_id' => $playerId,
                    'player_name' => self::getActivePlayerName(),
                    'taker' => self::getGameStateValue('contractTaker'),
                    'partner' => self::getGameStateValue('contractPartner'),
                ]
            );
        }

        // update coinche count
		self::setGameStateValue('coinche', $coinche+1);
		$this->gamestate->nextState('');
    }



    function discard($cards_ids) {
        self::checkAction("makeDog");
        $player_id = self::getActivePlayerId();

        if (count($cards_ids) != 2) {
            throw new BgaUserException(self::_("You must discard 2 cards exactly"));
        }
        array_unique($cards_ids);
        if (count($cards_ids) != 2) {
            throw new BgaUserException(self::_("You selected the same card at least twice"));
        }


        $player_id = self::getActivePlayerId();
        $player_hand = $this->cards->getCardsInLocation('hand', $player_id) ;

        foreach($cards_ids as $card_id) {
            $bInHand = false;
            foreach($player_hand as $card) {
                if($card['id'] != $card_id) {
                    continue;
                }
                $bInHand = true;
                
                $discarded_cards[] = $card;
                $discarded_cards_ids[] = $card_id;
            }
            if (!$bInHand) {
                throw new BgaUserException(self::_("A card is not in your hand"));
            }
        }
 
        $discard_info = array(
            'player_id' => $player_id,
            'discarded_cards' => $discarded_cards,
            'discarded_cards_ids' => $discarded_cards_ids,
        );

        self::_discard($discard_info);

		$this->gamestate->nextState('');
    }


    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    

    //TRICKS
    function stNewHand() {
        $this->cards->moveAllCardsInLocation(null, 'deck');
       // Shuffle deck  
        $this->cards->shuffle('deck');
        // Deal 6 cards to each players
        $players = self::loadPlayersBasicInfos();
        foreach ( $players as $player_id => $player ) {
            $cards = $this->cards->pickCards(6, 'deck', $player_id);

            self::notifyPlayer($player_id, 'newHand', '', array(
                'hand' => $cards,
                'first_player_id' => self::getGameStateValue('first_player_id')
            ));
        } 
        $this->cards->moveAllCardsInLocation('deck', 'dog');

        self::setGameStateValue( 'contractValue', 0 );
        self::setGameStateValue( 'contractColor', 0 );
        self::setGameStateValue( 'contractDog', 0 );
        self::setGameStateValue( 'contractTaker', 0 );
        self::setGameStateValue( 'contractPartner', 0 );
        self::setGameStateValue( 'pointsTaker', 0 );
        self::setGameStateValue( 'pointsDefense', 0 );
        self::setGameStateValue( 'coinche', 0 );
        self::setGameStateValue( 'trickColor', 0 );
        self::setGameStateValue( 'strongestCardPlayed', 0 );
        self::setGameStateValue( 'trickWinner', 0 );
        self::setGameStateValue( 'coincher', 0 );
        self::setGameStateValue( 'surCoincher', 0 );
        self::setGameStateValue( 'biddingDone', 0 ); 
        self::setGameStateValue( 'beloteId', 0 );
        self::setGameStateValue( 'belotePlayed', 0 );
        self::setGameStateValue( 'passCount', 0 );

        $first_player = self::getGameStateValue('first_player_id');
        $this->gamestate->changeActivePlayer($first_player);

        $this->gamestate->nextState("");
    }

    function stFirstTrick() {
        $first_player = self::getGameStateValue('first_player_id');
		$this->gamestate->changeActivePlayer($first_player);
        $player_ids =  array_keys($this->loadPlayersBasicInfos()); 

        self::notifyAllPlayers( // hide jean claude and previous bids
            'firstTrick',
            '',
            ['player_ids' => $player_ids,
            ]
        );

        //find belote
        $contractValue = self::getGameStateValue('contractValue');

        if ($contractValue != 250) {   //not capot  (=> color 1-4)
            $contractColor = self::getGameStateValue('contractColor');  
            $contractTaker = self::getGameStateValue('contractTaker');
            $cards_taker = $this->cards->getCardsInLocation('hand', $contractTaker) ;
            $beloteCards = 0;
            foreach($cards_taker as $card) {    //find belote cards in taker hand
                if  ($card['type'] == $contractColor      // it's a trump
                    && ($card['type_arg'] == 12 || $card['type_arg'] == 13) )  {
                        ++$beloteCards;
                    }
            }
            if ($beloteCards == 2) {
                self::setGameStateValue('beloteId', $contractTaker);
            }

            $contractPartner = self::getGameStateValue('contractPartner');
            if ($contractPartner != $contractTaker) {
                $beloteCards = 0;
                $cards_partner = $this->cards->getCardsInLocation('hand', $contractPartner) ;
                foreach($cards_partner as $card) {    //find belote cards in taker hand
                    if  ($card['type'] == $contractColor      // it's a trump
                        && ($card['type_arg'] == 12 || $card['type_arg'] == 13) )  {
                            ++$beloteCards;
                        }
                }
                if ($beloteCards == 2) {
                    self::setGameStateValue('beloteId', $contractPartner);
                }
            }
        }
        $this->gamestate->nextState("");
    }


	function stNextPlayer() {
		if ($this->cards->countCardInLocation('cardsontable') < 5) {
			// not the end of the trick, just active the next player
			$playerId = self::activeNextPlayer();
			self::giveExtraTime($playerId);
			$this->gamestate->nextState('nextPlayerTurn');
			return;
		}

		// 5 cards played, this is the end of the trick.
        $trickWinner = self::getGameStateValue("trickWinner");
        $cardsOnTable = $this->cards->getCardsInLocation('cardsontable');
		// Look up the winner of this trick.
		$players = self::loadPlayersBasicInfos();
        $contractValue = self::getGameStateValue("contractValue");

        //compute points of the trick
        $points = 0;
        if ($contractValue == 250) { $points =1 ;}
        else {  //not capot. Color has to be 1-4
            $trumpColor = self::getGameStateValue('contractColor');
    		foreach ($cardsOnTable as $card) {
                $cardColor = $card['type'];
                $cardValue = $card['type_arg'];
                if ($cardColor == $trumpColor) { //c'est un atout
                    $points = $points + $this->pointsTrump($cardValue); //OR $points += ...
                }
                else {
                    $points = $points + $this->pointsUsual($cardValue);
                }
            }
        }

        //move cards from previous trick to deck
        $this->cards->moveAllCardsInLocation(
			'previousTrick',
			'deck'
		);

		// Move all cards from table to previousTrick, with loc_arg the winner
		$this->cards->moveAllCardsInLocation(
			'cardsontable',
			'previousTrick',
			null,
			$trickWinner
		);

        self::setGameStateValue('trickColor', 0);


        $isLastTrick = $this->cards->countCardInLocation('hand') == 0 ;
        if ($isLastTrick && $contractValue != 250) { //add points to the trick (if not capot)
            $points = $points +10;
        }
 



        $contractTaker = self::getGameStateValue('contractTaker');
        $contractPartner = self::getGameStateValue('contractPartner');
        $pointsTaker = self::getGameStateValue('pointsTaker');
        $pointsDefense = self::getGameStateValue('pointsDefense');



        // add points to the winning team
        if ($trickWinner == $contractTaker || $trickWinner == $contractPartner) { //won by taker team
            self::setGameStateValue('pointsTaker', $pointsTaker + $points);
        }
        else {  //won by defense
            self::setGameStateValue('pointsDefense', $pointsDefense + $points);
        }

        //reset trick values
        self::setGameStateValue('trickColor', 0);
        self::setGameStateValue('strongestCardPlayed', 0);
        self::setGameStateValue('trickWinner', 0);
        

        //notify and update points
        if ($isLastTrick) { 			// End of the hand
            self::notifyAllPlayers('trickWin', 
                clienttranslate('${player_name} wins the last trick (+10 pts)'), [
			    'player_name' => $players[$trickWinner]['player_name'],
                'cardsOnTable' => $cardsOnTable,
                'pointsTaker' => self::getGameStateValue('pointsTaker'),
                'pointsDefense' => self::getGameStateValue('pointsDefense'),
		    ]);
			$this->gamestate->nextState('endHand');
        }

        else {             // End of the trick
            self::notifyAllPlayers('trickWin', 
                '', //'${player_name} wins the trick (temp)', 
                ['player_name' => $players[$trickWinner]['player_name'],
                'cardsOnTable' => $cardsOnTable,
                'pointsTaker' => self::getGameStateValue('pointsTaker'),
                'pointsDefense' => self::getGameStateValue('pointsDefense'),
    		]);

            // autolose capot
            if ($contractValue == 250 && self::getGameStateValue('pointsDefense') != 0) {
                $this->gamestate->nextState('endHand');
                return;
            }


            $this->gamestate->changeActivePlayer($trickWinner);
			$this->gamestate->nextState('nextTrick');
		}
	}



    function stEndHand() { 
        // add dog and belote point, check contract result
        $dogCards = $this->cards->getCardsInLocation( 'dog');
        $contractValue = self::getGameStateValue('contractValue');
        $contractDog = self::getGameStateValue('contractDog');
        $pointsTaker = self::getGameStateValue('pointsTaker');
        $pointsDefense = self::getGameStateValue('pointsDefense');


        $points = 0;
        if ($contractValue == 250) { //capot
            $contractDone = ($pointsTaker == 6);
        }
        else {  //not capot. Color has to be 1-4
            $trumpColor = self::getGameStateValue('contractColor');
    		//get dog points
            foreach ($dogCards as $card) {
                $cardColor = $card['type'];
                $cardValue = $card['type_arg'];
                if ($cardColor == $trumpColor) { //c'est un atout
                    $points = $points + $this->pointsTrump($cardValue); //OR $points += ...
                }
                else {
                    $points = $points + $this->pointsUsual($cardValue);
                }
            }

            // add the points to the correct team
            if ($contractDog != 3) {  // WITH or WITHOUT
                self::setGameStateValue('pointsTaker', $pointsTaker + $points);
                $pointsTaker = self::getGameStateValue('pointsTaker');                
            }
            else {  // AGAINST
                self::setGameStateValue('pointsDefense', $pointsDefense + $points);
            }

            //add belote points
            $beloteId = self::getGameStateValue('beloteId');
            if ($beloteId != 0) {
                self::setGameStateValue('pointsTaker', $pointsTaker + 20);
            }

            // check bid result
            $pointsTaker = self::getGameStateValue('pointsTaker');                
            $contractDone = ($pointsTaker >= $contractValue);
        }


 
        if ($contractValue == 250) {
            $contractValueDisplay = 6 ;
        }
        else {
            $contractValueDisplay = $contractValue;
        }

        // notify results
        if ($contractDone) {
            self::notifyAllPlayers(
                'endHand', 
                clienttranslate('Bid successful (${pointsTaker}/${contractValueDisplay})'), 
                ['pointsTaker' => self::getGameStateValue('pointsTaker'),
                'pointsDefense' => self::getGameStateValue('pointsDefense'),
                'contractValue' => $contractValue,
                'contractValueDisplay' => $contractValueDisplay
                ]
            );
        }

        else {
            self::notifyAllPlayers(
                'endHand', 
                clienttranslate('Bid failed (${pointsTaker}/${contractValueDisplay})'), 
                ['pointsTaker' => self::getGameStateValue('pointsTaker'),
                'pointsDefense' => self::getGameStateValue('pointsDefense'),
                'contractValue' => $contractValue,
                'contractValueDisplay' => $contractValueDisplay
                ]
            );
        }


        // change scores        
        $players = self::loadPlayersBasicInfos();
        $contractTaker = self::getGameStateValue('contractTaker');
        $contractPartner = self::getGameStateValue('contractPartner');
        $mult = 1;
        // multiplier according to coinche value
        $coinche = self::getGameStateValue('coinche');
        if ($coinche == 1) {
            $mult = 2 ;
        }
        if ($coinche == 2) {
            $mult = 4;
        }
        // win = +-1 depending on win loss
        if ( $contractDone) {$winMult = 1;}
        else {$winMult = -1;}
        //normalise contract value (82 to 80)
        $contractValueReg = $contractValue;
        if ($contractValue == 82) {$contractValueReg = 80;}
        // array of elo to add
        $player_points = array();
        foreach($players as $player_id => $player) {
            if ($player_id == $contractTaker && $player_id == $contractPartner) { //bid 1v4
                $player_points[$player_id] = $winMult * 4*$mult*$contractValueReg;
            }
            else if ($player_id == $contractTaker) {
                $player_points[$player_id] = $winMult *2*$mult*$contractValueReg;
            }
            else if ($player_id == $contractPartner) {
                $player_points[$player_id] = $winMult * $mult*$contractValueReg;
            }
            else {
                $player_points[$player_id] = -$winMult * $mult*$contractValueReg;
            }
        }

        foreach($player_points as $player_id => $points) {
            self::DbQuery(sprintf("UPDATE player SET player_score = player_score + %d WHERE player_id = '%s'", $points, $player_id));

            // update total points stats
            self::incStat($points, 'total_points', $player_id );
            //KEEP TRACK FOR EACH HAND ? 
        }
	
        
		// Notify score info
		$this->notifyScores();

        // TODO : update all stats


        // $game_length = self::getGameStateValue('game_length');
        $turn_count = self::getGameStateValue('turn_count');
        $numberHands = self::getGameStateValue('numberHands');

        if ($turn_count == $numberHands) {  
            // notify results
            $this->gamestate->nextState("gameEnd");
        }
        else {
            self::incGameStateValue('turn_count', 1);
            // change first player
            $first_player_id = self::getGameStateValue('first_player_id');
            $this->gamestate->changeActivePlayer($first_player_id);
            self::setGameStateValue('first_player_id', self::activeNextPlayer());
            $this->gamestate->nextState('newHand');

        }       
    }


    //BIDDING

    function stNextPlayerBid() {
        $coinche = self::getGameStateValue('coinche') ;

        if ($coinche == 2) {
            $this->gamestate->nextState('endBidding');
            return;
        }

        if ($coinche == 1) {
            $playerId = self::activeNextPlayer();
            $passCount = self::getGameStateValue('passCount');

            if ($passCount == 2) {  // Taker and partner passed
                $this->gamestate->nextState('endBidding');
                return;
            }
            // find next teammate that can surcoinche  
            while ($playerId != self::getGameStateValue('contractTaker') && $playerId != self::getGameStateValue('contractPartner')   ) {
                $playerId = self::activeNextPlayer();
            }
            $this->gamestate->nextState('playerBid'); 
            return;
        }

        // find next 
        $passCount = self::getGameStateValue('passCount');
        $contractValue = self::getGameStateValue('contractValue') ;
        
        if ($contractValue == 0 && $passCount == 5) {            
            self::incGameStateValue('turn_count', 1);
            self::notifyAllPlayers(
                'allPassNoBid',
                clienttranslate('Everybody pass, new hand'),
                []
            );
            // change first player 
            $first_player_id = self::getGameStateValue('first_player_id');
            $this->gamestate->changeActivePlayer($first_player_id);
            self::setGameStateValue('first_player_id', self::activeNextPlayer());
            $this->gamestate->nextState('newHand');
            return;
        }

        if ($passCount == 4 && $contractValue != 0) {
            $this->gamestate->nextState('endBidding');
            return;
        }

        
		$playerId = self::activeNextPlayer();
		self::giveExtraTime($playerId);
		$this->gamestate->nextState('playerBid');

    }

    function stEndBidding() {
        self::setGameStateValue('biddingDone', 1);
        $dog = self::getGameStateValue('contractDog') ;
        $color = self::getGameStateValue('contractColor') ;

        self::notifyAllPlayers(
			'endBidding',
			'', //give color for ordering
			[
				'player_name' => self::getActivePlayerName(),
                'color' => $color
            ]
        );

        if ($dog==1) {
            $this->gamestate->nextState('makeDog');
            return;
        }


        $this->gamestate->nextState('firstTrick');
    }


    //DOGGING
    function stActivateDoger() {
    	$contractTaker = self::getGameStateValue('contractTaker');
		$this->gamestate->changeActivePlayer($contractTaker);
        self::giveExtraTime($contractTaker);

        // cards in the dog
        $dogCards = $this->cards->getCardsInLocation( 'dog');

        // deal remaining deck card to player hand (so they are shown if reloading)
        $this->cards->pickCards(2, 'dog', $contractTaker);

        // move them to player hand and previousTrick panel
        self::notifyAllPlayers(
			'updateDog',
			clienttranslate('${player_name} makes his dog' ), //
			[
				'player_name' => self::getActivePlayerName(),
                'dogCards' => $dogCards,
                'taker' => self::getGameStateValue('contractTaker') 
            ]
        );
        
        $this->gamestate->nextState('');
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
