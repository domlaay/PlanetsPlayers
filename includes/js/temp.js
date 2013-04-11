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
			        var GAMES = JSON.parse(data);
			        datalength = GAMES.length;
			        var gameIDs = [];
			        for(i=0;i<datalength;i++){ 
			        	gameIDs[i]=GAMES[i].id; 
			        }
			        playersDS.prebuild(gameIDs);
			    }
			});
		},
		
		prebuild: function(gameIDs){
			var jsonObject = {"buildset":[]};
			playersDS.buildset(gameIDs,jsonObject,0);
		},
		
		buildset: function(gameIDs,jsonObject,count){
			//alert(gameIDs.length);
			if(gameIDs.length > 0){
				$.ajax({
					url: "http://api.planets.nu/game/loadinfo?gameid="+gameIDs[0],
					dataType: 'json',
					success: function(data) {
						//alert(data['players'].length);
						parray=[];
						plen = data['players'].length;
						for(i=0;i<plen;i++){
							parray[i]={"username":data['players'][i].username,"accountid":data['players'][i].accountid,"finishrank":data['players'][i].finishrank};
						}
						jsonObject['buildset'][count]={"gameid":data['game'].id,"gamename":data['game'].name,"players":parray};
						//alert(jsonObject['buildset'][count].players[0].username);
						gameIDs.shift();
						count = count + 1;
						
						playersDS.buildset(gameIDs,jsonObject,count);
					}
				});
			}
			else{
				$('body').activity(false);
				$('body').html(JSON.stringify(jsonObject));
			}
		}
	}
	
	var playersDS = new PlayersDataSet();
	playersDS.loadgames();
	
});