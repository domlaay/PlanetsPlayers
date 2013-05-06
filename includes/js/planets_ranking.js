//    Cully Wakelin 2013
//    Unofficial Planets.nu Statistics Project
//    This Javascript document is open license and open source

$(document).ready(function(){
	
	load();
	
	//Event Handlers
	$('#cp_about').click(function(){ 
		about_project(true); 
	});
	$('#cp_close_about').click(function(){ 
		about_project(false); 
	});
	$('#cp_refresh_records').click(function(){ 
		cpHello();
	});
	$('#cp_update_games').click(function(){ 
		update_games(); 
	});
	$('#cp_update_players').click(function(){ 
		cpHello(); 
	});
	$('#cp_update_ranking').click(function(){ 
		cpHello(); 
	});
	//The Load Function calls up The Post Class to retrieve all the necessary data
	// The data is return to this function as a JSON Object
	function load(){
		
		thinking("loading...");
		var Load = true;
		$.post('classes/PlayerData.class.php',{Load:Load},function(data){
			//alert(data);
			var parsed = JSON.parse(data);
			show(parsed);
			stopthinking();
			
		});
	}
	// Thow Show funtion merges the data with the html template
	function show(TheData){
		var template = $('#rankingtemplate').html();
		var info = Mustache.to_html(template, TheData);
		$('#cp-main-container').html(info);
	}
	//UPDATE GAMES IN DATABASE FUNCTION
	// FIRST IT CALLS THE API FOR THE LIST OF GAMES THAT ARE STANDARD AND FINISHED
	// THEN IT SENDS THE GAME
	function update_games(){
		thinking("updating games...");
		$.ajax({
			url : 'http://api.planets.nu/games/list?type=2&status=3',
			dataType: 'json',
			success: function(data){
				gameids = [];
				gamenames = [];
				for(i=0;i<data.length;i++){
					gameids[i] = data[i].id;
					gamenames[i] = data[i].name;
				}
				var Post_Update_Games = 1;
				
				$.post('classes/PlayerData.class.php',{Post_Update_Games:Post_Update_Games,gameids:gameids,gamenames:gamenames},function(data){
					if(data=="0" || data==0){
						alert("No new Games to update");
						stopthinking();
					}
					else{
						//alert(data);
						parsed = JSON.parse(data);
						
						gameids = [];
						for(i=0; i<parsed['gameids'].length; i++){
							gameids[i]=parsed['gameids'][i]['gameid'];
						}
						$('#update_info').css('display', 'block');
						recursive_update(gameids);
					}
					
				});
			}
		});
	}
	function recursive_update(gameids){
		var time = new Date();
		
		if(gameids.length>0){
			setTimeout(function(){
				$.ajax({
					url: "http://api.planets.nu/game/loadinfo?gameid="+gameids[0],
					dataType: 'json',
					success: function(data){
						gameid=gameids[0];
						gamename=data['game']['name'];
						
						update1 = time+"<br />Game "+gamename+" ID "+gameid+" has been added with the following finishers:<br />";
						$('#update_info').append(update1);
						
						for(i=0; i<data['players'].length; i++){
							
							accountid = data['players'][i]['accountid'];
							username = data['players'][i]['username'];
							finishrank = data['players'][i]['finishrank'];
							update2 = "Username "+username+" AccountID "+accountid+" Finishrank "+finishrank+"<br />";
							$('#update_info').append(update2);
							if((data['players'][i]['accountid']!=0) && (data['players'][i]['accountid'])!="0"){
								NewStuff = 1;
								$.post('classes/PlayerData.class.php',{NewStuff:NewStuff,gameid:gameid,accountid:accountid,username:username,finishrank:finishrank},function(result){
									
								});
							}
						}
						
						gameids.shift();
						recursive_update(gameids);
					}
				});
			},3000);
		}
		else{
			stopthinking();
			alert('Update Complete');
			$('#update_info').css('display', 'none');
		}
			
	}
	function update_players(){
		thinking("updating player data...")
	}
	function update_ranking(){
		
	}
	function temp(){
		thinking();
		GetTemp = 1;
		$.post('classes/PlayerData.class.php',{GetTemp:GetTemp},function(data){
			
			players = JSON.parse(data);
			plen = players['Players'].length;
			
			var accountids = [];
			for(i=0;i<plen;i++){
				
				accountid = players['Players'][i].accountid;
				accountids[i] = accountid;
			}
			
			temp2(accountids);
		});
	}
	function temp2(accountids){
		
		if(accountids.length > 0){
			
			accountid = accountids[0];
			
			setTimeout(function(){
				
				$.ajax({
					url : 'http://api.planets.nu/account/history?version=2&accountid='+accountids[0],
					dataType: 'json',
					success: function(data){
						
						plen = data['history'].length;
						for(i=0;i<plen;i++){
							
							if((data['history'][i]['status'] != 1)&&(data['history'][i]['status'] != 6)){
								gameid = data['history'][i]['gameid'];
								
								
								NewDrop = 1;
								$.post('classes/PlayerData.class.php',{NewDrop:NewDrop,accountid:accountid,gameid:gameid},function(data){
									$('body').append(accountids.length+" "+"insertid:"+data+"<br />");
								});
								
								
							}
							
							
						}
					}
				});
				
				accountids.shift();
				temp2(accountids);
				
			}, 1300);
			
		}
		else{
			stopthinking();
			alert('Finished');
		}
		
	}
	//Circular activity display
	function thinking(thinkingabout){
		$('#thinking').append(thinkingabout);
		$('#thinking').activity();
	}
	// Stop Display
	function stopthinking(){
		
		$('#thinking').activity(false);
		$('#thinking').empty();
	}
	// Test Alert
	function cpHello(){
		alert("Hello");
	}
	function about_project(show){
		if(show != true)
			$('#about_project').css('display', 'none');
		else
			$('#about_project').css('display', 'block');
	}
	
	
	//var Rankings = new PageLoad();
	//Rankings.show();
	
	
})


