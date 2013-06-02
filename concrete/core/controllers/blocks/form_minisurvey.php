<?php
defined('C5_EXECUTE') or die("Access Denied.");
/**
 * @package Blocks
 * @subpackage Form
 * @author Tony Trupp <tony@concrete5.org>
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2012 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */
class Concrete5_Controller_Block_FormMinisurvey {

    public $btTable = 'btForm';
    public $btQuestionsTablename = 'btFormQuestions';
    public $btAnswerSetTablename = 'btFormAnswerSet';
    public $btAnswersTablename = 'btFormAnswers';
    public $check_field = array();
    public $timefields = array();
    public $Lregex = "";
    public $Lmin = 0;
    public $Lmax = 0;
    public $LminL = 0;
    public $LmaxL = 0;
    public $divcheckbox = 0;
    public $language;          // change in load servey and load inputTypes

    public $lastSavedMsqID=0;
    public $lastSavedqID=0;

    function __construct(){
        $db = Loader::db();
        $this->db=$db;
    }

    function addEditQuestion($values,$withOutput=1){
        $jsonVals=array();
        $values['options']=str_replace(array("\r","\n"),'%%',$values['options']);
        $values['optionsLpl']=str_replace(array("\r","\n"),'%%',$values['optionsLpl']);
        $values['optionsLnl']=str_replace(array("\r","\n"),'%%',$values['optionsLnl']);
        $values['optionsLes']=str_replace(array("\r","\n"),'%%',$values['optionsLes']);
        if(strtolower($values['inputType'])=='undefined')  $values['inputType']='field';

        //set question set id, or create a new one if none exists
        if(intval($values['qsID'])==0) $values['qsID']=time();

        //validation
        if( strlen($values['question'])==0 || strlen($values['inputType'])==0  || $values['inputType']=='null' ){
            //complete required fields
            $jsonVals['success']=0;
            $jsonVals['noRequired']=1;
        }else{

            if( intval($values['msqID']) ){
                $jsonVals['mode']='"Edit"';

                //questions that are edited are given a placeholder row in btFormQuestions with bID=0, until a bID is assign on block update
                $pendingEditExists = $this->db->getOne( "select count(*) as total from btFormQuestions where bID=0 AND msqID=".intval($values['msqID']) );

                //hideQID tells the interface to hide the old version of the question in the meantime
                $vals=array( intval($values['msqID']));
                $jsonVals['hideQID']=intval($this->db->GetOne("SELECT MAX(qID) FROM btFormQuestions WHERE bID!=0 AND msqID=?",$vals));
            }else{
                $jsonVals['mode']='"Add"';
            }

            //see if the 'send notification from' checkbox is checked and save this to the options field
            if($values['inputType'] == 'email') {
                $options = array();
                if(array_key_exists('send_notification_from', $values) && $values['send_notification_from'] == 1) {
                    $options['send_notification_from'] = 1;
                } else {
                    $options['send_notification_from'] = 0;
                }
                $values['options'] = serialize($options);
            }
            if( $pendingEditExists ){
                $width = $height = 0;
                if ($values['inputType'] == 'text'){
                    $width  = $this->limitRange(intval($values['width']), 20, 500);
                    $height = $this->limitRange(intval($values['height']), 1, 100);
                }
                $dataValues=array(intval($values['qsID']), trim($values['question']), trim($values['Lpl']), trim($values['Lnl']), trim($values['Les']), $values['inputType'], trim($values['Lregex']),
                    $values['options'], $values['optionsLpl'], $values['optionsLnl'], $values['optionsLes'], intval($values['position']), $width, $height, trim($values['Lmin']), trim($values['Lmax']), intval($values['LminL']), intval($values['LmaxL']), intval($values['required']), intval($values['msqID']) );
                $sql='UPDATE btFormQuestions SET questionSetId=?, question=?, Lpl=?, Lnl=?, Les=?, inputType=?, Lregex=?, options=?, optionsLpl=?, optionsLnl=?, optionsLes=?, position=?, width=?, height=?, Lmin=?, Lmax=?, LminL=?, LmaxL=?, required=? WHERE msqID=? AND bID=0';
            }else{
                if( !isset($values['position']) ) $values['position']=1000;
                if(!intval($values['msqID']))
                    $values['msqID']=intval($this->db->GetOne("SELECT MAX(msqID) FROM btFormQuestions")+1);
                $dataValues=array($values['msqID'],intval($values['qsID']), trim($values['question']), trim($values['Lpl']), trim($values['Lnl']), trim($values['Les']), $values['inputType'], trim($values['Lregex']),
                    $values['options'], $values['optionsLpl'], $values['optionsLnl'], $values['optionsLes'], intval($values['position']), intval($values['width']), intval($values['height']), trim($values['Lmax']), trim($values['Lmin']), intval($values['LminL']), intval($values['LmaxL']), intval($values['required']) );
                $sql='INSERT INTO btFormQuestions (msqID,questionSetId,question,Lpl,Lnl,Les,inputType,Lregex,options,optionsLpl,optionsLnl,optionsLes,position,width,height,Lmin,Lmax,LminL,LmaxL,required) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            }
            $result=$this->db->query($sql,$dataValues);
            $this->lastSavedMsqID=intval($values['msqID']);
            $this->lastSavedqID=intval($this->db->GetOne("SELECT MAX(qID) FROM btFormQuestions WHERE bID=0 AND msqID=?", array($values['msqID']) ));
            $jsonVals['qID']=$this->lastSavedqID;
            $jsonVals['success']=1;
        }

        $jsonVals['qsID']=$values['qsID'];
        $jsonVals['msqID']=intval($values['msqID']);
        //create json response object
        $jsonPairs=array();
        foreach($jsonVals as $key=>$val) $jsonPairs[]=$key.':'.$val;
        if($withOutput) echo '{'.join(',',$jsonPairs).'}';
    }

    function getQuestionInfo($qsID,$qID){
        $questionRS=$this->db->query('SELECT * FROM btFormQuestions WHERE questionSetId='.intval($qsID).' AND qID='.intval($qID).' LIMIT 1' );
        $questionRow=$questionRS->fetchRow();
        $jsonPairs=array();
        foreach($questionRow as $key=>$val){
            if($key=='options') {
                $key='optionVals';
                if($questionRow['inputType'] == 'email') {
                    $options = unserialize($val);
                    if (is_array($options)) {
                        foreach($options as $o_key => $o_val) {
                            $val = $o_key."::".$o_val.";";
                        }
                    }
                }
            }

            $jsonPairs[]=$key.':"'.str_replace(array("\r","\n"),'%%',addslashes($val)).'"';
        }
        echo '{'.join(',',$jsonPairs).'}';
    }

    function deleteQuestion($qsID,$msqID){
        $sql='DELETE FROM btFormQuestions WHERE questionSetId='.intval($qsID).' AND msqID='.intval($msqID).' AND bID=0';
        $this->db->query($sql, $dataValues);
    }

    function loadQuestions($qsID, $bID=0, $showPending=0 ){
        $db = Loader::db();
        if( intval($bID) ){
            $bIDClause=' AND ( bID='.intval($bID).' ';
            if( $showPending )
                $bIDClause.=' OR bID=0) ';
            else $bIDClause.=' ) ';
        }
        return $db->query('SELECT * FROM btFormQuestions WHERE questionSetId='.intval($qsID).' '.$bIDClause.' ORDER BY position, msqID');
    }

    static function getAnswerCount($qsID){
        $db = Loader::db();
        return $db->getOne( 'SELECT count(*) FROM btFormAnswerSet WHERE questionSetId='.intval($qsID) );
    }

    function loadSurvey( $qsID, $showEdit=false, $bID=0, $hideQIDs=array(), $showPending=0 ){
        $check_field = $_POST['check_field'];


        $language = 'Lpl';  //'question'; //'Lnl'; // 'Les';
        $buttonsend = ' Wysla&#263 ';   //' Submit ';  //' Verzenden ';  //' Enviar ';

        //loading questions
        $questionsRS=$this->loadQuestions( $qsID, $bID, $showPending);
        if(!$showEdit){
            echo '<table class="formBlockSurveyTable">';
            //echo $check_field . '<all';
            while( $questionRow=$questionsRS->fetchRow() ){

                if( in_array($questionRow['qID'], $hideQIDs) ) continue;

                $tQuestion = ' id="' . $questionRow[$language] . '"';
                $tQuestion2 = ' id="result' . $questionRow[$language] . '"';

                $treq = $questionRow["required"];

                $val = $questionRow[$language];
                $Lregex = $questionRow['Lregex'];
                $Lmin = $questionRow['Lmin'];
                $Lmax = $questionRow['Lmax'];
                $LminL = $questionRow['LminL'];
                $LmaxL = $questionRow['LmaxL'];
                $xrow = $questionRow['msqID'];

                $itemp = "normal";

                if ($questionRow['inputType'] == 'checkboxlist'){$itemp = "result";}
                if ($questionRow['inputType'] == 'checkboxlist_hidden'){$itemp = "result";}

                if($itemp == "normal"){
                    if($treq <> 1){
                        $treq = 2;
                    }
                }
                else{
                    $Lregex = $ipt . '" '. $tQuestion2;
                }
                $requiredSymbol=($questionRow['required'])?'&nbsp;<span class="required">*</span>':'';

                echo '<tr>
						        <td valign="top" width="200" class="question"><label req="' . $treq . '" for="Question'.intval($questionRow['msqID']).'"' . $ipt . '>'.$questionRow[$language].''.$requiredSymbol.'</label></td>
						        <td valign="top" width="350">'.$this->loadInputType($questionRow,showEdit, $Lregex, $Lmin, $Lmax, $LminL, $LmaxL);
                echo '<span id="Question' . $questionRow["msqID"] . '_' . $questionRow['Lregex'] . '"></span></td>';
                echo   '</tr>';

            }
            $surveyBlockInfo = $this->getMiniSurveyBlockInfoByQuestionId($qsID,intval($bID));

            if($surveyBlockInfo['displayCaptcha']) {
                echo '<tr><td colspan="2">';
                $captcha = Loader::helper('validation/captcha');
                echo $captcha->label();
                echo '</td></tr><tr><td>&nbsp;</td><td>';

                $captcha->showInput();
                $captcha->display();

                //echo isset($errors['captcha'])?'<span class="error">' . $errors['captcha'] . '</span>':'';
                echo '</td></tr>';

            }

            echo '<tr><td>&nbsp;</td><td><input class="formBlockSubmitButton ccm-input-button" name="Submit" type="submit" value="'.t($buttonsend).'" />';
            echo '</table>';

        }else{


            echo '<div id="miniSurveyTableWrap"><div id="miniSurveyPreviewTable" class="miniSurveyTable">';
            while( $questionRow=$questionsRS->fetchRow() ){

                if( in_array($questionRow['qID'], $hideQIDs) ) continue;

                $requiredSymbol=($questionRow['required'])?'<span class="required">*</span>':'';
                ?>
                <div id="miniSurveyQuestionRow<?php  echo $questionRow['msqID']?>" class="miniSurveyQuestionRow">
                    <div class="miniSurveyQuestion"><?php  echo $questionRow['question'].' '.$requiredSymbol; ?></div>
                    <?php   /* <div class="miniSurveyResponse"><?php  echo $this->loadInputType($questionRow,$showEdit)?></div> */ ?>
                    <div class="miniSurveyOptions">
                        <div style="float:right">
                            <a href="javascript:void(0)" onclick="miniSurvey.moveUp(this,<?php  echo $questionRow['msqID']?>);return false" class="moveUpLink"></a>
                            <a href="javascript:void(0)" onclick="miniSurvey.moveDown(this,<?php  echo $questionRow['msqID']?>);return false" class="moveDownLink"></a>
                        </div>
                        <a href="javascript:void(0)" onclick="miniSurvey.reloadQuestion(<?php echo intval($questionRow['qID']) ?>);return false"><?php  echo t('edit')?></a> &nbsp;&nbsp;
                        <a href="javascript:void(0)" onclick="miniSurvey.deleteQuestion(this,<?php echo intval($questionRow['msqID']) ?>,<?php echo intval($questionRow['qID'])?>);return false"><?php echo  t('remove')?></a>
                    </div>
                    <div class="miniSurveySpacer"></div>
                </div>
            <?php   }
            echo '</div></div>';
        }
    }

    function loadInputType($questionData,$showEdit,$Lregex, $Lmin, $Lmax, $LminL, $LmaxL){

        $language = 'Lpl';  //'question'; //'Lnl'; // 'Les';

        if($language == 'question'){$options=explode('%%',$questionData['options']);}else{$options=explode('%%',$questionData['options' . $language]);}
        $msqID=intval($questionData['msqID']);
        $datetime = loader::helper('form/date_time');
        $onkeyup = "";
        $classra = "none" ;
        if($questionData['Lregex'] == 'text'){
            $classra = 'ra' . $Lregex ;
            $onkeyup = "this.value = this.value.replace(/[^a-zA-Z]/g, '')";
            $check_field['Question'.$msqID] = 5;
        }
        if($questionData['inputType'] == 'date'){
            $classra = 'ra' . $Lregex ;
            $onkeyup = "this.value = this.value.replace(/[^-/0-9]/g, '')";
            $check_field['Question'.$msqID] = 5;
        }
        if($questionData['Lregex'] == 'number'){
            $classra = 'ra' . $Lregex ;
            $onkeyup = "this.value = this.value.replace(/[^0-9]/g, '')";
            $check_field['Question'.$msqID] = 5;
        }
        if($questionData['inputType'] == 'email'){
            $classra = 'ra' . $Lregex ;
            $onkeyup = "this.value = this.value.replace(/[^-a-zA-Z0-9._@]/g, '')";
            $check_field['Question'.$msqID] = 5;
        }
        switch($questionData['inputType']){
            case 'checkboxlist_hidden':
                // this is looking really crappy so i'm going to make it behave the same way all the time - andrew
                /*
                if (count($options) == 1){
                    if(strlen(trim($options[0]))==0) continue;
                    $checked=($_REQUEST['Question'.$msqID.'_0']==trim($options[0]))?'checked':'';
                    $html.= '<input name="Question'.$msqID.'_0" type="checkbox" value="'.trim($options[0]).'" '.$checked.' />';
                }else{
                */
                $html.= '<div class="checkboxList">' . "\r\n";
                $spaces = "&nbsp;";
                for ($i = 0; $i < count($options); $i++) {
                    if(strlen(trim($options[$i]))==0) continue;
                    $checked = ($_REQUEST['Question'.$msqID.'_'.$i]==trim($options[$i]))?'checked':'';
                    if($i == 0){$divcheckbox = 3;
                        $html.= '<span class="checkboxPair"><input name="Question'.$msqID.'_'.$i.'" type="checkbox" onclick="doWhat(this.checked,this.value);" value="'.trim($options[$i]).'" '.$checked.'><span id="Question'.$msqID . '">&nbsp;'.$options[$i].'</span></input></div>
                                 <div class="checkboxItem" id="result' . $options[$i] . '" style="display:none">'."\r\n";
                    }else{
                        if($options[$i] == "<"){$divcheckbox = 2; $html.= '<div class="checkboxPair" id="result2' . $options[$i - 1] .'" style="display:none">'; $spaces = $spaces . '&nbsp;&nbsp;';}
                        if($options[$i] == ">"){$divcheckbox = 1; $html.= '</div>'; $spaces = '&nbsp;';}
                        if($options[$i] == "("){$divcheckbox = 6; $html.= '<div class="checkboxPair" id="result2' . $options[$i - 1] .'" style="display:none"><h6>' . $options[$i + 1] . '</h6>'; $spaces = $spaces . '&nbsp;&nbsp;';}
                        if($options[$i] == ")"){$divcheckbox = 2; $html.= '</div>'; $spaces = '&nbsp;';}
                        if($divcheckbox == 3){$html.= '<div class="checkboxPair"><span class="spaces">' . $spaces . '</span><input name="Question'.$msqID.'_'.$i.'" type="checkbox" onclick="doWhat2(this.checked,this.value);" value="'.trim($options[$i]).'" '.$checked.'>&nbsp;'.$options[$i].'</input></div>'."\r\n";}
                        if($divcheckbox == 4){$html.= '<div class="numbers-row"><span class="spaces">' . $spaces . '</span><img class="inc button" onclick="buttonclick(Question'.$msqID.'_'.$i.', 1);" src="http://bizztemp.nl/buttonup.png"></img><img class="dec button" onclick="buttonclick(Question'.$msqID.'_'.$i.', 2);" src="http://bizztemp.nl/buttondw.png"></img><input id="Question'.$msqID.'_'.$i.'" type="text" max="100" maxlength="3" style="width:30px" title="'.trim($options[$i]).'">&nbsp;'.$options[$i].'</input></div>'."\r\n";}
                    }
                    if($divcheckbox <= 2){$divcheckbox = 3;}
                    if($divcheckbox == 5){$divcheckbox = 4;}
                    if($divcheckbox == 6){$divcheckbox = 5;}
                }

                $html.= '</div></span></div>';
                //}
                return $html;

            case 'checkboxlist':
                // this is looking really crappy so i'm going to make it behave the same way all the time - andrew
                /*
                if (count($options) == 1){
                if(strlen(trim($options[0]))==0) continue;
                $checked=($_REQUEST['Question'.$msqID.'_0']==trim($options[0]))?'checked':'';
                $html.= '<input name="Question'.$msqID.'_0" id="Question'.$msqID.'_0" type="checkbox" value="'.trim($options[0]).'" '.$checked.'></input>';
                }else{
                */
                $html.= '<div class="checkboxList">'."\r\n";
                $Lregex = "'" . $Lregex . "'";
                for ($i = 0; $i < count($options); $i++) {
                    if(strlen(trim($options[$i]))==0) continue;
                    $checked=($_REQUEST['Question'.$msqID.'_'.$i]==trim($options[$i]))?'checked':'';
                    $html.= '  <div class="checkboxPair"><input name="Question'.$msqID.'_'.$i.'" id="Question'.$msqID.'_'.$i.'" type="checkbox" onclick="doWhat(this.checked,this.value);" value="'.trim($options[$i]).'" '.$checked.'>&nbsp;'.$options[$i].'</input></div>'."\r\n";
                }
                $html.= '</div>';
                //}
                return $html;


            case 'select':
                if($this->frontEndMode){
                    $selected=(!$_REQUEST['Question'.$msqID])?'selected="selected"':'';
                    $html.= '<option value="" '.$selected.'>----</option>';
                }
                foreach($options as $option){
                    $checked=($_REQUEST['Question'.$msqID]==trim($option))?'selected="selected"':'';
                    $html.= '<option '.$checked.'>'.trim($option).'</option>';
                }
                return '<select name="Question'.$msqID.'" id="Question'.$msqID.'" >'.$html.'</select>';

            case 'radios':
                foreach($options as $option){
                    if(strlen(trim($option))==0) continue;
                    $checked=($_REQUEST['Question'.$msqID]==trim($option))?'checked':'';
                    $html.= '<div class="radioPair"><input name="Question'.$msqID.'" type="radio" value="'.trim($option).'" '.$checked.' />&nbsp;'.$option.'</div>';
                }
                return $html;

            case 'fileupload':
                $html='<input type="file" name="Question'.$msqID.'" id="Question'.$msqID.'" />';
                return $html;

            case 'text':
                $val=($_REQUEST['Question'.$msqID])?Loader::helper('text')->entities($_REQUEST['Question'.$msqID]):'';
                return '<textarea class="' . $classra . '" name="Question'.$msqID.'" id="Question'.$msqID.'" onKeyUp="' . $onkeyup . '" max="' . $Lmax . '" min="'. $Lmin . '" maxlength="' . $LmaxL .'" minlength="'. $LminL .'" cols="'.$questionData['width'].'px" rows="'.$questionData['height'] . '" >'.$val.'</textarea>';
            case 'url':
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return '<input class="' . $classra . 'url" name="Question'.$msqID.'" id="Question'.$msqID.'" type="url" style="width:'.$questionData['width'].'" value="'.stripslashes(htmlspecialchars($val)).'" />';
            case 'telephone':
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return '<input class="' . $classra . '" name="Question'.$msqID.'" id="Question'.$msqID.'" type="tel" onKeyUp="' . $onkeyup . '" max="' . $Lmax . '" min="'. $Lmin . '" maxlength="' . $LmaxL .'" minlength="'. $LminL .'" style="width:'.$questionData['width'].'px" value="'.stripslashes(htmlspecialchars($val)).'" />';
            case 'email':
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return '<input class="' . $classra . '" name="Question'.$msqID.'" id="Question'.$msqID.'" type="email" onKeyUp="' . $onkeyup . '" max="' . $Lmax . '" min="'. $Lmin . '" maxlength="' . $LmaxL .'" minlength="'. $LminL .'" style="width:'.$questionData['width'].'px" value="'.stripslashes(htmlspecialchars($val)).'" />';
            case 'date':
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return $datetime->date('Question'.$msqID,($val));
            case 'datetime':
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return $datetime->datetime('Question'.$msqID,$val);
            case 'field':
            default:
                $val=($_REQUEST['Question'.$msqID])?$_REQUEST['Question'.$msqID]:'';
                return '<input class="' . $classra . '" name="Question'.$msqID.'" id="Question'.$msqID.'" type="text" onKeyUp="' . $onkeyup . '" max="' . $Lmax . '" min="'. $Lmin . '" maxlength="' . $LmaxL .'" minlength="'. $LminL .'" style="width:'.$questionData['width'].'px" value="'.stripslashes(htmlspecialchars($val)).'" />';

        }
    }


    function getMiniSurveyBlockInfo($bID){
        $rs=$this->db->query('SELECT * FROM btForm WHERE bID='.intval($bID).' LIMIT 1' );
        return $rs->fetchRow();
    }

    function getMiniSurveyBlockInfoByQuestionId($qsID,$bID=0){
        $sql='SELECT * FROM btForm WHERE questionSetId='.intval($qsID);
        if(intval($bID)>0) $sql.=' AND bID='.$bID;
        $sql.=' LIMIT 1';
        $rs=$this->db->query( $sql );
        return $rs->fetchRow();
    }

    function reorderQuestions($qsID=0,$qIDs){
        $qIDs=explode(',',$qIDs);
        if(!is_array($qIDs)) $qIDs=array($qIDs);
        $positionNum=0;
        foreach($qIDs as $qID){
            $vals=array( $positionNum,intval($qID), intval($qsID) );
            $sql='UPDATE btFormQuestions SET position=? WHERE msqID=? AND questionSetId=?';
            $rs=$this->db->query($sql,$vals);
            $positionNum++;
        }
    }

    function limitRange($val, $min, $max){
        $val = ($val < $min) ? $min : $val;
        $val = ($val > $max) ? $max : $val;
        return $val;
    }

    //Run on Form block edit
    static function questionCleanup( $qsID=0, $bID=0 ){
        $db = Loader::db();

        //First make sure that the bID column has been set for this questionSetId (for backwards compatibility)
        $vals=array( intval($qsID) );
        $questionsWithBIDs=$db->getOne('SELECT count(*) FROM btFormQuestions WHERE bID!=0 AND questionSetId=? ',$vals);

        //form block was just upgraded, so set the bID column
        if(!$questionsWithBIDs){
            $vals=array( intval($bID), intval($qsID) );
            $rs=$db->query('UPDATE btFormQuestions SET bID=? WHERE bID=0 AND questionSetId=?',$vals);
            return;
        }

        //Then remove all temp/placeholder questions for this questionSetId that haven't been assigned to a block
        $vals=array( intval($qsID) );
        $rs=$db->query('DELETE FROM btFormQuestions WHERE bID=0 AND questionSetId=?',$vals);
    }
}	