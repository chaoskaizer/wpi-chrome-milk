jQuery(document).ready( function(){ 
  var wpi= {}; 
  wpi.toggle = function(elm){jQuery(elm).animate({"height":"toggle","opacity":"toggle"},{duration:550});	};
    jQuery('.title-').click(function(){	wpi.toggle(jQuery(this).next());}); 	
    if (typeof adminMenu == 'object') adminMenu.fold(); 
    //if(jQuery('#message').length >= 0){jQuery('#message').hide(1500, function(){jQuery(this).remove();});};
 });