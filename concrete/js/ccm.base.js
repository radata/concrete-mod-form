// cannot rely on jQuery being loaded here

var ccm_uiLoaded = false;
var ccm_siteActivated = true;
var ccm_animEffects = false;

var field_min = 0;
var field_max = 0;

// validation code start RA----------------------------------------------------------------------------------------------------

function doWhat(active,value){
    var bdiv = document.getElementById('result'+ value);
    if (bdiv != null){
        if(active == true){
            $(bdiv).show(900);
        }else{
            $(bdiv).hide(900);
        }
    }
}
function doWhat2(active,value){
    var bdiv = document.getElementById('result2'+ value);
    if (bdiv != null){
        if(active == true){
            $(bdiv).show(900);
        }else{
            $(bdiv).hide(900);
        }
    }
}
function doWhat3(active,value){
    var bdiv = document.getElementById('result2'+ value);
    if (bdiv != null){$(bdiv).slideToggle(900)}
}
function update_css_class(field, class_index) {
    if (class_index == 2) {class_s = 'wrong';
    } else if (class_index == 1) {class_s = 'correct';
    } else if (class_index == 0) {class_s = 'none';
    }
    document.getElementById(field).className = class_s;
    return 1;
}
$(function(){
    $('.formBlockSubmitButton').click(function(){
        check_field.push(field);
        check_field = check_field.toString();
        document.getElementById("check_field").value= check_field;
    });
});
$(function(){
    $(".ratext").bind("focus blur change keyup",function() {
        var regex1 = $(this);
        var field_req = $("label[for='"+$(this).attr('id')+"']").attr("req");
        field = regex1.attr("name");
        curr_length = regex1.val().length;
        field_name = field+ '_'+ regex1.attr("type");
        field_mlen = regex1.attr("maxLength");
        res_len = field_mlen - curr_length;
        if (curr_length >= 2 && curr_length <= field_mlen) {
        if (field_req == 1) {
        regex1.css({'background-color': 'rgb(220, 255, 165)'});
        }
        }
        if(curr_length <= 2 || curr_length >= field_mlen){
        if (field_req == 1){
        regex1.css({'background-color': 'rgb(255, 183, 183)'});
        }else{
        regex1.css({'background-color': 'rgb(255, 255, 255)'});
        }
        }
    });
});
$(function(){
    $(".ccm-input-date").bind("focus blur change keyup",function() {
        var regex1 = $(this);
        var field_req = $("label[for='"+$(this).attr('id')+"']").attr("req");
        field = regex1.attr("name");
        curr_length = regex1.val().length;
        if (curr_length >=8 && curr_length <= 12) {
            if (field_req == 1) {
                regex1.css({'background-color': 'rgb(220, 255, 165)'});
            }
        }else{
            if (field_req == 1){
                regex1.css({'background-color': 'rgb(255, 183, 183)'});
            }else{
                regex1.css({'background-color': 'rgb(255, 255, 255)'});
            }
        }
    });
});
$(function(){
    $(".ranumber").bind("focus blur change keyup",function() {
    var regex = /^\d{8,16}[0-9]$/i;
    var regex1 = $(this);
    var field_req = $("label[for='"+$(this).attr('id')+"']").attr("req");
    field = regex1.attr("name");
    curr_length = regex1.val().length;
    field_name = field+ '_number';
    curr_val=0
    field_max=0
    field_min=0
    curr_val = regex1.val() /1;
    field_max = regex1.attr("max") /1;
    field_min = regex1.attr("min") /1;
    field_mlen = regex1.attr("maxLength");
    if(curr_length <= 7 || curr_length >= 17 || curr_val <= field_min || curr_val >= field_max){
        if (field_req == "1"){
            regex1.css({'background-color': 'rgb(255, 183, 183)'});
            if(curr_length <= 3){document.getElementById(field_name).innerHTML = '';}else{document.getElementById(field_name).innerHTML = '<h6>  0048.... 06....</h6>'+ ' ';}
            
        }else{
            regex1.css({'background-color': 'rgb(255, 255, 255)'});
            if(curr_length <= 3){document.getElementById(field_name).innerHTML = '';}else{document.getElementById(field_name).innerHTML = '<h6>  0048.... 06....</h6>'+ ' ';}
        }
    }else{
        if (regex.test(curr_val) && curr_val >= field_min && curr_val <= field_max){
            regex1.css({'background-color': 'rgb(220, 255, 165)'});
            document.getElementById(field_name).innerHTML = '';
        }}
    });
});
$(function(){
    $(".raemail").bind("focus blur change keyup",function() {
    var regex = /(([a-zA-Z0-9\-_.]+)@(([a-zA-Z0-9\-_]+\.)+)([a-z]{2,4}))+$/;
        var regex1 = $(this);
        var field_req = $("label[for='"+$(this).attr('id')+"']").attr("req");
        field = regex1.attr("name");
        curr_length = regex1.val().length;
        field_name = field+ '_email';
        curr_val = regex1.val();
        field_mlen = regex1.attr("maxLength");
        if(curr_length <= 6 || curr_length >= 200){
            if (field_req == "1"){
                regex1.css({'background-color': 'rgb(255, 183, 183)'});
                if(curr_length <= 3){document.getElementById(field_name).innerHTML = '';}else{document.getElementById(field_name).innerHTML = '<h6>  name@server.pl</h6>'+ ' ';}
            }else{
                regex1.css({'background-color': 'rgb(255, 255, 255)'});
                if(curr_length <= 3){document.getElementById(field_name).innerHTML = '';}else{document.getElementById(field_name).innerHTML = '<h6>  name@server.pl</h6>'+ ' ';}
            }
        }else{
            if (regex.test(curr_val)){
                regex1.css({'background-color': 'rgb(220, 255, 165)'});
                document.getElementById(field_name).innerHTML = '';
            }else{
                regex1.css({'background-color': 'rgb(255, 183, 183)'});
                if(curr_length <= 3){document.getElementById(field_name).innerHTML = '';}else{document.getElementById(field_name).innerHTML = '<h6>  name@server.pl</h6>'+ ' ';}
            }}
    });
});
function buttonclick(input, th) {
    if(input.value == ''){temp = 15;}else{temp = 0;}
    if(th == 1) {
       input.value = (input.value / 1) + 5 + temp;
    } else {
        // Don't allow decrementing below zero
        if (input.value > 0) {
            input.value = (input.value / 1) - 5;
        } else {
            input.value = '';
        }
    }
}
// validation code end RA----------------------------------------------------------------------------------------------------

ccm_parseJSON = function(resp, onNoError) {
	if (resp.error) {
		alert(resp.message);	
	} else {
		onNoError();
	}
}

ccm_deactivateSite = function(onDone) {
	if (ccm_siteActivated == false) {
		return false;
	}
	
	if ($("#ccm-overlay").length < 1) {
		$(document.body).append('<div id="ccm-overlay"></div>');
	}
	
	$("embed,object").each(function() {
		$(this).attr('ccm-style-old-visibility', $(this).css('visibility'));
		$(this).css('visibility', 'hidden');
	});
	
	if (ccm_animEffects) {
		$("#ccm-overlay").fadeIn(100);
	} else {
		$("#ccm-overlay").show();
	}
	
	ccm_siteActivated = false;
	if (typeof onDone == 'function') {
		onDone();
	}
}

ccm_activateSite = function() {
	if (ccm_animEffects) {
		$("#ccm-overlay").fadeOut(100);
	} else {
		$("#ccm-overlay").hide();
	}
	
	$("embed,object").each(function() {
		$(this).css('visibility', $(this).attr('ccm-style-old-visibility'));
	});

	ccm_siteActivated = true;
	ccm_topPaneDeactivated = false;
}


ccm_addHeaderItem = function(item, type) {
	// "item" might already have a "?v=", so avoid invalid query string.
	var qschar = (item.indexOf('?') != -1 ? '' : '?ts=');
	if (type == 'CSS') {
		if (navigator.userAgent.indexOf('MSIE') != -1) {
			// Most reliable way found to force IE to apply dynamically inserted stylesheet across jQuery versions
			var ss = document.createElement('link'), hd = document.getElementsByTagName('head')[0];
			ss.type = 'text/css'; ss.rel = 'stylesheet'; ss.href = item; ss.media = 'screen';
			hd.appendChild(ss);
		} else {
			if (!($('head').children('link[href*="' + item + '"]').length)) {
				$('head').append('<link rel="stylesheet" media="screen" type="text/css" href="' + item + qschar + new Date().getTime() + '" />');
			}
		}
	} else if (type == 'JAVASCRIPT') {
		if (!($('script[src*="' + item + '"]').length)) {
			$('head').append('<script type="text/javascript" src="' + item + qschar + new Date().getTime() + '"></script>');
		}
	} else {
		if (!($('head').children(item).length)) {
			$('head').append(item);
		}
	}
}

// called in versions popup
ccm_disableLinks = function() {
	td = document.createElement("DIV");
	td.style.position = "absolute";
	td.style.top = "0px";
	td.style.left = "0px";
	td.style.width = "100%";
	td.style.height = "100%";
	td.style.zIndex = "1000";
	document.body.appendChild(td);
}
