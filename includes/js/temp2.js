//This is a temporary program that runs on the page load 
// it grabs the gameids from the DB and calls the api for each game ID to get game details

$(document).ready(function(){
	
	function DataProgram() { };
	DataProgram.prototype = {
		
		getgames: function(){
			
			dp.thinking();
			var GET_GAMES = 1;
			$.post('classes/POSTS.php',{GET_GAMES:GET_GAMES},function(data){ 
				
					result = JSON.parse(data);
					var gameIDs = [];
					var numIDs = result['Games'].length;
					for(i=0;i<numIDs;i++){
						gameIDs.push(result['Games'][i]['gameid']);
					}
					dp.run(gameIDs);
					
			 });
		},
		
		run: function(gameIDs){
			var d = new Date();
			var seconds = d.getSeconds();
			$('.cwp-wrapper').append("<br />    RUN!!!  "+seconds+ "<br />" );
			//$('body').html(gameIDs);
			if(gameIDs.length > 0){
				
				var gameid = gameIDs[0];
				
				setTimeout(function(){
					$.ajax({
						url: "http://api.planets.nu/game/loadinfo?gameid="+gameIDs[0],
						dataType: 'json',
						success: function(data){
							
							plen = data['players'].length;
							for(i=0;i<plen;i++){
								ADD_STUFF = 1;
								username  = data['players'][i].username;
								accountid = data['players'][i].accountid;
								finishrank = data['players'][i].finishrank;
								
								$.post('classes/POSTS.php',{ADD_STUFF:ADD_STUFF,username:username,accountid:accountid,finishrank:finishrank,gameid:gameid},function(data){ 
									output = "["+ gameIDs.length +" "+ gameid +" "+ data +"]";
									$('.cwp-wrapper').append(output); 
								});
								
							}
							
							gameIDs.shift();
							dp.run(gameIDs);
						}
					});
					
				},3500);
				
			}
			else{
				dp.stopthinking();
				alert(gameIDs.length + 'DONE!');
			}
		},
		thinking: function(){
			$('#thinking').activity();
		},
		stopthinking: function(){
			$('#thinking').activity(false);
		}
		
	};
	
	dp = new DataProgram();
	dp.getgames();
});
