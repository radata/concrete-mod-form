var miniSurvey ={
    bid:0,
    serviceURL: $("input[name=miniSurveyServices]").val() + '?block=form&',
    init: function(){
        this.tabSetup();

        /*
         for(var i=0;i<this.answerTypes.length;i++){
         this.answerTypes[i].onclick=function(){miniSurvey.optionsCheck(this);miniSurvey.settingsCheck(this);}
         this.answerTypes[i].onchange=function(){miniSurvey.optionsCheck(this);miniSurvey.settingsCheck(this);}
         }
         for(var i=0;i<this.answerTypesEdit.length;i++){
         this.answerTypesEdit[i].onclick=function(){miniSurvey.optionsCheck(this,'Edit');miniSurvey.settingsCheck(this,'Edit');}
         this.answerTypesEdit[i].onchange=function(){miniSurvey.optionsCheck(this,'Edit');miniSurvey.settingsCheck(this,'Edit');}
         }
         */

        $("#answerType").change(function(r) {
            miniSurvey.optionsCheck($('#answerType').get(0));
        });

        $("#Lregex").change(function(r) {
            miniSurvey.regexCheck($('#Lregex').get(0));
        });

        $("#answerTypeEdit").change(function(r) {
            miniSurvey.optionsCheck($('#answerTypeEdit').get(0), 'Edit');
        });

        $("#LregexEdit").change(function(r) {
            miniSurvey.regexCheck($('#LregexEdit').get(0)), 'Edit';
        });

        $('#refreshButton').click( function(){ miniSurvey.refreshSurvey(); return false; } );
        $('#addQuestion').click(   function(){ miniSurvey.addQuestion(); return false; } );
        $('#editQuestion').click(  function(){ miniSurvey.addQuestion('Edit'); return false; } );
        $('#cancelEditQuestion').click(   function(){ $('#editQuestionForm').css('display','none') } );
        this.serviceURL+='cID='+this.cID+'&arHandle='+this.arHandle+'&bID='+this.bID+'&btID='+this.btID+'&';
        miniSurvey.refreshSurvey();
        $('#emailSettings').hide();
    },
    tabSetup: function(){
        $('ul#ccm-formblock-tabs li a').each( function(num,el){
            el.onclick=function(){
                var pane=this.id.replace('ccm-formblock-tab-','');
                miniSurvey.showPane(pane);
            }
        });
    },
    showPane:function(pane){
        $('ul#ccm-formblock-tabs li').each(function(num,el){ $(el).removeClass('active') });
        $(document.getElementById('ccm-formblock-tab-'+pane).parentNode).addClass('active');
        $('div.ccm-formBlockPane').each(function(num,el){ el.style.display='none'; });
        $('#ccm-formBlockPane-'+pane).css('display','block');
    },
    refreshSurvey : function(){
        $.ajax({
            url: this.serviceURL+'mode=refreshSurvey&qsID='+parseInt(this.qsID)+'&hide='+miniSurvey.hideQuestions.join(','),
            success: function(msg){ $('#miniSurveyPreviewWrap').html(msg); }
        });
        $.ajax({
            url: this.serviceURL+'mode=refreshSurvey&qsID='+parseInt(this.qsID)+'&showEdit=1&hide='+miniSurvey.hideQuestions.join(','),
            success: function(msg){	$('#miniSurveyWrap').html(msg); }
        });
    },
    optionsCheck : function(radioButton,mode){
        if(mode!='Edit'){ mode='';}
        $('#regexSettings'+mode).css('display','none');
        $('#answerSettingsNumber'+mode).css('display','none');
        $('#answerSettings'+mode).css('display','none');
        $('#answerSettingsText'+mode).css('display','none');
        $('#answerSettings2'+mode).css('display','none');
        $('#width').val('0');
        $('#height').val('0');
        $('#Lmin').val('0');
        $('#Lmax').val('0');
        $('#LminL').val('0');
        $('#LmaxL').val('100');
        $('#answerOptionsArea'+mode).css('display','none');
        if( radioButton.value=='select' || radioButton.value=='radios' || radioButton.value=='checkboxlist' || radioButton.value=='checkboxlist_hidden'){
            $('#answerOptionsArea'+mode).css('display','block');
        }
        if( radioButton.value=='email') {
            $('#emailSettings'+mode).show();
            $('#answerSettings'+mode).css('display','block');
            $('#width').val('200');
        }
        if( radioButton.value == 'field'){
            $('#regexSettings'+mode).css('display','block');
            $('#width').val('200');
        }
        if( radioButton.value=='telephone'){
            $('#regexSettings'+mode).css('display','block');
            $('#width').val('200');
            $('#Lmin').val('1000000');
            $('#Lmax').val('99999999999999');
            $('#LminL').val('7');
            $('#LmaxL').val('16');
        }
        if( radioButton.value == 'date' || radioButton.value=='datetime' || radioButton.value=='url'){
            $('#answerSettings'+mode).css('display','block');
            $('#width').val('100');
        }
        if( radioButton.value=='text'){
            $('#answerSettings2'+mode).css('display','block');
            $('#answerSettings'+mode).css('display','block');
            $('#width').val('50');
            $('#height').val('3');
        }
    },
    settingsCheck : function(radioButton,mode){
        if(mode!='Edit') mode='';
        if( radioButton.value == 'field' || radioButton.value=='telephone'){
            $('#regexSettings'+mode).css('display','block');
        }else {
            $('#regexSettings'+mode).css('display','none');
        }
        if( radioButton.value=='text'){
            $('#answerSettings2'+mode).css('display','block');
            $('#answerSettings'+mode).css('display','block');
        }
    },
    regexCheck : function(radioButton,mode){
        $('#Lmin').val('');
        $('#Lmax').val('');
        if(mode!='Edit') mode='';
        $('#answerSettingsNumber'+mode).css('display','none');
        $('#answerSettings'+mode).css('display','none');
        $('#answerSettingsText'+mode).css('display','none');
        if( radioButton.value=='number' || radioButton.value=='text'){
            if( radioButton.value=='number'){
                $('#answerSettingsNumber'+mode).css('display','block');
            }
            $('#answerSettings'+mode).css('display','block');
            $('#answerSettingsText'+mode).css('display','block');
        }
    },
    addQuestion : function(mode){
        var msqID=0;
        if(mode!='Edit') {
            mode='';
        } else {
            msqID=parseInt($('#msqID').val(), 10);
        }
        var formID = '#answerType'+mode;
        answerType = $(formID).val();
        var options = encodeURIComponent($('#answerOptions'+mode).val());
        var optionsLpl = encodeURIComponent($('#answerOptionsLpl'+mode).val());
        var optionsLnl = encodeURIComponent($('#answerOptionsLnl'+mode).val());
        var optionsLes = encodeURIComponent($('#answerOptionsLes'+mode).val());
        var Lregex = $('#Lregex'+mode).get(0).value;
        var postStr='question='+encodeURIComponent($('#question'+mode).val())+'&options='+options;
        postStr+='&Lpl='+encodeURIComponent($('#Lpl'+mode).val())+'&optionsLpl='+optionsLpl;
        postStr+='&Lnl='+encodeURIComponent($('#Lnl'+mode).val())+'&optionsLnl='+optionsLnl;
        postStr+='&Les='+encodeURIComponent($('#Les'+mode).val())+'&optionsLes='+optionsLes;
        postStr+='&Lregex='+Lregex;
        postStr+='&width='+escape($('#width'+mode).val());
        postStr+='&height='+escape($('#height'+mode).val());
        postStr+='&Lmin='+encodeURIComponent($('#Lmin'+mode).val());
        postStr+='&Lmax='+encodeURIComponent($('#Lmax'+mode).val());
        postStr+='&LminL='+escape($('#LminL'+mode).val());
        postStr+='&LmaxL='+escape($('#LmaxL'+mode).val());
        var req = $('#required'+mode+' input[value=1]').prop('checked') ? 1 : 0;
        postStr+='&required='+req;
        postStr+='&position='+escape($('#position'+mode).val());
        var form=document.getElementById('ccm-block-form');
        postStr+='&inputType='+answerType;//$('input[name=answerType'+mode+']:checked').val()
        postStr+='&msqID='+msqID+'&qsID='+parseInt(this.qsID);
        if(answerType == 'email') {
            postStr+='&send_notification_from=';
            if (mode == 'Edit') {
                fieldID = "#send_notification_from_edit";
            }
            else {
                fieldID = "#send_notification_from";
            }
            postStr+= $(fieldID).is(':checked') ? "1" : "0"
        }
        $.ajax({
            type: "POST",
            data: postStr,
            url: this.serviceURL+'mode=addQuestion&qsID='+parseInt(this.qsID),
            success: function(msg){
                eval('var jsonObj='+msg);
                if(!jsonObj){
                    alert(ccm_t('ajax-error'));
                }else if(jsonObj.noRequired){
                    alert(ccm_t('complete-required'));
                }else{
                    if(jsonObj.mode=='Edit'){
                        var questionMsg = $('#questionEditedMsg');
                        questionMsg.fadeIn();
                        setTimeout(function(){
                            questionMsg.fadeOut();
                        }, 5000);
                        if(jsonObj.hideQID){
                            miniSurvey.hideQuestions.push( miniSurvey.edit_qID ); //jsonObj.hideQID);
                            miniSurvey.edit_qID=0;
                        }
                    }else{
                        var questionMsg = $('#questionAddedMsg');
                        questionMsg.fadeIn();
                        setTimeout(function(){
                            questionMsg.fadeOut();
                        }, 5000);
                        //miniSurvey.saveOrder();
                    }
                    $('#editQuestionForm').css('display','none');
                    miniSurvey.qsID=jsonObj.qsID;
                    miniSurvey.ignoreQuestionId(jsonObj.msqID);
                    $('#qsID').val(jsonObj.qsID);
                    miniSurvey.resetQuestion();
                    miniSurvey.refreshSurvey();
                    //miniSurvey.showPane('preview');
                }
            }
        });
    },
    //prevent duplication of these questions, for block question versioning
    ignoreQuestionId:function(msqID){
        var msqID, ignoreEl=$('#ccm-ignoreQuestionIDs');
        if(ignoreEl.val()) msqIDs=ignoreEl.val().split(',');
        else msqIDs=[];
        msqIDs.push( parseInt(msqID, 10) );
        ignoreEl.val( msqIDs.join(',') );
    },
    reloadQuestion : function(qID){

        $.ajax({
            url: this.serviceURL+"mode=getQuestion&qsID="+parseInt(this.qsID)+'&qID='+parseInt(qID),
            success: function(msg){
                eval('var jsonObj='+msg);
                $('#editQuestionForm').css('display','block')
                $('#questionEdit').val(jsonObj.question);
                $('#answerOptionsEdit').val(jsonObj.optionVals.replace(/%%/g,"\r\n") );
                $('#widthEdit').val(jsonObj.width);
                $('#heightEdit').val(jsonObj.height);
                $('#LplEdit').val(jsonObj.Lpl);
                $('#answerOptionsLplEdit').val(jsonObj.optionsLpl.replace(/%%/g,"\r\n") );
                $('#LnlEdit').val(jsonObj.Lnl);
                $('#answerOptionsLnlEdit').val(jsonObj.optionsLnl.replace(/%%/g,"\r\n") );
                $('#LesEdit').val(jsonObj.Les);
                $('#answerOptionsLesEdit').val(jsonObj.optionsLes.replace(/%%/g,"\r\n") );
                $('#LregexEdit').val(jsonObj.Lregex);
                $('#LminEdit').val(jsonObj.Lmin);
                $('#LmaxEdit').val(jsonObj.Lmax);
                $('#LminLEdit').val(jsonObj.LminL);
                $('#LmaxLEdit').val(jsonObj.LmaxL);
                $('#positionEdit').val(jsonObj.position);
                if (parseInt(jsonObj.required, 10) == 1) {
                    $('#requiredEdit input[value=1]').prop('checked', true);
                    $('#requiredEdit input[value=0]').prop('checked', false);
                } else {
                    $('#requiredEdit input[value=1]').prop('checked', false);
                    $('#requiredEdit input[value=0]').prop('checked', true);
                }

                if(jsonObj.inputType == 'email') {
                    var options = jsonObj.optionVals.split(";");
                    for (var i = 0; i < options.length; i++) {
                        key_val = options[i].split('::');
                        if(key_val.length == 2) {
                            if (key_val[0] == 'send_notification_from') {
                                if (key_val[1] == 1) {
                                    $('.send_notification_from input').prop('checked', true);
                                } else {
                                    $('.send_notification_from input').prop('checked', false);
                                }
                            }
                        }
                    }
                }

                $('#msqID').val(jsonObj.msqID);
                $('#answerTypeEdit').val(jsonObj.inputType);
                miniSurvey.optionsCheck($('#answerTypeEdit').get(0), 'Edit');
                miniSurvey.settingsCheck($('#answerTypeEdit').get(0), 'Edit');

                if(parseInt(jsonObj.bID)>0)
                    miniSurvey.edit_qID = parseInt(qID) ;
                $('.miniSurveyOptions').first().closest('.ui-dialog-content').get(0).scrollTop = 0;
            }
        });
    },
    //prevent duplication of these questions, for block question versioning
    pendingDeleteQuestionId:function(msqID){
        var msqID, el=$('#ccm-pendingDeleteIDs');
        if(el.val()) msqIDs=ignoreEl.val().split(',');
        else msqIDs=[];
        msqIDs.push( parseInt(msqID, 10) );
        el.val( msqIDs.join(',') );
    },
    hideQuestions : [],
    deleteQuestion : function(el,msqID,qID){
        if(confirm(ccm_t('delete-question'))) {
            $.ajax({
                url: this.serviceURL+"mode=delQuestion&qsID="+parseInt(this.qsID)+'&msqID='+parseInt(msqID),
                success: function(msg){	miniSurvey.resetQuestion(); miniSurvey.refreshSurvey();  }
            });

            miniSurvey.ignoreQuestionId(msqID);
            miniSurvey.hideQuestions.push(qID);
            miniSurvey.pendingDeleteQuestionId(msqID)
        }
    },
    resetQuestion : function(){
        $('#question').val('');
        $('#answerOptions').val('');
        $('#width').val('50');
        $('#height').val('3');
        $('#Lpl').val('');
        $('#answerOptionsLpl').val('');
        $('#Lnl').val('');
        $('#answerOptionsLnl').val('');
        $('#Les').val('');
        $('#answerOptionsLes').val('');
        $('#Lregex').val('');
        $('#Lmin').val('0');
        $('#Lmax').val('0');
        $('#LminL').val('0');
        $('#LmaxL').val('100');
        $('#msqID').val('');
        $('#answerType').val('field').change();
        $('#answerOptionsArea').hide();
        $('#answerSettings').hide();
        $('#required input').prop('checked', false);
    },

    validate:function(){
        var failed=0;

        var n=$('#surveyName');
        if( !n || parseInt(n.val().length, 10)==0 ){
            alert(ccm_t('form-name'));
            this.showPane('options');
            n.focus();
            failed=1;
        }

        var Qs=$('.miniSurveyQuestionRow');
        if( !Qs || parseInt(Qs.length, 10)<1 ){
            alert(ccm_t('form-min-1'));
            failed=1;
        }

        if(failed){
            ccm_isBlockError=1;
            return false;
        }
        return true;
    },

    moveUp:function(el,thisQID){
        var qIDs=this.serialize();
        var previousQID=0;
        for(var i=0;i<qIDs.length;i++){
            if(qIDs[i]==thisQID){
                if(previousQID==0) break;
                $('#miniSurveyQuestionRow'+thisQID).after($('#miniSurveyQuestionRow'+previousQID));
                break;
            }
            previousQID=qIDs[i];
        }
        this.saveOrder();
    },
    moveDown:function(el,thisQID){
        var qIDs=this.serialize();
        var thisQIDfound=0;
        for(var i=0;i<qIDs.length;i++){
            if(qIDs[i]==thisQID){
                thisQIDfound=1;
                continue;
            }
            if(thisQIDfound){
                $('#miniSurveyQuestionRow'+qIDs[i]).after($('#miniSurveyQuestionRow'+thisQID));
                break;
            }
        }
        this.saveOrder();
    },
    serialize:function(){
        var t = document.getElementById("miniSurveyPreviewTable");
        var qIDs=[];
        for(var i=0;i<t.childNodes.length;i++){
            if( t.childNodes[i].className && t.childNodes[i].className.indexOf('miniSurveyQuestionRow')>=0 ){
                var qID=t.childNodes[i].id.substr('miniSurveyQuestionRow'.length);
                qIDs.push(qID);
            }
        }
        return qIDs;
    },
    saveOrder:function(){
        var postStr='qIDs='+this.serialize().join(',')+'&qsID='+parseInt(this.qsID);
        $.ajax({
            type: "POST",
            data: postStr,
            url: this.serviceURL+"mode=reorderQuestions",
            success: function(msg){
                miniSurvey.refreshSurvey();
            }
        });
    }
};
ccmValidateBlockForm = function() { return miniSurvey.validate(); };
$(document).ready(function(){
    //miniSurvey.init();
    /* TODO hackzors, this shouldnt be necessary */
    $('#ccm-block-form').closest('div').addClass('ccm-ui');
});
