<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Taroinche implementation : © Côme Dattin <c.dattin@gmail.com>
 * Implemented using parts of the coinche and tarot games

 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * Taroinche game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "successful_bids_number" => array("id"=> 11,
                    "name" => totranslate("Number of successful bids"),
                    "type" => "int" ),

        "failed_bids_number" => array("id"=> 12,
                    "name" => totranslate("Number of failed bids"),
                    "type" => "int" ),

        "successful_capot_number" => array("id"=> 13,
                    "name" => totranslate("Number of successful capots"),
                    "type" => "int" ),

        "failed_capot_number" => array("id"=> 14,
                    "name" => totranslate("Number of failed capots"),
                    "type" => "int" ),
        
        "coinched_succesful_number" => array("id"=> 15,
                    "name" => totranslate("Number of coinched bids that were successful"),
                    "type" => "int" ),

        "coinched_failed_number" => array("id"=> 16,
                    "name" => totranslate("Number of coinched bids that were failed"),
                    "type" => "int" ),
    ),
    
    // Statistics existing for each player
    "player" => array(

        "total_points" => array("id"=> 30,
                    "name" => totranslate("Number of points"),
                    "type" => "int" ),

        "hands_number" => array("id"=> 31,
                    "name" => totranslate("Number of hands played"),
                    "type" => "int" ),

        "taker_number" => array("id"=> 32,
                    "name" => totranslate("Number of hands you took the bid"),
                    "type" => "int" ),

        "partner_number" => array("id"=> 33,
                    "name" => totranslate("Number of hands you were partner of the bid"),
                    "type" => "int" ),
                    
        "successful_bids_number" => array("id"=> 34,
                    "name" => totranslate("Number of successful bids"),
                    "type" => "int" ),

        "failed_bids_number" => array("id"=> 35,
                    "name" => totranslate("Number of failed bids"),
                    "type" => "int" ),

        "successful_defense_number" => array("id"=> 36,
                    "name" => totranslate("Number of successful defenses"),
                    "type" => "int" ),

        "failed_defense_number" => array("id"=> 37,
                    "name" => totranslate("Number of failed defenses"),
                    "type" => "int" ),

        "successful_capot_number" => array("id"=> 38,
                    "name" => totranslate("Number of successful capots"),
                    "type" => "int" ),

        "failed_capot_number" => array("id"=> 39,
                    "name" => totranslate("Number of failed capots"),
                    "type" => "int" ),

        "coinche_worked_number" => array("id"=> 40,
                    "name" => totranslate("Number of time a contract you coinched failed"),
                    "type" => "int" ),

        "coinche_failed_number" => array("id"=> 41,
                    "name" => totranslate("Number of time a contract you coinched was successful"),
                    "type" => "int" ),

    )

);
