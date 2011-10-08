<?php
/**
 * ÖÐÎÄ·Ö´Ê²Ù×÷Àà
 * @author wangcanjia
 *
 */
class segment {
	public $rank_dic = array();
	public $one_name_dic = array();
	public $two_name_dic = array();
	public $new_word = array();
	public $source_string = '';
	public $result_string = '';
	public $split_char = ' '; //·Ö¸ô·û
	public $SplitLen = 4; //±£Áô´Ê³¤¶È
	public $especial_char = "ºÍ|µÄ|ÊÇ";
	public $new_word_limit = "ÔÚ|µÄ|Óë|»ò|¾Í|Äã|ÎÒ|Ëû|Ëý|ÓÐ|ÁË|ÊÇ|Æä|ÄÜ|¶Ô|µØ";
	public $common_unit = "Äê|ÔÂ|ÈÕ|Ê±|·Ö|Ãë|µã|Ôª|°Ù|Ç§|Íò|ÒÚ|Î»|Á¾";
	public $cn_number = "£°|£±|£²|£³|£´|£µ|£¶|£·|£¸|£¹|£«|£­|£¥|£®|£á|£â|£ã|£ä|£å|£æ|£ç|£è|£é|£ê|£ë|£ì|£í|£î|£ï|£ð|£ñ|£ò|£ó |£ô|£õ|£ö|£÷|£ø|£ù|£ú|£Á|£Ã|£Ä|£Å|£Æ|£Ç|£È|£É|£Ê|£Ë|£Ì|£Í|£Î|£Ï|£Ð|£Ñ|£Ò|£Ó|£Ô|£Õ|£Ö|£×|£Ø|£Ù|£Ú";
	public $cn_sg_num = "Ò»|¶þ|Èý|ËÄ|Îå|Áù|Æß|°Ë|¾Å|Ê®|°Ù|Ç§|Íò|ÒÚ|Êý";
	public $max_len = 13; //´Êµä×î´ó 7 ÖÐÎÄ×Ö£¬ÕâÀïµÄÊýÖµÎª×Ö½ÚÊý×éµÄ×î´óË÷Òý
	public $min_len = 3;  //×îÐ¡ 2 ÖÐÎÄ×Ö£¬ÕâÀïµÄÊýÖµÎª×Ö½ÚÊý×éµÄ×î´óË÷Òý
	public $cn_two_name = "¶ËÄ¾ ÄÏ¹¬ ÚÛóÎ ÐùÔ¯ Áîºü ÖÓÀë ãÌÇð ³¤Ëï ÏÊÓÚ ÓîÎÄ Ë¾Í½ Ë¾¿Õ ÉÏ¹Ù Å·Ñô ¹«Ëï Î÷ÃÅ ¶«ÃÅ ×óÇð ¶«¹ù ºôÑÓ Ä½ÈÝ Ë¾Âí ÏÄºî Öî¸ð ¶«·½ ºÕÁ¬ »Ê¸¦ Î¾³Ù ÉêÍÀ";
	public $cn_one_name = "ÕÔÇ®ËïÀîÖÜÎâÖ£Íõ·ë³ÂñÒÎÀ½¯Éòº«ÑîÖìÇØÓÈÐíºÎÂÀÊ©ÕÅ¿×²ÜÑÏ»ª½ðÎºÌÕ½ªÆÝÐ»×ÞÓ÷°ØË®ñ¼ÕÂÔÆËÕÅË¸ðÞÉ·¶ÅíÀÉÂ³Î¤²ýÂíÃç·ï»¨·½ÓáÈÎÔ¬ÁøÛº±«Ê·ÌÆ·ÑÁ®á¯Ñ¦À×ºØÄßÌÀëøÒóÂÞ±ÏºÂÚù°²³£ÀÖÓÚÊ±¸µÆ¤¿¨Æë¿µÎéÓàÔª²·¹ËÃÏÆ½»ÆÄÂÏôÒüÒ¦ÉÛ¿°ÍôÆîÃ«ÓíµÒÃ×±´Ã÷ê°¼Æ·ü³É´÷Ì¸ËÎÃ©ÅÓÐÜ¼ÍÊæÇüÏî×£¶­Á»¶ÅÈîÀ¶ãÉÏ¯¼¾ÂéÇ¿¼ÖÂ·Â¦Î£½­Í¯ÑÕ¹ùÃ·Ê¢ÁÖµóÖÓÐìÇñÂæ¸ßÏÄ²ÌÌï·®ºúÁè»ôÓÝÍòÖ§¿Â¾Ì¹ÜÂ¬Äª¾­·¿ôÃçÑ¸É½âÓ¦×ÚÐû¶¡êÚµËÓôµ¥º¼ºé°üÖî×óÊ¯´Þ¼ªÅ¥¹¨³ÌïúÐÏ»¬ÅáÂ½ÈÙÎÌÜ÷Ñòì¶»ÝÕçÎº¼Ó·âÜÇôà´¢½ù¼³ÚûÃÓËÉ¾®¶Î¸»Î×ÎÚ½¹°Í¹­ÄÁÚó¹È³µºîåµÅîÈ«Û­°àÑöÇïÖÙÒÁ¹¬Äþ³ðèï±©¸Êî×À÷ÈÖ×æÎä·ûÁõ½ªÕ²ÊøÁúÒ¶ÐÒË¾ÉØÛ¬Àè¼»±¡Ó¡ËÞ°×»³ÆÑÌ¨´Ó¶õË÷ÏÌ¼®Àµ×¿ÝþÍÀÃÉ³ØÇÇÒõÓôñãÄÜ²ÔË«ÎÅÝ·µ³µÔÌ·¹±ÀÍåÌ¼§Éê·ö¶ÂÈ½Ô×ÛªÓºàSè³É£¹ðå§Å£ÊÙÍ¨±ßìèÑà¼½Û£ÆÖÉÐÅ©ÎÂ±ð×¯êÌ²ñµÔÑÖ³äÄ½Á¬ÈãÏ°»Â°¬ÓãÈÝÏò¹ÅÒ×É÷¸êÁÎ¸ýÖÕôß¾Óºâ²½¶¼¹¢Âúºë¿ï¹úÎÄ¿Ü¹ãÂ»ãÚ¶«Å¹ì¯ÎÖÀûÎµÔ½ÙçÂ¡Ê¦¹®ØÇÄôêË¹´°½ÈÚÀäö¤ÐÁãÛÄÇ¼òÈÄ¿ÕÔøÉ³Ðë·á³²¹ØØáÏà²éºó½­ÓÎóÃ";
  
	function __construct($loaddic=true) {
  	if($loaddic) {
  	  for($i=0;$i<strlen($this->cn_one_name);$i++){
  		  $this->one_name_dic[$this->cn_one_name[$i].$this->cn_one_name[$i+1]] = 1;
  		  $i++;
  	  }
  	  $twoname = explode(" ",$this->cn_two_name);
  	  foreach($twoname as $n){ $this->two_name_dic[$n] = 1; }
  	  unset($twoname);
  	  unset($this->cn_two_name);
  	  unset($this->cn_one_name);
  	  $dicfile = PC_PATH.'libs'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'dict'.DIRECTORY_SEPARATOR.'dict.csv';
  	  $fp = fopen($dicfile,'r');
  	  while($line = fgets($fp,64)){
  		  $ws = explode(' ',$line);
  		  $this->rank_dic[strlen($ws[0])][$ws[0]] = $ws[1];
  	  }
  	  fclose($fp);
    }
  }

  function clear() {
  	unset($this->rank_dic);
  }
  function get_source($str) {
  	if(CHARSET == 'utf-8') $str = iconv('utf-8','gbk',$str);
  	$this->source_string = $str;
  	$this->result_string = '';
  }
  function simple_split($str) {
  	$this->source_string = $this->revise_string($str);
  	return $this->source_string;
  }
  function split_result($str='',$try_num_name=true,$try_diff=true) {
  	$str = trim($str);
  	if($str!='') $this->get_source($str);
  	else return '';
  	$this->source_string = preg_replace('/ {1,}/',' ',$this->revise_string($this->source_string));
  	$spwords = explode(' ',$this->source_string);
  	$spLen = count($spwords) - 1;
  	$spc = $this->split_char;
  	for($i=$spLen;$i>=0;$i--){
  		if(ord($spwords[$i][0])<33) continue;
  		else if(!isset($spwords[$i][$this->min_len])) $this->result_string = $spwords[$i].$spc.$this->result_string;
  		else if(ord($spwords[$i][0])<0x81){
  			$this->result_string = $spwords[$i].$spc.$this->result_string;
  		} else {
  		  $this->result_string = $this->split_mm($spwords[$i],$try_num_name,$try_diff).$spc.$this->result_string;
  	  }
  	}
  	if(CHARSET=='utf-8') $okstr = iconv('gbk','utf-8',$this->result_string);
  	else $okstr = $this->result_string;
  	return $okstr;
  }
  function par_number($str) {
  	if($str == '') return '';
  	$ws = explode(' ',$str);
  	$wlen = count($ws);
  	$spc = $this->split_char;
  	$reStr = '';
  	for($i=0;$i<$wlen;$i++){
  		if($ws[$i]=='') continue;
  		if($i>=$wlen-1) $reStr .= $spc.$ws[$i];
  		else{ $reStr .= $spc.$ws[$i]; }
    }
    return $reStr;
  }
  function par_other($word_array) {
  	$wlen = count($word_array)-1;
  	$rsStr = '';
  	$spc = $this->split_char;
  	for($i=$wlen;$i>=0;$i--) {
  		if(preg_match('/'.$this->cn_sg_num.'/',$word_array[$i])) {
  			$rsStr .= $spc.$word_array[$i];
  			if($i>0 && preg_match('/^'.$this->common_unit.'/',$word_array[$i-1]) ) {
				$rsStr .= $word_array[$i-1]; $i--;
			} else {
  				while($i>0 && preg_match("/".$this->cn_sg_num."/",$word_array[$i-1]) ){ $rsStr .= $word_array[$i-1]; $i--; }
  			}
  			continue;
  		}
  		if(strlen($word_array[$i])==4 && isset($this->two_name_dic[$word_array[$i]])) {
  			$rsStr .= $spc.$word_array[$i];
  			if($i>0&&strlen($word_array[$i-1])==2){
  				$rsStr .= $word_array[$i-1];$i--;
  				if($i>0&&strlen($word_array[$i-1])==2){ $rsStr .= $word_array[$i-1];$i--; }
  			}
  		} else if(strlen($word_array[$i])==2 && isset($this->one_name_dic[$word_array[$i]])) {
  			$rsStr .= $spc.$word_array[$i];
  			if($i>0&&strlen($word_array[$i-1])==2){
  				 if(preg_match("/".$this->especial_char."/",$word_array[$i-1])) continue;
  				 $rsStr .= $word_array[$i-1];$i--;
  				 if($i>0 && strlen($word_array[$i-1])==2 &&
  				  !preg_match("/".$this->especial_char."/",$word_array[$i-1]))
  				 { $rsStr .= $word_array[$i-1];$i--; }
  			}
  		} else {
  			$rsStr .= $spc.$word_array[$i];
  		}
  	}
  	$rsStr = preg_replace("/^".$spc."/","",$rsStr);
  	return $rsStr;
  }
  function split_mm($str,$try_num_name=true,$try_diff=true) {
  	$spc = $this->split_char;
  	$spLen = strlen($str);
  	$rsStr = $okWord = $tmpWord = '';
  	$word_array = array();
  	for($i=($spLen-1);$i>=0;) {
  		if($i<=$this->min_len){
  			if($i==1){
  			  $word_array[] = substr($str,0,2);
  		  } else {
  			   $w = substr($str,0,$this->min_len+1);
  			   if($this->is_word($w)){
  			   	$word_array[] = $w;
  			   }else{
  				   $word_array[] = substr($str,2,2);
  				   $word_array[] = substr($str,0,2);
  			   }
  		  }
  			$i = -1; break;
  		}
  		if($i>=$this->max_len) $max_pos = $this->max_len;
  		else $max_pos = $i;
  		$isMatch = false;
  		for($j=$max_pos;$j>=0;$j=$j-2){
  			 $w = substr($str,$i-$j,$j+1);
  			 if($this->is_word($w)){
  			 	$word_array[] = $w;
  			 	$i = $i-$j-1;
  			 	$isMatch = true;
  			 	break;
  			 }
  		}
  		if(!$isMatch){
  			if($i>1) {
  				$word_array[] = $str[$i-1].$str[$i];
  				$i = $i-2;
  			}
  		}
  	}//End For

  	if($try_num_name) {
		$rsStr = $this->par_other($word_array);
	} else {
  		$wlen = count($word_array)-1;
  		for($i=$wlen;$i>=0;$i--){
  	  	$rsStr .= $spc.$word_array[$i];
  	  }
  	}
  	if($try_diff) $rsStr = $this->test_diff(trim($rsStr));
  	return $rsStr;
  }
  function auto_description($str,$keyword,$strlen) {
  	$this->source_string = $this->revise_string($this->source_string);
  	$spwords = explode(" ",$this->source_string);
  	$keywords = explode(" ",$this->keywords);
  	$regstr = "";
  	foreach($keywords as $k=>$v) {
  		if($v=="") continue;
  		if(ord($v[0])>0x80 && strlen($v)<3) continue;
  		if($regstr=="") $regstr .= "($v)";
  		else $regstr .= "|($v)";
  	}
  }
  function test_diff($str) {
  	$str = preg_replace("/ {1,}/"," ",$str);
  	if($str == ""||$str == " ") return "";
  	$ws = explode(' ',$str);
  	$wlen = count($ws);
  	$spc = $this->split_char;
  	$reStr = "";
  	for($i=0;$i<$wlen;$i++) {
  		if($i>=($wlen-1)) {
  			$reStr .= $spc.$ws[$i];
  		} else {
  			if($ws[$i]==$ws[$i+1]){
  				$reStr .= $spc.$ws[$i].$ws[$i+1];
  				$i++; continue;
  			}
  			if(strlen($ws[$i])==2 && strlen($ws[$i+1])<8 && strlen($ws[$i+1])>2) {
  				$addw = $ws[$i].$ws[$i+1];
  				$t = 6;
  				$testok = false;
  				while($t>=4) {
  				  $w = substr($addw,0,$t);
  				  if($this->is_word($w) && ($this->get_rank($w) > $this->get_rank($ws[$i+1])*2) ) {
  					   $limit_word = substr($ws[$i+1],strlen($ws[$i+1])-$t-2,strlen($ws[$i+1])-strlen($w)+2);
  					   if($limit_word!="") $reStr .= $spc.$w.$spc.$limit_word;
  					   else $reStr .= $spc.$w;
  					   $testok = true;
  					   break;
  				  }
  				  $t = $t-2;
  			  }
  			  if(!$testok) $reStr .= $spc.$ws[$i];
  			  else $i++;
  			} else if(strlen($ws[$i])>2 && strlen($ws[$i])<8 && strlen($ws[$i+1])>2 && strlen($ws[$i+1])<8) {
  				$t21 = substr($ws[$i+1],0,2);
  				$t22 = substr($ws[$i+1],0,4);
  				if($this->is_word($ws[$i].$t21)) {
  					if(strlen($ws[$i])==6||strlen($ws[$i+1])==6){
  						$reStr .= $spc.$ws[$i].$t21.$spc.substr($ws[$i+1],2,strlen($ws[$i+1])-2);
  						$i++;
  					} else {
  						$reStr .= $spc.$ws[$i];
  					}
  				} else if(strlen($ws[$i+1])==6) {
  					if($this->is_word($ws[$i].$t22)) {
  						$reStr .= $spc.$ws[$i].$t22.$spc.$ws[$i+1][4].$ws[$i+1][5];
  						$i++;
  					} else { $reStr .= $spc.$ws[$i]; }
  				} else if(strlen($ws[$i+1])==4) {
  					$addw = $ws[$i].$ws[$i+1];
  					$t = strlen($ws[$i+1])-2;
  					$testok = false;
  					while($t>0) {
  						$w = substr($addw,0,strlen($ws[$i])+$t);
  						if($this->is_word($w) && ($this->get_rank($w) > $this->get_rank($ws[$i+1])*2) ) {
  				       $limit_word = substr($ws[$i+1],$t,strlen($ws[$i+1])-$t);
  					     if($limit_word!="") $reStr .= $spc.$w.$spc.$limit_word;
  					     else $reStr .= $spc.$w;
  					     $testok = true;
  					     break;
  				    }
  				    $t = $t-2;
  					}
  					if(!$testok) $reStr .= $spc.$ws[$i];
  			    else $i++;
  				}else {
  					$reStr .= $spc.$ws[$i];
  				}
  			} else {
  				$reStr .= $spc.$ws[$i];
  			}
  		}
    }//End For
  	return $reStr;
  }
  function is_word($okWord){
  	$slen = strlen($okWord);
  	if($slen > $this->max_len) return false;
  	else return isset($this->rank_dic[$slen][$okWord]);
  }
  function revise_string($str) {
  	$spc = $this->split_char;
    $slen = strlen($str);
    if($slen==0) return '';
    $okstr = '';
    $prechar = 0; // 0-¿Õ°× 1-Ó¢ÎÄ 2-ÖÐÎÄ 3-·ûºÅ
    for($i=0;$i<$slen;$i++){
      if(ord($str[$i]) < 0x81) {
        if(ord($str[$i]) < 33){
          //$str[$i]!="\r"&&$str[$i]!="\n"
          if($prechar!=0) $okstr .= $spc;
          $prechar=0;
          continue;
        } else if(preg_match("/[^0-9a-zA-Z@\.%#:\\/\\&_-]/",$str[$i])) {
          if($prechar==0) {
          	$okstr .= $str[$i]; $prechar=3;
          } else {
          	$okstr .= $spc.$str[$i]; $prechar=3;
          }
        } else {
        	if($prechar==2||$prechar==3) {
        		$okstr .= $spc.$str[$i]; $prechar=1;
        	} else {
        	  if(preg_match("/@#%:/",$str[$i])){ $okstr .= $str[$i]; $prechar=3; }
        	  else { $okstr .= $str[$i]; $prechar=1; }
        	}
        }
      } else{
        if($prechar!=0 && $prechar!=2) $okstr .= $spc;
        if(isset($str[$i+1])){
          $c = $str[$i].$str[$i+1];
          if(preg_match("/".$this->cn_number."/",$c)) {
          	$okstr .= $this->get_alab_num($c); $prechar = 2; $i++; continue;
          }
          $n = hexdec(bin2hex($c));
          if($n>0xA13F && $n < 0xAA40) {
            if($c=="¡¶"){
            	if($prechar!=0) $okstr .= $spc." ¡¶";
            	else $okstr .= " ¡¶";
            	$prechar = 2;
            } else if($c=="¡·"){
            	$okstr .= "¡· ";
            	$prechar = 3;
            } else{
            	if($prechar!=0) $okstr .= $spc.$c;
            	else $okstr .= $c;
            	$prechar = 3;
            }
          } else {
            $okstr .= $c;
            $prechar = 2;
          }
          $i++;
        }
      }//ÖÐÎÄ×Ö·û
    }//½áÊøÑ­»·
    return $okstr;
  }
  function find_new_word($str,$maxlen=6) {
    $okstr = "";
    return $str;
  }
  function get_keyword($str,$ilen=-1) {
    if($str=='') return '';
    else $this->split_result($str,true,true);
    $okstr = $this->result_string;
    $ws = explode(' ',$okstr);
    $okstr = $wks = '';
    foreach($ws as $w) {
      $w = trim($w);
      if(strlen($w)<2) continue;
      if(!preg_match("/[^0-9:-]/",$w)) continue;
      if(strlen($w)==2&&ord($w[0])>0x80) continue;
      if(isset($wks[$w])) $wks[$w]++;
      else $wks[$w] = 1;
    }
    if(is_array($wks)) {
      arsort($wks);
      if($ilen==-1) {
		foreach($wks as $w=>$v) {
      		if($this->get_rank($w)>500) $okstr .= $w." ";
        }
      }  else {
        foreach($wks as $w=>$v){
          if((strlen($okstr)+strlen($w)+1)<$ilen) $okstr .= $w." ";
          else break;
        }
      }
    }
    if(CHARSET=='utf-8') $okstr = iconv('gbk','utf-8',$okstr);
    return trim($okstr);
  }
  function get_rank($w){
  	if(isset($this->rank_dic[strlen($w)][$w])) return $this->rank_dic[strlen($w)][$w];
  	else return 0;
  }
  function get_alab_num($fnum){
	  $nums = array("£°","£±","£²","£³","£´","£µ","£¶",
	  "£·","£¸","£¹","£«","£­","£¥","£®",
	  "£á","£â","£ã","£ä","£å","£æ","£ç","£è","£é","£ê","£ë","£ì","£í",
	  "£î","£ï","£ð","£ñ","£ò","£ó ","£ô","£õ","£ö","£÷","£ø","£ù","£ú",
	  "£Á","£Â","£Ã","£Ä","£Å","£Æ","£Ç","£È","£É","£Ê","£Ë","£Ì","£Í",
	  "£Î","£Ï","£Ð","£Ñ","£Ò","£Ó","£Ô","£Õ","£Ö","£×","£Ø","£Ù","£Ú");
	  $fnums = "0123456789+-%.abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	  $fnum = str_replace($nums,$fnums,$fnum);
	  return $fnum;
  }
}
?>