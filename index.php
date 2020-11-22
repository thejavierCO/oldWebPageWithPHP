<?php
include_once("explorer.php");
function is_json($var){
    return is_string($var) && is_array(json_decode($var, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
function is_nothing($var){
    return !is_string($var)&&is_array($var)?count(array_keys($var))>1?true:false:true;
}
function response($values){
    $r = array();
    $res = is_array($values)?$values:array($values);
    if(array_key_exists("type",$res)){
        switch($res["type"]){
            case "file":
            $r["file"] = array();
            break;
            case "error":
            $r["error"] = array_key_exists(0,$res)?$res[0]:$res;
            break;
            default:
            $r["result"] = $res;
        }
    }else{
        $r["result"] = $res;
    }
    return $r;
}
function get_parameter(){
    $r = array();
    $p = $_GET;
    $q = file_get_contents("php://input");
    if(is_json($q)||!is_nothing($p)){
        $json_q = json_decode($q,true);
        if(!is_nothing($json_q)){
            $r[] = $json_q; 
        }
        if(!is_nothing($p)){
            $r[] = $p;
        }
        return $r;
    }else{
        return array("not_exist_values","type"=>"error");
    }
}
function get_methodo(){
    if(array_key_exists("REQUEST_METHOD",$_SERVER)){
        return array($_SERVER["REQUEST_METHOD"]);
    }else{
        return array("not_exist_methodo","type"=>"error");
    }
}
function get_values($var){
    if(is_string($var)){
        $r = explode(",",$var);
        $res = is_array($r)?array($r,count($r)):$r;
        return $res;
    }
}
function exist($keys,$value,$error_msg=false){
    $r = get_values($keys);
    $res = array();
    if($error_msg==false){
        if(is_array($value)){
            foreach($r[0] as $v => $n){
                $res[$n]["exist"] = array_key_exists($n,$value);
            }
            return $res;
        }else{
            return $res;
        }
    }else{
        if(is_array($value)){
            foreach($r[0] as $v => $n){
                if(array_key_exists($n,$value)){
                    $res[] = "exist_".$n;
                }else{
                    $res[] = "not_exist_".$n;
                }
            }
            return $res;
        }else{
            return $res;
        }
    }
}
class Get {
}
class fun {
    private function explorer($ruta="./",$filter="",$description=""){
        $file = new exp;
        return $file->openDir($ruta,$filter);
    }
    public function param($values){
        $func = new fun;
        $res = array();
        foreach(array_keys($values) as $v => $r){
            if($v=="file"){
                $res[] = exist("filter",$values["file"])["filter"]["exist"]==true?$values["file"]["filter"]:"";
                $res[] = exist("raiz",$values)["raiz"]["exist"]==true?$values["raiz"]:"";
            }
        }
        return $func->explorer($res[1],$res[0]);
    }
}
class Api {
    public function add($methodo,$parameter,$result){
        if(is_json($parameter)){// si es json
            $res = json_decode($parameter,true);
            if($methodo==get_methodo()[0]){ // si metodo es igual metodo a usar
                if(exist("get",get_parameter()[0])["get"]["exist"]==true){
                    $r = true;
                    foreach(exist("type,id",$res)as$v=>$f){
                        if($f["exist"]==false){
                            $r = false;
                        }
                    }
                    if($r==true){
                        if($res["type"]==get_parameter()[0]["get"]){
                            $func = new fun;
                            $data = array();
                            foreach($res["id"] as $v => $f){
                                $data[$v] = $f;
                            }
                            return $func->param($data);
                        }else{
                            return array("error_parameter_get","type"=>"error");
                        }
                    }else{
                        return array(exist("type,id",$res,true),"type"=>"error");
                    }
                }else{
                    return array(exist("get",get_parameter()[0],true),"type"=>"error");
                }
            }else
            return array("error_methodo","type"=>"error");
        }else
        return array("error_not_json_parameter","type"=>"error");
    }
}
//---------------------------(test)
$app = new Api;
$result = array();
$result[] = response($app->add("GET",'{"type":"page","id":{"file":{"filter":"html"},"raiz":"./"}}',array(
    "name"=>""
)));
$result[] = response($app->add("GET",'{"type":"page","id":{"file":[],"raiz":"./"}}',array(
    "name"=>""
)));
echo json_encode($result);
?>