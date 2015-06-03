jQuery(document).ready(function(){ 
    var zindex = 1;
    var i = 0;
//    jQuery(".img").draggable({      
//        start: function(event, ui) {
//            zindex++;
//            var cssObj = {
//                'z-index' : zindex
//            };
//            $(this).css(cssObj);
//        }
//    });


    jQuery('.img').each(function(){
        
        var id = jQuery(this).attr('id');
        var rot = '';
        var left = '';
        var top = '';
        /*var rot = Math.random()*30-15+'deg'; alert('Rot'+rot);        
        var left = Math.random()*50+'px'; alert('Left'+left);
        var top = Math.random()*150+'px'; alert('Top'+top);*/
        
        if(i%2 == 0){

            rot = '-3.086876218770616deg';
            left = '-62.736825359692546px';
            top = '100.53539054753695px';
            i++;
        }else{
            
            rot = '3.199003466929405deg';
            left = '-72.584499464719634px';
            top = '121.18911103849784px';
            i++;
        }
        
        
        
        jQuery(this).css('-webkit-transform' , 'rotate('+rot+')');
        jQuery(this).css('-moz-transform' , 'rotate('+rot+')');
        jQuery(this).css('top' , left);
        jQuery(this).css('left' , top);
        jQuery(this).mouseup(function(){
            zindex++;
            jQuery(this).css('z-index' , zindex);
        });
        
         jQuery('#caption_'+id).css('-webkit-transform' , 'rotate('+rot+')');
        jQuery('#caption_'+id).css('-moz-transform' , 'rotate('+rot+')');
        jQuery('#caption_'+id).css('top' , left);
        jQuery('#caption_'+id).css('left' , top);
        jQuery('#caption_'+id).mouseup(function(){
            zindex++;
            jQuery('#caption_'+id).css('z-index' , zindex);
        });
        
        
    });
    jQuery('.img').dblclick(function(){
        jQuery(this).css('-webkit-transform' , 'rotate(0)');
        jQuery(this).css('-moz-transform' , 'rotate(0)');
    });
    
    jQuery('.img').mouseover(function()
       {
           var id = jQuery(this).attr('id');
          jQuery(this).css("cursor","pointer");
          jQuery(this).animate({width: "150px",height:'171px',position:'absolute'}, 'fast');
          jQuery(this).css('z-index' , '9999999');
          
          jQuery('#caption_'+id).css('z-index' , '9999999');          
          jQuery('#caption_'+id).css('font-size','150%');
		  jQuery('#caption_'+id).css('top','-75px');
		  
        /*   jQuery('#caption_'+id).css('-webkit-transform' , 'rotate(0)');
        jQuery('#caption_'+id).css('-moz-transform' , 'rotate(0)');*/
          
                    
       });
       
      jQuery('.img').mouseout(function()
      {   
          var id = jQuery(this).attr('id');
          jQuery(this).animate({width: "180px",height:'166px'}, 'fast');
          jQuery(this).css('z-index' , '')
          
          jQuery('#caption_'+id).css('z-index' , '');          
          jQuery('#caption_'+id).css('font-size','130%');
          jQuery('#caption_'+id).css('top','-75px');
          
       });   
    

    jQuery(".gallery:first a[rel^='prettyPhoto']").prettyPhoto(
    {
        animation_speed:'normal',
        theme:'pp_default',
        slideshow:3000, 
        show_title: true,
        autoplay_slideshow: false,
        gallery_markup:'',
        social_tools: jQuery('#addthiscontent').html()
		
    });
    
    
    jQuery('#add_more').click(function(){
        
        
        
    });
    
});

