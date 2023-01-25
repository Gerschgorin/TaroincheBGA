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
 * taroinche.js
 *
 * Taroinche user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.taroinche", ebg.core.gamegui, {
        constructor: function(){
            console.log('taroinche constructor');

            // Here, you can init the global variables of your user interface

            this.cardwidth =72;
            this.cardheight = 96;
            this.selection_mode = null;

            
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                
                //ICONS IN RIGHT PANEL
                //dojo.place("<div class='panel_container' id='panel_container_" + player_id +  
                //           "<div class='taker_spot_in_panel' id='taker_spot_in_panel_" + player_id + "'></div>" +
                //           "<div class='starter_spot_in_panel' id='starter_spot_in_panel_" + player_id + "'></div></div>", 'player_board_' + player_id);
                         
            }


            // Number of turns
            this.turn_counter = new ebg.counter();
            this.turn_counter.create('turn_counter');
            this.turn_counter.setValue(gamedatas.turn_count);
            $('total_turns').innerHTML = '/' + gamedatas.numberHands;


			this.playerBid = {
				color: null,
				value: null,
                dog:null,
                partner:null
			}
            
            if (gamedatas.biddingDone==0) {  //jean-claude icon
            // only if betting happening
                dojo.place('<div id="icon_starter" class="icon_starter"></div>', 'starter_icon_spot_' + gamedatas.first_player_id);
            // ICON ON RIGHT PANEL
            //dojo.place('<div id="icon_starter_in_panel" class="icon_starter"></div>', 'starter_spot_in_panel_' + gamedatas.first_player_id);
            }

            

            if (gamedatas.contractTaker != 0) {
            
            //team icons
            dojo.place('<div id="icon_taker" class="icon_taker"></div>', 'taker_icon_spot_' + gamedatas.contractTaker);
            dojo.place('<div id="icon_partner" class="icon_partner"></div>', 'partner_icon_spot_' + gamedatas.contractPartner);

            // color of the contract
            this.contractColor = gamedatas.contractColor ;
            if (this.contractColor == 1) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/spade.svg"/>';
                this.displayColor = 'spade'}
            if (this.contractColor == 2) 
                {var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/heart.svg"/>';
                this.displayColor = 'heart'}
            if (this.contractColor == 3) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/club.svg"/>';
                this.displayColor = 'club'}
            if (this.contractColor == 4) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/diamond.svg"/>';
                this.displayColor = 'diamond'}
            if (this.contractColor == 5) {
//                var contractPicture = 'SA';
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/alltrump.svg"/>';
                this.displayColor = 'all trump'}
            if (this.contractColor == 6) { 
//                var contractPicture = 'TA';
                var contractPicture = '<img  class=card-color-icon--size16 src="'+g_gamethemeurl+'img/notrump.svg"/>';
                this.displayColor = 'no trump'}

            this.contractDog =  gamedatas.contractDog;
            this.displayDog = '';
            if (this.contractDog == 2) {
                this.displayDog = 'WITHOUT';
             }
             if (this.contractDog == 3) {
                this.displayDog = 'AGAINST';
             }

            $('bid_indicator').innerHTML = this.format_string_recursive('${value} ${picture} (${color}) ${dog}', {'value': gamedatas.contractValue, 'picture': contractPicture, 'color': this.displayColor, 'dog':this.displayDog});
            }

            $('points_teams').innerHTML = '(' + gamedatas.pointsTaker + '|' +  gamedatas.pointsDefense + ')';


            //Add coincher and surcoincher icons
            if (gamedatas.coinche !=0) {
                dojo.place('<div id="icon_coincher" class="icon_coincher"></div>', 'coincher_icon_spot_' + gamedatas.coincher);
            }
            if (gamedatas.coinche == 2) {
                dojo.place('<div id="icon_surcoincher" class="icon_surcoincher"></div>', 'surcoincher_icon_spot_' + gamedatas.surCoincher);
            }


            // Player hand
            this.playerHand = new ebg.stock(); // new stock object for hand
            this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight);
            this.playerHand.image_items_per_row = 8; // 8 images per row

            this.previousTrick = new ebg.stock();
            this.previousTrick.create( this, $( 'previous_trick' ), this.cardwidth, this.cardheight);
            this.previousTrick.image_items_per_row = 8;



            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' 
            );

            // Create cards types:
            for (var color = 1; color <= 4; color++) {
                for (var value = 7; value <= 14; value++) {
                    // Build card type id
                    var card_type_id = this.getCardUniqueId(color, value);  //start at 0 !
                    this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                    this.previousTrick.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                }
            }


            // order cards
            if (gamedatas.biddingDone == 0) {   // still bidding. order as trumps
                this.orderTrump(5);
            }
            else {      // contract decided
                this.orderTrump(gamedatas.contractColor);
            }

            // Cards in player's hand
            for ( var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }


            // THE CARDS OF THE DOG DISAPPEAR IF F5, but it's okay


            // Cards played on table
            for (i in this.gamedatas.cardsontable) {
                var card = this.gamedatas.cardsontable[i];
                var color = card.type;
                var value = card.type_arg;
                var player_id = card.location_arg;
                this.playCardOnTable(player_id, color, value, card.id);
            }
            
            // Cards in previous trick
            for (i in this.gamedatas.cardsprevioustrick) {
                var card = this.gamedatas.cardsprevioustrick[i];
                var color = card.type;
                var value = card.type_arg;
                var player_id = card.location_arg;
                this.previousTrick.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }

			// Buttons of bidPanel
			this.connectClass('bidPanel__btn', 'onclick', 'onBidPanelBtnClick')
			this.connectClass('.bidPanel__btn--coinche',	'onclick','onCoincheBtnClick')
            
            // connect player 
            this.connectClass('.playertable', 'onclick', 'onPlayerClick')


            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            var isBidPanelVisible = false
            var selection_mode=0;

            switch( stateName )
            {   case 'playerBid':
					if (this.isCurrentPlayerActive()) {
						isBidPanelVisible = true
					}
                    
				break;

                case 'dogMaking':
                    if (this.isCurrentPlayerActive()) {
                        selection_mode = 2; // Several cards can be selected (for discard)
                    }
                    else {
                        selection_mode = 0; // No card can be selected by that player
                    }
                break;

                case 'playerTurn':
                    if (this.isCurrentPlayerActive()) {
                        selection_mode = 1;
                    }
                    else {
                        selection_mode = 0;
                    }
                break;
            }

            if (isBidPanelVisible) {
                dojo.query('.bidPanel').addClass('bidPanel--visible')
                this.scrollBidPanelValues(0)
            } else {
                dojo.query('.bidPanel').removeClass('bidPanel--visible')
            }      

            if (selection_mode != this.selection_mode) {
                this.selection_mode = selection_mode;
                this.playerHand.setSelectionMode(selection_mode);
            }

            


        },



        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {

            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName ){
                    case 'dogMaking':
                        this.addActionButton('discard', _("Discard"), 'onButtonClickForDiscard'); 
                    //break;
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */


        // Get card unique identifier based on its color and value
        getCardUniqueId : function(color, value) {
            return (color - 1) * 8 + (value - 7);
        },

        playCardOnTable : function(player_id, color, value, card_id) {
            // player_id => direction
            dojo.place(this.format_block('jstpl_cardontable', {
                x : this.cardwidth * (value - 7),
                y : this.cardheight * (color - 1),
                player_id : player_id
            }), 'playertablecard_' + player_id);

            if (player_id != this.player_id) {
                // Some opponent played a card
                // Move card from player panel
                this.placeOnObject('cardontable_' + player_id, 'overall_player_board_' + player_id);
            } else {
                // You played a card. If it exists in your hand, move card from there and remove
                // corresponding item

                if ($('myhand_item_' + card_id)) {
                    this.placeOnObject('cardontable_' + player_id, 'myhand_item_' + card_id);
                    this.playerHand.removeFromStockById(card_id);
                }
            }

            // In any case: move it to its final destination
            this.slideToObject('cardontable_' + player_id, 'playertablecard_' + player_id).play();
        },



		scrollBidPanelValues: function(where) {
			var tickSize = 54
			var listEl = dojo.query('.bidPanel__values-list')[0]
			var currentScroll = listEl.scrollLeft
			if (where == 'right') {
				currentScroll += tickSize
			} else if (where == 'left') {
				currentScroll -= tickSize
			} else {
				currentScroll = where
			}
			currentScroll = tickSize * Math.ceil(currentScroll / tickSize)
			listEl.scrollLeft = currentScroll
		},

        sendPlayerBid: function() {
			if (!this.playerBid.value && this.playerBid.color && this.playerBid.dog && this.playerBid.partner) { 
                    return 	
                }

			this.ajaxcall(
				'/' + this.game_name + '/' + this.game_name + '/' + 'bid' + '.html',
				{
					value: this.playerBid.value,
					color: this.playerBid.color,
                    dog: this.playerBid.dog,
                    partner: this.playerBid.partner,
					lock: true
				},
				this,
				function(result) {
					this.updatePlayerBid(true);
				},
				function(is_error) {
					this.updatePlayerBid(true)
				}
			)
 		},


        updatePlayerBid: function(clearValue) {
			if (clearValue) {
				this.playerBid = {
					color: null,
					value: null
				}
			}
			dojo.query('.bidPanel__btn').removeClass('bidPanel__btn--selected')
			if (this.playerBid.value) {
				dojo
					.query(
						'.bidPanel__btn--value[data-value="' + this.playerBid.value + '"]'
					)
					.addClass('bidPanel__btn--selected')
			}
			if (this.playerBid.color) {
				dojo
					.query(
						'.bidPanel__btn--color[data-color="' + this.playerBid.color + '"]'
					)
					.addClass('bidPanel__btn--selected')
			}
            if (this.playerBid.dog) {
				dojo
					.query(
						'.bidPanel__btn--dog[data-value="' + this.playerBid.dog + '"]'
					)
					.addClass('bidPanel__btn--selected')
			}

		},

        updateBid: function(contractTaker, contractValue, contractColor, contractColorWritten, contractDog, contractPartner) { 
            //icon of the contract
            if (contractColor == 1) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/spade.svg"/>';
            }
            if (contractColor == 2) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/heart.svg"/>';
            }
            if (contractColor == 3) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/club.svg"/>';
            }
            if (contractColor == 4) {
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/diamond.svg"/>';
            }
            if (contractColor == 5) {
//                var contractPicture = 'TA';
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/alltrump.svg"/>';
            }
            if (contractColor == 6) {
//                var contractPicture = 'SA';
                var contractPicture = '<img class=card-color-icon--size16 src="'+g_gamethemeurl+'img/notrump.svg"/>';
            }
            // update current bid in right panel
            $('bid_indicator').innerHTML = this.format_string_recursive('${value} ${picture} (${color}) ${dog}', {'value': contractValue, 'picture': contractPicture, 'color': contractColorWritten, 'dog':contractDog});

            // update bid on player panel
            oldhtml = $('player_bid_spot_' + contractTaker).innerHTML;
            newbid = this.format_string_recursive('${value} ${picture} ${dog}', {'value': contractValue, 'picture': contractPicture, 'dog':contractDog});
            newhtml = oldhtml + newbid + '<br>';
            $('player_bid_spot_' + contractTaker).innerHTML = newhtml;



			// Update bid panel buttons
            dojo.query('.bidPanel__btn--value').forEach(function(el) {
			    var value = +el.getAttribute('data-value')
			    if (!contractValue || contractValue < +value ||  contractValue == value
//                    || ((contractDog=='' || contractDog=='WITHOUT') && (contractValue == value))   //SOMEHOW FAILS
                    ) { //show icons
    		    	el.classList.remove('bidPanel__btn--hidden')
			    } else {
    				el.classList.add('bidPanel__btn--hidden')
			    }
		    })


            // hide previous team icons
            dojo.query('.icon_taker').forEach(dojo.destroy);
            dojo.query('.icon_partner').forEach(dojo.destroy);

            //show new team icons
            dojo.place('<div id="icon_taker" class="icon_taker"></div>', 'taker_icon_spot_' + contractTaker);
            dojo.place('<div id="icon_partner" class="icon_partner"></div>', 'partner_icon_spot_' + contractPartner);
        },


        updateCoinche: function(coincher) { 
            //add icon
            dojo.place('<div id="icon_coincher" class="icon_coincher"></div>', 'coincher_icon_spot_' + coincher);
        },

        updateSurCoinche: function(contractTaker, contractPartner) { 
            // hide previous team icons
            dojo.query('.icon_taker').forEach(dojo.destroy);
            dojo.query('.icon_partner').forEach(dojo.destroy);


            //show new team icons
            dojo.place('<div id="icon_taker" class="icon_taker"></div>', 'taker_icon_spot_' + contractTaker);
            dojo.place('<div id="icon_partner" class="icon_partner"></div>', 'partner_icon_spot_' + contractPartner);

            // add surcoinche icons
            dojo.place('<div id="icon_surcoincher" class="icon_surcoincher"></div>', 'surcoincher_icon_spot_' + contractTaker);        
        },



        orderTrump: function($color) { 
            if ($color == 5) {      //all trump
                for (var i = 0; i <= 3; i++) { //for each color
                    this.playerHand.changeItemsWeight({     //card_id start at 0 
                        [0+8*i]: 0+8*i,       // 7   inutile 
                        [1+8*i]: 1+8*i,       // 8   inutile
                        [2+8*i]: 6+8*i,       // 9 
                        [3+8*i]: 4+8*i,       // 10 
                        [4+8*i]: 7+8*i,       // V
                        [5+8*i]: 2+8*i,       // D
                        [6+8*i]: 3+8*i,       // R                    
                        [7+8*i]: 5+8*i,       // A                    
                    });
                }
            }

            if ($color == 6) {     // no trump
                for (var i = 0; i <= 3; i++) { //for each color
                    this.playerHand.changeItemsWeight({
                        [0+8*i]: 0+8*i,       // 7   inutile 
                        [1+8*i]: 1+8*i,       // 8   inutile
                        [2+8*i]: 2+8*i,       // 9 
                        [3+8*i]: 6+8*i,       // 10 
                        [4+8*i]: 3+8*i,       // V
                        [5+8*i]: 4+8*i,       // D
                        [6+8*i]: 5+8*i,       // R                    
                        [7+8*i]: 7+8*i,       // R                    
                    });
                }
            }
            
            if (1 <= $color && $color <= 4) {   // order trumps for color (in 1-4)
                for (var i = 0; i <= 3; i++) { //order no trump for each color
                    this.playerHand.changeItemsWeight({
                        [0+8*i]: 0+8*i,       // 7   inutile 
                        [1+8*i]: 1+8*i,       // 8   inutile
                        [2+8*i]: 2+8*i,       // 9 
                        [3+8*i]: 6+8*i,       // 10 
                        [4+8*i]: 3+8*i,       // V
                        [5+8*i]: 4+8*i,       // D
                        [6+8*i]: 5+8*i,       // R                    
                        [7+8*i]: 7+8*i,       // A                    
                    });
                }
                var i = $color-1;
                this.playerHand.changeItemsWeight({     // trump order for color
                    [0+8*i]: 0+8*i,       // 7   inutile 
                    [1+8*i]: 1+8*i,       // 8   inutile
                    [2+8*i]: 6+8*i,       // 9 
                    [3+8*i]: 4+8*i,       // 10 
                    [4+8*i]: 7+8*i,       // V
                    [5+8*i]: 2+8*i,       // D
                    [6+8*i]: 3+8*i,       // R                    
                    [7+8*i]: 5+8*i,       // A                    
                });
            }
        },
        


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/taroinche/taroinche/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */


        onPlayerHandSelectionChanged : function() {
            var items = this.playerHand.getSelectedItems();

            if (items.length > 0) {
                if (this.checkAction('playCard', true)) {     // Can play a card

                    var card_id = items[0].id;
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/playCard.html", {
                        id: card_id,
                        lock: true
                    }, this, function(result) {
                    }, function(is_error) {
                    });

                    this.playerHand.unselectAll();
                } else if (this.checkAction('makeDog')) {
                    // let the player select some cards
                } else {
                    this.playerHand.unselectAll();
                }
            }
        },


		onBidPanelBtnClick: function(e) {
			e.preventDefault()
			e.stopPropagation()
			var target = e.currentTarget


			if (target.classList.contains('bidPanel__btn--value-left')) {
				this.scrollBidPanelValues('left')
				return
			}
			if (target.classList.contains('bidPanel__btn--value-right')) {
				this.scrollBidPanelValues('right')
				return
			}

			if (target.classList.contains('bidPanel__btn--pass')) {
				this.updatePlayerBid(true)
				this.onPlayerPass()
				return
			}
			if (target.classList.contains('bidPanel__btn--color')) {
				this.playerBid.color = target.getAttribute('data-color')
			}
			if (target.classList.contains('bidPanel__btn--value')) {
				this.playerBid.value = target.getAttribute('data-value')
			}
            if (target.classList.contains('bidPanel__btn--dog')) {
				this.playerBid.dog = target.getAttribute('data-value')
			}
			this.updatePlayerBid(false)
			if (this.playerBid.value && this.playerBid.color && this.playerBid.dog && this.playerBid.partner) {
				this.sendPlayerBid();
			}
		},


        onPlayerClick: function(e){
            e.preventDefault()
			e.stopPropagation()
			var target = e.currentTarget

            if (target.classList.contains('playertable')) {
				this.playerBid.partner = target.getAttribute('data-player')
			}

            if (this.playerBid.value && this.playerBid.color && this.playerBid.dog && this.playerBid.partner) {
                this.sendPlayerBid()
            }   	


        },

		onCoincheBtnClick: function(e) {
			this.onPlayerCoinche()
		},


		onPlayerPass: function() {
			if (this.checkAction('pass')) {
				this.ajaxcall(
					'/' + this.game_name + '/' + this.game_name + '/' + 'pass' + '.html',
					{
						lock: true
					},
					this,
					function(result) {},
					function(is_error) {}
				)
			}
		},


		onPlayerCoinche: function() {
			if (this.checkPossibleActions('coinche')) {
				this.ajaxcall(
					'/' +
						this.game_name +
						'/' +
						this.game_name +
						'/' +
						'coinche' +
						'.html',
					{
						lock: true
					},
					this,
					function(result) {},
					function(is_error) {}
				)
			}
		},



        onButtonClickForDiscard: function() {
            if (this.checkAction('makeDog')) {
                var items = this.playerHand.getSelectedItems();
                var cards_ids = [];
                for(var i in items) {
                    cards_ids.push(items[i].id);
                }
                cards_ids = cards_ids.join(',');
                    
                this.ajaxcall(
                    '/' + this.game_name + '/' + this.game_name + '/' + 'makeDog' + '.html', { 
                    'cards_ids': cards_ids,
                    lock: true 
                }, this, function(result) {}, function(is_error) {});
            }
        },

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your taroinche.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            dojo.subscribe('updateBid', this, "notif_updateBid");
            dojo.subscribe('updatePass', this, "notif_updatePass");
            dojo.subscribe('updateCoinche', this, "notif_updateCoinche" );
            dojo.subscribe('updateSurCoinche', this, "notif_updateSurCoinche" );
            dojo.subscribe('endBidding', this, "notif_endBidding" );
            dojo.subscribe('updateDog', this, "notif_updateDog" );
            dojo.subscribe('discardCards', this, "notif_discardCards");
            dojo.subscribe('firstTrick', this, "notif_firstTrick");
            dojo.subscribe('playCard', this, "notif_playCard");
            dojo.subscribe('trickWin', this, 'notif_trickWin');
            dojo.subscribe('newHand', this, 'notif_newHand');
            dojo.subscribe('endHand', this, 'notif_endHand');
			dojo.subscribe('newScores', this, 'notif_newScores');
			dojo.subscribe('playBelote', this, 'notif_playBelote');
			dojo.subscribe('allPassNoBid', this, 'notif_allPassNoBid');
  
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        
        notif_updateBid: function(notif) {
            this.contractTaker = notif.args.player_id;
            this.contractValue = notif.args.value;
            this.contractColor = notif.args.color;
            this.contractColorWritten = notif.args.color_written;
            this.contractDog = notif.args.dog;
            this.contractPartner = notif.args.partner;

            this.updateBid(this.contractTaker, this.contractValue, this.contractColor, this.contractColorWritten, this.contractDog, this.contractPartner);
        },

        notif_updateCoinche: function(notif) {
            this.coincher = notif.args.player_id;
            this.updateCoinche(this.coincher);
//            dojo.query('playertable').addClass('playerTables--coinched')   //should play coinche annimation
        },

        notif_updatePass: function(notif) {
            this.player = notif.args.player_id;
            this.oldhtml = $('player_bid_spot_' + this.player).innerHTML;
            this.newhtml = this.oldhtml+ 'Pass <br>';
            $('player_bid_spot_' + this.player).innerHTML = this.newhtml;
        },
    
        notif_updateSurCoinche: function(notif) {
            this.contractTaker = notif.args.taker;
            this.contractPartner = notif.args.partner;
            this.updateSurCoinche(this.contractTaker, this.contractPartner);
//			dojo.query('playertable').addClass('playerTables--coinched')    //should play coinche annimation
        },

        notif_endBidding: function(notif) {
            //remove previous trick from previous hand  (move to endHand?)
            this.previousTrick.removeAll();  

            this.orderTrump(notif.args.color)
        },

        notif_updateDog: function(notif) {
            // cards from the dog
            var dogCards =  notif.args.dogCards ;

            // move to previousTrick panel
            for ( var i in dogCards ) {
                var card = dogCards[i];
                var color = card.type;
                var value = card.type_arg;
                var cardId = this.getCardUniqueId(color, value) ;
                this.previousTrick.addToStockWithId(cardId, card.id);
            } 

            //for the taker, move them also to his hand
            this.contractTaker = notif.args.taker;
            if (this.player_id == this.contractTaker) {
                for ( var i in dogCards ) {
                    var card = dogCards[i];
                    var color = card.type;
                    var value = card.type_arg;
                    var cardId = this.getCardUniqueId(color, value) ;
                    this.playerHand.addToStockWithId(cardId, card.id);
                } 
            }
        },


        notif_discardCards: function(notif) {
            this.playerHand.unselectAll();
            
            // Remove the selected cards
            var cards = notif.args.discarded_cards_ids;
            for (var i in cards) {
                var card_id = cards[i];
                this.playerHand.removeFromStockById(card_id);
            }
        },

        notif_firstTrick : function(notif) {
            // hide jean-claude
            dojo.query('.icon_starter').forEach(dojo.destroy);
            // hide previous bids
            player_ids = notif.args.player_ids;
            for (var i in player_ids) {
                id = player_ids[i];
                $('player_bid_spot_' + id).innerHTML = '';
            }
        },


        notif_playCard : function(notif) {
            // Play a card on the table
            this.playCardOnTable(notif.args.player_id, notif.args.color, notif.args.value, notif.args.card_id);
        },


		notif_trickWin: function(notif) {
            var cardsOnTable = notif.args.cardsOnTable ;
            var me=this
			setTimeout(function() {
            // remove cards from previous trick
            me.previousTrick.removeAll();  

            // move cards from table to previous trick
            for ( var i in cardsOnTable ) {
                var card = cardsOnTable[i];
                var color = card.type;
                var value = card.type_arg;
                var cardId = me.getCardUniqueId(color, value) ;
                var player_id = card.location_arg;
                //me.previousTrick.changeItemsWeight({[card.id]:i});  //order cards, does not work
                me.previousTrick.addToStockWithId(cardId, card.id, 'cardontable_'+player_id);
            } 


            //destroy cards on table
            for(var player_id in me.gamedatas.players) {
                var card = dojo.byId('cardontable_' + player_id);
                me.fadeOutAndDestroy(card, 1);
            }
            }, 3000)

            //show points of teams (temp?)
            $('points_teams').innerHTML = '(' + notif.args.pointsTaker + '|' +  notif.args.pointsDefense + ')';
		},
    
        notif_newHand : function(notif) {
            // Indicate the received cards
            for (var i in notif.args.hand) {
                var card = notif.args.hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
            $('bid_indicator').innerHTML = '';
            dojo.query('.icon_coincher').forEach(dojo.destroy);  //(move to after bids ?)
            dojo.query('.icon_surcoincher').forEach(dojo.destroy);
            dojo.query('.icon_taker').forEach(dojo.destroy);
            dojo.query('.icon_partner').forEach(dojo.destroy);
            dojo.place('<div id="icon_starter" class="icon_starter"></div>', 'starter_icon_spot_' + notif.args.first_player_id);
            this.orderTrump(5);

			// Update bid panel buttons
            dojo.query('.bidPanel__btn--value').forEach(function(el) {
   		    	el.classList.remove('bidPanel__btn--hidden')
		    })

        },

        notif_endHand : function(notif) {
            this.playerHand.removeAll(); //in case it was an auto lose
            this.turn_counter.incValue(1);
            $('points_teams').innerHTML = '(' + notif.args.pointsTaker + '|' +  notif.args.pointsDefense + ')';
        },

        notif_newScores: function(notif) {
			// Update players' scores
			for (var playerId in notif.args.newScores) {
				this.scoreCtrl[playerId].toValue(notif.args.newScores[playerId])
			}
		},


        notif_playBelote: function(notif) { //TODO add bubble or smthg
        },

        notif_allPassNoBid : function(notif) {
            dojo.query('.icon_starter').forEach(dojo.destroy);
            this.playerHand.removeAll();
            this.turn_counter.incValue(1);
        },
        
   });             
});
