//    Cully Wakelin 2013
//    Unofficial Planets.nu Statistics Project
//    This Javascript document is open license and open source

$(document).ready(function(){
	
	function PageLoad(){ };
	PageLoad.prototype = {
		//var data{};
		//var posturl = "classes.POSTS.php";
		
		
		//The Load Function calls up The Post Class to retrieve all the necessary data
		// The data is return to this function as a JSON Object
		load: function(){
			
			alert("Load");
			//load data here and show again
			Rankings.show();
		},
		// Thow Show funtion merges the data with the html template
		show: function(){
			
			//show data
			//alert("Show!");
			
			//add event handlers -
			/*
			 * var template = $('#locations-template').html();
					var info = Mustache.to_html(template, parse);
					$('#LocationNames').html(info);
			 */
			$('#cp_about').click(function(){ cpHello(); });
			$('#cp_refresh_records').click(function(){ cpHello(); });
			$('#cp_update_database').click(function(){ cpHello(); });
			
		},
		thinking: function(){
			$('#thinking').activity();
		},
		stopthinking: function(){
			$('#thinking').activity(false);
		},
		
	}
	
	function cpHello(){
		alert("Hello");
	}
	var Rankings = new PageLoad();
	Rankings.show();
	
})
