{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Taroinche implementation : © Côme Dattin <c.dattin@gmail.com>
-- Implemented using parts of the coinche and tarot games
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="playertables">

	<!-- players according to tarot -->

    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{DIR} playertable_{PLAYER_ID}" data-player="{PLAYER_ID}">
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
		<div id="player_bid_spot_{PLAYER_ID}" class="player_bid"></div>  <!-- utile ?? -->
        <div id="starter_icon_spot_{PLAYER_ID}" class="starter starter_spot"></div>
        <div id="taker_icon_spot_{PLAYER_ID}" class="taker taker_spot"></div>
        <div id="partner_icon_spot_{PLAYER_ID}" class="partner partner_spot"></div>
        <div id="coincher_icon_spot_{PLAYER_ID}" class="coincher coincher_spot"></div>
        <div id="surcoincher_icon_spot_{PLAYER_ID}" class="surcoincher surcoincher_spot"></div>
        <div class="playertablecard" id="playertablecard_{PLAYER_ID}">
        </div>
    </div>
    <!-- END player -->


    <!-- BID PANEL  according to coinche-->
	<div class="bidPanel">

		<div class="bidPanel__title">
			{BID_OR} <a class="bidPanel__btn bidPanel__btn--pass">{PASS}</a>
		</div>

		<div class="bidPanel__values">
			<a class="bidPanel__btn bidPanel__btn--value-left">&lt;</a>
			<div class="bidPanel__values-list">
				<a class="bidPanel__btn bidPanel__btn--value bidPanel__btn--value-capot" data-value="250">Capot</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="82">82</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="90">90</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="100">100</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="110">110</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="120">120</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="130">130</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="140">140</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="150">150</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="160">160</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="170">170</a>
				<a class="bidPanel__btn bidPanel__btn--value" data-value="180">180</a>
			</div>
			<a class="bidPanel__btn bidPanel__btn--value-right">&gt;</a>
		</div>

		<div class="bidPanel__colors">
			<a class="bidPanel__btn bidPanel__btn--color" data-color="1">
				<span class="card-color-icon card-color-icon--spade"/>
			</a>
			<a class="bidPanel__btn bidPanel__btn--color" data-color="2">
				<span class="card-color-icon card-color-icon--heart"/>
			</a>
			<a class="bidPanel__btn bidPanel__btn--color" data-color="3">
				<span class="card-color-icon card-color-icon--club"/>
			</a>
			<a class="bidPanel__btn bidPanel__btn--color" data-color="4">
				<span class="card-color-icon card-color-icon--diamond"/>
			</a>
			<a class="bidPanel__btn bidPanel__btn--color" data-color="6">
				<span class="card-color-icon card-color-icon--notrump"/>
			</a>
			<a class="bidPanel__btn bidPanel__btn--color" data-color="5">
				<span class="card-color-icon card-color-icon--alltrump"/>
			</a>
			<div class="bidPanel__spacer"></div>
			<a class="bidPanel__btn bidPanel__btn--coinche ">{COINCHE} !</a>
		</div>

        <div class="bidPanel__dog">
            <a class="bidPanel__btn bidPanel__btn--dog" data-value="1">With</a>
            <a class="bidPanel__btn bidPanel__btn--dog" data-value="2">Without</a>
            <a class="bidPanel__btn bidPanel__btn--dog" data-value="3">Against</a>
        </div>

	</div>
</div>



<div id="myhand_wrap" class="whiteblock">
    <h3>{MY_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<div id="right">
	<div id='turn_count_wrap' class='whiteblock'>
        <span>{HAND}</span>
        <span> </span>
        <span id='turn_counter'></span><span id='total_turns'></span>
    </div>
        
    <div id='bid_indicator_wrap' class='whiteblock'>
        <h3 id='current_bid'>{CURRENT_BID}</h3>
        <span id="bid_indicator"></span>
		<div  id='points_teams'> </div>
    </div>

    <div id='previous_trick_wrap' class='whiteblock'>
        <span> {PREVIOUS_TRICK} </span>
        <div id='previous_trick'></div>
    </div>



</div>

<script type="text/javascript">


var jstpl_cardontable = '<div class="cardontable" id="cardontable_${player_id}" style="background-position:-${x}px -${y}px"> </div>';


</script>  

{OVERALL_GAME_FOOTER}
