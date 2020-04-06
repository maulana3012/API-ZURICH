<?php
function convertDate($stringtgl){
        $tgl=trim($stringtgl);
        if(strlen($tgl)==8){
            $tahun = substr($tgl, 0,4);
            $bulan = substr($tgl, 4,2);
            $tangg = substr($tgl, 6,2);
            $arraybln=array(" ","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
            $strbln=$arraybln[intval($bulan)];
            return "$tangg $strbln $tahun";    
        }else{
            return 'invalid date!';
        }
    }

    function convertNominal($value)
    {
        return number_format((int)$value, 0, ',', '.');
    }

    function setDash($string){
        if($string=='')
            return "-";
        else
            return $string;
        
    }

    function converrtCurr($curr){
        if($curr=='IDR'){
            return "Rp ";
        }
    }

    function convertfreq($freq){
        if($freq=='Quarterly'){
            return "K";
        }elseif ($freq=="Monthly") {
            return "B";
        }else {
            return "T";
        }
    }

    function convertmetode($metode){
            if($metode=='D'){
                return "Auto Debit Rekening Bank";
            }elseif ($metode=="C") {
                return "Auto Debit Kartu Kredit";
            }else {
                return "Transfer Bank";
            }
    }

    function convertcode($code){
            if($code=='Zurich Proteksi 8'){
                return "ZP8";
            }elseif ($code=="") {
                return "null";
            }else {
                return "null";
            }
    }

    function parsing($string,$max_char_per_line,$row){
        $splited_string=explode(' ', $string);
        $tempresult='';
        $baris=1;
        $result = array(
            1   => "",
            2   => "",
        );
        foreach ($splited_string as $val) {
            if(strlen($tempresult." ".$val)<=$max_char_per_line){
                $tempresult = $tempresult." ".$val;
                $result[$baris] = $tempresult;
            }else{
                $baris++;
                $tempresult=$val;
                $result[$baris]=$tempresult;
            }
        }
        return $result[$row];   
    }
?>