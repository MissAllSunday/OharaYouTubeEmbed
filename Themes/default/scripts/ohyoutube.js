/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

$(function(){$(".youtube").each(function(){$(this).css("background-image","url(http://i.ytimg.com/vi/"+this.id+"/sddefault.jpg)");$(this).append($("<div/>",{"class":"youtube_play"}));$(document).on("click","#"+this.id,function(a){a="https://www.youtube.com/embed/"+this.id+"?autoplay=1&autohide=1";$(this).data("params")&&(a+="&"+$(this).data("params"));a=$("<iframe/>",{frameborder:"0",src:a,width:$(this).width(),height:$(this).height()});$(this).replaceWith(a)})})});