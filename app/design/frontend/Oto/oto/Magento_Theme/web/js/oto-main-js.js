require(['jquery','domReady!'],function($){$(".scrollNext").find("a").click(function(e){e.preventDefault();var section=$(this).attr("href");$("html, body").animate({scrollTop:$(section).offset().top-140})});$(function(){$('a[href*=#s]').on('click',function(e){e.preventDefault();$('html, body').animate({scrollTop:$($(this).attr('href')).offset().top-200},500,'linear')})});$(document).ready(function(){window.addEventListener('resize',()=>{let vh=window.innerHeight*0.01;document.documentElement.style.setProperty('--vh',`${vh}px`)});$(document.body).addClass('jsloaded')});$("a[href^='#ingredients']").click(function(e){e.preventDefault();var position=$($(this).attr("href")).offset().top;$("body, html").animate({scrollTop:position})});$(document).ready(function(){$('.header.links').clone().appendTo('#store\\.links')});$(document).ready(function(){$(".random-div"+(new Date().getTime()%3+1)).css("display","block")});window.onscroll=function(){stickyNav()};var navbar=document.getElementById("hello");if(navbar!==null){if(window.matchMedia('screen and (max-width: 767px)').matches){var sticky=navbar.offsetTop+20}
else{var sticky=navbar.offsetTop+0}
function stickyNav(){if(window.pageYOffset>=sticky){navbar.classList.add("fixed-top");navbar.classList.add("mobiletop")}else{navbar.classList.remove("fixed-top")
navbar.classList.remove("mobiletop")}}}
if($.isFunction($.fn.jScrollability)){(function($){$(document).ready(function(){$.jScrollability([{'selector':'.slide-in-demo','start':'parent','end':function($el){return $el.offset().top+$(window).height()},'fn':{'left':{'start':100,'end':0,'unit':'%'}}},{'selector':'.reveal-demo','start':'parent','end':function($el){return $el.offset().top+$(window).height()},'fn':{'top':{'start':100,'end':0,'unit':'%'}}},{'selector':'.text-wrapper','start':function($el){return $el.offset().top+$el.height()},'end':function($el){return $el.offset().top+$(window).height()},'fn':function($el,pcnt){var $spans=$el.find('span');var point=Math.floor(($spans.length+2)*pcnt);$spans.each(function(i,el){var $span=$(el);if(i<point){$span.addClass('visible')}else{$span.removeClass('visible')}})}}])})})(jQuery)}
$(document).ready(function(){var imgs=$('img');imgs.each(function(){var img=$(this);var width=img.width();var height=img.height();if(width<height){img.addClass('portrait')}else{img.addClass('landscape')}})});$(document).ready(function(){$(document).on("mouseover",".three-focus",function(){changeToFocus()});$(document).on("mouseout",".three-focus",function(){changeBack()});$(document).on("mouseover",".three-amplify",function(){changeToAmplify()});$(document).on("mouseout",".three-amplify",function(){changeBack()});$(document).on("mouseover",".three-balance",function(){changeToBalance()});$(document).on("mouseout",".three-balance",function(){changeBack()});function changeToAmplify(){$("#bellCurveChange").attr("src","https://otocbd.com/pub/media/amplify-curve-2000-500.png").stop(!0,!0).hide().fadeIn(500);$(".col-lg-4.amplify ").css("opacity","1")}
function changeToFocus(){$("#bellCurveChange").attr("src","https://otocbd.com/pub/media/focus-curve-2000-500.png").stop(!0,!0).hide().fadeIn(500);$(".col-lg-4.focus ").css("opacity","1")}
function changeToBalance(){$("#bellCurveChange").attr("src","https://otocbd.com/pub/media/balance-curve-2000-500.png").stop(!0,!0).hide().fadeIn(500);$(".col-lg-4.balance ").css("opacity","1")}
function changeBack(){$(".col-lg-4.amplify ").css("opacity","0.2");$(".col-lg-4.focus ").css("opacity","0.2");$(".col-lg-4.balance ").css("opacity","0.2");$(".category-collections .col-lg-4.amplify ").css("opacity","1");$(".category-collections .col-lg-4.focus ").css("opacity","1");$(".category-collections .col-lg-4.balance ").css("opacity","1")}})})