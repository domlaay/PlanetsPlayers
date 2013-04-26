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
	
	$('#cp_update_database').click(function(){ 
		update(); 
	});
	//The Load Function calls up The Post Class to retrieve all the necessary data
	// The data is return to this function as a JSON Object
	function load(){
		
		thinking();
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
	function update(){
		var Calc = 1;
		$.post('classes/PlayerData.class.php',{Calc:Calc},function(data){
			alert(data);
		});
	}
	//Circular activity display
	function thinking(){
		$('#thinking').activity();
	}
	// Stop Display
	function stopthinking(){
		$('#thinking').activity(false);
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
