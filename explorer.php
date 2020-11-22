<?php
class exp{
    public $result = array();
    function openDir($raiz,$exc=""){
        if (is_dir($raiz)){
            if ($dh = opendir($raiz)){
                return exp::loop($dh,$raiz,$exc);
                closedir($dh);
            }
        }
    }
    private function loop($dh,$raiz,$Filter){
        while (($file = readdir($dh)) !== false){
            if(exp::filter("dir",$file,array(".","..",".git"))!=="deleted"){
                if(is_dir($file)){
                    //$this->result[] = array($file,count(glob($file."*")));
                    $this->result[$raiz][] = array("dir"=>$file,"count"=>count(glob($file."*")));
                    exp::openDir($raiz.$file."/");
                }else{
                    if($Filter != ""){
                        if(exp::filter("file",$file,$Filter)){
                            $this->result[$raiz][] = array("name"=>exp::filter("name",$file,$Filter),"raiz"=>$raiz,"extencion"=>$Filter);
                        }
                    }else{
                        $this->result[$raiz][] = array("file"=>$file,"raiz"=>$raiz);
                    }
                }
            }
        }
        return $this->result;
    }
    private function filter($T,$N,$Vfilter){
        switch($T){
            case "file":
            $ex = explode(".",$N);
            if($Vfilter != ""){
                if($ex[count($ex)-1]==$Vfilter){
                    return true;
                }
                return false;
            }else{
                return true;
            }
            break;
            case "dir":
            if(is_array($Vfilter)){
                foreach($Vfilter as $key){
                    if($N == $key){
                        return "deleted";
                    }
                }
            }
            break;
            case "name":
            $name = "";
            $ex = explode(".".$Vfilter,$N);
            $last = $ex[count($ex)-1];
            for ($i=0; $i < count($ex)-1; $i++) {
                $name .= $ex[$i];
            }
            return $name;
            break;
        }
    }
}
?>