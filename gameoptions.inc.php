<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Taroinche implementation : © Côme Dattin <c.dattin@gmail.com>
 * Implemented using parts of the coinche and tarot games

 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

$game_options = array(

    100 => array(
        'name' => totranslate('Number of hands'),    
        'values' => array(
                    5 => array( 'name' => totranslate('5 hands') ),
                    10 => array( 'name' => totranslate('10 hands') ),
                    15 => array( 'name' => totranslate('15 hands') ),
                    20 => array( 'name' => totranslate('20 hands') ),

                ),
        'default' => 5
    ),

/*    101 => array(
        'name' => totranslate('Number of players'),    
        'values' => array(
                    // A simple value for this option:
                    5 => array( 'name' => totranslate('5 players') ),
                    6 => array( 'name' => totranslate('6 players') ),
                ),
        'default' => 5
    ),
*/


);


