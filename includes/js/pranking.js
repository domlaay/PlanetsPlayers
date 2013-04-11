//  JAVASCRIPT FOR PLANETS RANKING PROJECT
//     BY: CULLY WAKELIN
$(document).ready(function(){
	
	function PlayersDataSet(){ };
	PlayersDataSet.prototype = {
		
		loadgames: function(){
			$('body').activity();
			$.ajax({
			    url: "http://api.planets.nu/games/list?status=3",
			    //dataType: 'jsonp',
			    success: function(data) {
			    	//$('body').html(data);
			        var GAMES = JSON.parse(data);
			        datalength = GAMES.length;
			        var gameID;
			        for(i=0;i<datalength;i++){ 
			        	if((GAMES[i].slots == 11) || (GAMES[i].slots == '11'))
			        	{
			        		table = 'p_games';
			        	}
			        	else{
			        		table = 'p_alt_games';
			        	}
			        	gameID = GAMES[i].id;
			        	playersDS.trythis(gameID, table);
			        }
			        
			        $('body').activity(false);
			    }
			});
		},
		trythis: function(gameID,table){
			$.post('classes/POSTS.php',{gameID:gameID,table:table},function(data){});
		}

	}
	
	var playersDS = new PlayersDataSet();
	playersDS.loadgames();
	
});
