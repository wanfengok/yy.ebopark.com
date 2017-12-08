<?php
/**
 * Created by PhpStorm.
 * User: pengjixiang
 * Date: 17/5/12
 * Time: 上午10:31
 */

namespace Admin\Service;


use Think\Exception;

class MapService
{
    public function getMapJsonData()
    {
        // 从文件中读取数据到PHP变量
        $json_string = file_get_contents('./map.json');
        // 把JSON字符串转成PHP数组
        if (empty($json_string)) {
            return null;
        }
        $data = json_decode($json_string, true);
        return $data;
    }

    public function updateMapJson($info)
    {
        $fullname = $info["apkfile"]["name"];
        $name = substr($info["apkfile"]["name"], 0, count($fullname) - 5);
        $fileObj = [$name => $info["apkfile"]];
        $mapJson = $this->getMapJsonData();
        try {
            if ($mapJson == null) {
                $content = json_encode($fileObj);
                file_put_contents('./map.json', $content);
                return;
            }
            $mapJson[$name] = $info["apkfile"];
            $content = json_encode($mapJson);
            file_put_contents('./map.json', $content);
        } catch (Exception $e) {

        }
    }

    public function decodePackage($filepath,$info)
    {
        $fullname = $info["apkfile"]["name"];
        $name = substr($info["apkfile"]["name"], 0, count($fullname) - 5);
        if(!file_exists($filepath)){
            return false;
        }
        try{
            $content=str_split(bin2hex(file_get_contents($filepath)),2);

            $floors = $this->getFloors($content);
            $mapJson = $this->getMapJsonData();
            $mapJson[$name]["data"]=json_encode($floors);
            file_put_contents('./map.json', json_encode($mapJson));
            return true;
        }catch(Exception $e){
            return false;
        }

    }
    public function getParkInfoByParkcode($parkcode){
        $mapJson = $this->getMapJsonData();
        return json_decode($mapJson[$parkcode]["data"]);
    }
    public function getAllParkinfo(){
        $mapJson = $this->getMapJsonData();
        return $mapJson;
    }
    private function getLength($buffer)
    {

        return (hexdec($buffer[0] >= 0) ? hexdec($buffer[0]) : hexdec($buffer[0]) + 256) * 256 * 256 * 256
        + (hexdec($buffer[1]) >= 0 ? hexdec($buffer[1]) : hexdec($buffer[1]) + 256) * 256 * 256
        + (hexdec($buffer[2]) >= 0 ? hexdec($buffer[2]) : hexdec($buffer[2]) + 256) * 256
        + (hexdec($buffer[3]) >= 0 ? hexdec($buffer[3]) : hexdec($buffer[3]) + 256);
    }

    private function getLengthWithOffet($buffer, $offset)
    {
        return (hexdec($buffer[$offset]) >= 0 ? hexdec($buffer[$offset]) : hexdec($buffer[$offset]) + 256) * 256 * 256 * 256
        + hexdec(($buffer[$offset + 1]) >= 0 ? hexdec($buffer[$offset + 1]) : hexdec($buffer[$offset + 1]) + 256) * 256 * 256
        + (hexdec($buffer[$offset + 2])>= 0 ? hexdec($buffer[$offset + 2]) : hexdec($buffer[$offset + 2]) + 256) * 256
        + (hexdec($buffer[$offset + 3]) >= 0 ? hexdec($buffer[$offset + 3]) : hexdec($buffer[$offset + 3]) + 256);
    }

    private function getFloors($buffer)
    {

        $floors = [];
        $count = $this->getLength($buffer);
        $lengths = [];
        $offset = 4;
        for ($i = 0; $i < $count; $i++) {
            $lengths[] = $this->getLengthWithOffet($buffer, $offset);
            $offset += 4;
        }
        foreach ($lengths as $val) {
            $mBuffer = [];
            for ($index = 0; $index < $val; $index++) {
                $mBuffer[$index] = $buffer[$offset + $index];
            }
            $offset += $val;
            $floor = $this->getFloorFromBuffer($mBuffer);
            $floors[] = $floor;

        }
        return $floors;
    }

    private function getFloorFromBuffer($mbuffer = [])
    {

        $floorNameLength = $this->getLengthWithOffet($mbuffer, 0);
        $mapLength = $this->getLengthWithOffet($mbuffer, 4);
        $pointLength = $this->getLengthWithOffet($mbuffer, 8);
        $lineLength = $this->getLengthWithOffet($mbuffer, 12);

        $floorBuffer = [];
        $map[] = [];
        $points[] = [];
        $lines[] = [];
        $offset = 16;
        for ($index = 0; $index < $floorNameLength; $index++) {
            $floorBuffer[$index] = $mbuffer[$offset + $index];
        }
        $offset += $floorNameLength;
        for ($index = 0; $index < $mapLength; $index++) {
            $map[$index] = $mbuffer[$offset + $index];
        }
        $offset += $mapLength;
        for ($index = 0; $index < $pointLength; $index++) {
            $points[$index] = $mbuffer[$offset + $index];
        }
        $offset += $pointLength;
        for ($index = 0; $index < $lineLength; $index++) {
            $lines[$index] = $mbuffer[$offset + $index];
        }
        $pointList=[];
        $lineList=[];
        for ($i = 0; $i < $pointLength; $i += 80) {
            $point["pointX"] = $this->getLengthWithOffet($points, $i);
            $point["pointY"] =  $this->getLengthWithOffet($points, $i + 4);
            $point["id"]=$this->getLengthWithOffet($points, $i + 8);
            $point["name"]=MapService::toStr($points,$i+12,20);
            $point["description"]=MapService::toStr($points,$i+32,48);
            $pointList[]=$point;
        }
        for ( $i = 0; $i < $lineLength; $i += 12) {
            $line["startpoint"]=$this->getLengthWithOffet($lines, $i);
            $line["stoppoint"]=$this->getLengthWithOffet($lines, $i+4);
            $line["weight"]=$this->getLengthWithOffet($lines, $i+8);
            $lineList[]=$line;
        }
        $floorname=MapService::arrtoStr($floorBuffer);
        $floor["floorname"]=$floorname;
        $floor["linelist"]=$lineList;
        $floor["pointlist"]=$pointList;
        $floor["map"]="data:image/bmp;base64,".base64_encode(MapService::arrtoStr($map));;
        return $floor;
    }
    public static function toStr($bytes,$offset,$length) {
        $tempArr=[];
        for($index=$offset;$index<$length+$offset;$index++){
            $tempArr[$index]=$bytes[$index];
        }
        $hexstr=implode("",$tempArr);
        return MapService::hexToStr($hexstr);;
    }
    public static function arrtoStr($arr=[]){
        $hexstr=implode("",$arr);
        return MapService::hexToStr($hexstr);
    }
    public static function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return trim($string);
    }

}