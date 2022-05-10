 // JavaScript Document

require([ 'jquery' ,  'domReady!'],function($){
    "use strict";
/*
if ($('#introVideo').length > 0) {
  // exists.

  
"use strict";
var video = document.getElementById('introVideo');
    
    

var x = window.matchMedia('(max-width: 767px)');
checkScreenSize(x) ;// Call listener function at run time
openVideo();
x.addListener(checkScreenSize) ;// Attach listener function on state changes


    
 
    
   $(document).ready(function(){
    $('video').on('ended',function(){
      
       endVideo();
        
    });
  });
    
    
     $(document).on("click","#play-button", function() {
         
        repeatVideo();
        });
    
     $(document).on("click","#pause-button", function() {
            
         endVideo();
        });
    
     $(document).on("click","#unmute-button", function() {
           
         disableMute();
        });
    
    $(document).on("click","#mute-button", function() {
        
        enableMute();
        });
  
    
}
    
    
    
    function checkScreenSize(x) {

    if (video !== null){

if (x.matches) { // If media query matches
    
    
video.insertAdjacentHTML("beforeend", "<source src='https://d181m8jqqjygm1.cloudfront.net/OTO_FOCUS_MOBILE.webm'  type='video/webm' /> <source src='https://d181m8jqqjygm1.cloudfront.net/OTO_FOCUS_MOBILE.mp4'  type='video/mp4' />");
//video.setAttribute("poster", "https://d181m8jqqjygm1.cloudfront.net/video-first-frame-mobile.jpg");
 
    
} else {
   
	video.muted = true;
    
video.insertAdjacentHTML("beforeend", "   <source  src='https://d181m8jqqjygm1.cloudfront.net/OTO_FOCUS_WIDE.webm' type='video/webm' /> <source src='https://d181m8jqqjygm1.cloudfront.net/OTO_FOCUS_WIDE.mp4 '  type='video/mp4' />"); 


}

}




}
    //End Video Action

function endVideo(){
	
    var x = document.getElementById("pause-button");
x.style.display = "none";
x = document.getElementById("play-button");
x.style.display = "inline-block";
        hideMuteButton();
	document.cookie = "videoplaying=false";   
	sessionStorage.setItem("videoplaying", "off");
	/*if (document.cookie.match(/^(.*;)?\s*videoplaying\s*=\s*[^;]+(.*)?$/))
{
alert("Hello");
}
	else{
		alert("Goodbye");
	}  document.getElementById("poo").checked =  true;
	
	

    
   
    $('#maincontent').css({ 'opacity' : 0 });

    $('html, body').delay(2000).animate({
    scrollTop: $('#maincontent').offset().top -160
        }, 5);
    
     $("#maincontent").delay(2000).fadeTo(2000, 1);
    
    
   
            



    }

//End End Video Action
    
     var myvid = $('#introVideo')[0];
$(window).scroll(function(){
  var scroll = $(this).scrollTop();
	if (scroll > 70) {
		
	myvid.pause()
	}  
	
	else if (/*document.cookie=="videoplaying=true" sessionStorage.videoplaying =="on"	) {
		myvid.play()
 
}
	
})
    

//Open Video

function openVideo() {
    "use strict";

    if (sessionStorage.videoplaying == "off") {
		showPlayButton();
hideMuteButton();



$('#maincontent').css({ 'opacity' : 0 });

$('html, body').delay(5).animate({
scrollTop: $('#maincontent').offset().top -160
    }, 5);
    video.pause();
 $("#maincontent").delay(2000).fadeTo(2000, 1);
    
   
	}
	
else{
    document.cookie ="videoplaying=true";
    sessionStorage.removeItem("videoplaying");
	sessionStorage.setItem("videoplaying", "on");
	
    setTimeout(function(){ 
   document.getElementById("poo").checked = false;
	
   
}, 1000);
 
}
    }

        


        //repeat video
        
        function repeatVideo() {
	
	var x = document.getElementById("play-button");
x.style.display = "none";
x = document.getElementById("pause-button");
x.style.display = "inline-block";
	document.getElementById("poo").checked = false;
video.currentTime = 0;
	document.cookie ="videoplaying=true";
	sessionStorage.setItem("videoplaying", "on");
video.muted=false;
		
		disableMute();
		
	

	video.play();
	
}

//Sound Controls

function disableMute() { 
    video.muted = false;
var x = document.getElementById("unmute-button");
x.style.display = "none";
x = document.getElementById("mute-button");
x.style.display = "inline-block";
} 

function enableMute() { 
    video.muted = true;

var x = document.getElementById("mute-button");
x.style.display = "none";
x = document.getElementById("unmute-button");
x.style.display = "inline-block";
} 


function hideMuteButton(){
var x = document.getElementById("mute-button");
x.style.display = "none";
x = document.getElementById("unmute-button");
x.style.display = "none";	
	
}





function showPlayButton(){
	var x = document.getElementById("pause-button");
x.style.display = "none";
x = document.getElementById("play-button");
x.style.display = "inline-block";	
	
}
   */ 
});
