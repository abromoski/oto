// JavaScript Document

require([ 'jquery' ,  'domReady!'],function($){
    

       
//Scroll next

        $(".scrollNext").find("a").click(function(e) {
e.preventDefault();
var section = $(this).attr("href");
$("html, body").animate({
    scrollTop: $(section).offset().top -140
});
});








});

