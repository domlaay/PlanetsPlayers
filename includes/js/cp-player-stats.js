$(document).ready(function(){
	
	function StatsPage(){ };
	StatsPage.prototype = {
		show: function(Fdata, searchText){
			
			var Data = {};
			Data = Fdata;
			
			Data['players'].sort(function(a,b){
				return b.weight - a.weight;
			});
			
			if(searchText!=""){
				players  = [];
				for(i=0;i<Data['players'].length; i++){
					if(Data['players'][i]['username'].indexOf(searchText)!= -1)
						players.push(Data['players'][i]);
				}
				Data['players']=players;
			}
			
			var template = $('#rankingtemplate').html();
			var info = Mustache.to_html(template, Data);
			$('#cp-main-container').html(info);
			psp.stopthinking();
			
			$('#cp_update_games').click(function(){ 
				psp.update_games(); 
			});
			$('#cp_about').click(function(){ 
				psp.about_project(true); 
			});
			$('#cp_close_about').click(function(){ 
				psp.about_project(false); 
			});
			$('#cp_weight_info').click(function(){
				psp.weight_info(true);
			});
			$('#cp_close_weight').click(function(){
				psp.weight_info(false);
			});
			$('#search_input').on('focus', function(){
				$(this).val("");
			});
			$('#search_button').click(function(){
				searchText = $('#search_input').val();
				psp.show(Fdata, searchText);
			});
			$('#clear_search_button').click(function(){
				psp.load();
			});
			$('.player-stats').hover(function(){
				id = $(this).attr("id");
				$('#lower_'+id).css('display','block');
			},function(){
				$('#lower_'+id).css('display','none');
			});
			
		},
		thinking: function(thinkingabout){
			$('#thinking').html('');
			$('#thinking').append(thinkingabout);
			$('#thinking').activity();
		},
		// Stop Display
		stopthinking: function(){
			$('#thinking').activity(false);
			$('#thinking').html('');
		},
		about_project: function(show){
			if(show != true)
				$('#about_project').css('display', 'none');
			else
				$('#about_project').css('display', 'block');
		},
		weight_info: function(show){
			if(show!=true)
				$('#weight_info').css('display', 'none');
			else
				$('#weight_info').css('display', 'block');
		},
		update_games: function(){
			psp.thinking("updating games...");
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
					$.post('classes/Post.class.php',{Post_Update_Games:Post_Update_Games,gameids:gameids,gamenames:gamenames},function(data){
						
						if(data=="0" || data==0){
							alert("No new Games to update");
							psp.stopthinking();	
						}
						else{
							//alert(data);
							parsed = JSON.parse(data);
							gameids = [];
							for(i=0; i<parsed['gameids'].length; i++){
								gameids[i]=parsed['gameids'][i]['gameid'];
							}
							$('#update_info').css('display', 'block');
							psp.recursive_update(gameids);
						}
					});
				}
			});
		},
		recursive_update: function(gameids){
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
								if(data['players'][i]['accountid']!=0){
									accountid = data['players'][i]['accountid'];
									username = data['players'][i]['username'];
									finishrank = data['players'][i]['finishrank'];
									planets = data['players'][i]['planets'];
									turnjoined = data['players'][i]['turnjoined'];
									turnfinish = data['players'][i]['score']['turn'];
									ships = data['players'][i]['ships'];
									militaryscore = data['players'][i]['score']['militaryscore'];
									starbases = data['players'][i]['score']['starbases'];
									update2 = "Username "+username+" AccountID "+accountid+" Finishrank "+finishrank+"<br />";
									$('#update_info').append(update2);
									NewFinish = 1;
									$.post('classes/Post.class.php',
										{
											NewFinish:NewFinish,
											gameid:gameid,
											accountid:accountid,
											username:username,
											finishrank:finishrank,
											planets:planets,
											turnjoined:turnjoined,
											turnfinish:turnfinish,
											ships:ships,
											militaryscore:militaryscore,
											starbases:starbases
										},
										function(result){
										
										}
									);
								}
							}
							gameids.shift();
							psp.recursive_update(gameids);
						}
					});
				},3000);
			}
			else{
				psp.stopthinking();
				alert('Update Complete');
				$('#update_info').css('display', 'none');
				load();
			}
		},
		load: function(){
			psp.thinking("loading...");
			Load=1;
			$.post('classes/Post.class.php',{Load:Load},function(data){
				//$('#update_info').css('display','block');
				//$('#update_info').html(data);
				
				var data = JSON.parse(data);
				psp.show(data,"");
				
			});
		}
	}
	

	var psp = new StatsPage();
	psp.load();
	
})
