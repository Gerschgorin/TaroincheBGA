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
 * states.inc.php
 *
 * Taroinche game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 20 )
    ),
    
    
    //NEW HAND
	20 => [
		'name' => 'newHand',
		'description' => '',
		'type' => 'game',
		'action' => 'stNewHand',
		'updateGameProgression' => true,
		'transitions' => ['' => 22],
	],
    



    // player can bid
    22 => [
		'name' => 'playerBid',
		'description' => clienttranslate('${actplayer} can bid'),
		'descriptionmyturn' => clienttranslate('${you} can bid'),
		'type' => 'activeplayer',
		'possibleactions' => ['bid', 'pass', 'coinche'],
		'transitions' => ['' => 23],
	],
	
    // choosing next bider
    23 => [
		'name' => 'nextPlayerBid',
		'description' => '',
		'type' => 'game',
		'action' => 'stNextPlayerBid',
		'transitions' => [
			'playerBid' => 22,
			'newHand' => 20,
			'endBidding' => 24,
		],
	],

    // final bid
    24 => [
		'name' => 'endBidding',
		'description' => '',
		'type' => 'game',
		'action' => 'stEndBidding',
		'transitions' => [
			'makeDog' => 26, 
            'firstTrick' => 30,
		],
	],


    //DOG MAKING
    // activate the taker
    26 => [
        'name' => 'dogMaker',
        'description' => '',
        'type' => 'game',
        'action' => 'stActivateDoger',
        'transitions' => [ '' => 27]
    ],

    // making the dog
    27 => [
        "name" => "dogMaking",
        "description" => clienttranslate('${actplayer} must discard 2 cards'),
        "descriptionmyturn" => clienttranslate('${you} must discard 2 cards'),
        "type" => "activeplayer",
        //"args" => "argDiscardCards",
        "possibleactions" => ['makeDog'],
        "transitions" => ['' => 30]
    ],


    //TRICK
    // activate Jean Claude (utile?? fuse with next trick?)
    30 => [	
    'name' => 'firstTrick',
    'description' => '',
    'type' => 'game',
    'action' => 'stFirstTrick',
    'transitions' => ['' => 31],
    ],

    // playing a card
    31 => [
		'name' => 'playerTurn',
		'description' => clienttranslate('${actplayer} must play a card'),
		'descriptionmyturn' => clienttranslate('${you} must play a card'),
		'type' => 'activeplayer',
		'possibleactions' => ['playCard'],
		'transitions' => ['' => 32],
		//'args' => 'argPlayerTurn',
	],

    // choosing next player
    32 => [
		'name' => 'nextPlayer',
		'description' => '',
		'type' => 'game',
		'action' => 'stNextPlayer',
		'transitions' => [
			'nextPlayerTurn' => 31,
			'nextTrick' => 31,
			'endHand' => 50,
		],
	],



    50 => [
		'name' => 'endHand',
		'description' => '',
		'type' => 'game',
		'action' => 'stEndHand',
		'transitions' => ['newHand' => 20, 'gameEnd' => 99],
	],




    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



