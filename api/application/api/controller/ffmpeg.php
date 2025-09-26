<?php
/**
 * video class
 * written: denny  Date :2020-06-15
 */
class Mpeg{
    public $sowarePath  = "";
    public $ffmpegCmd  = "ffmpeg ";
    public $ffprobeCmd = "ffprobe ";
    function __construct($sowarePath=''){
        if (!function_exists('exec')){
            echo "exec function not exist!";return;
        }

        if($sowarePath){
            $this->sowarePath = $sowarePath;
        }
    }

    /**
     * 视频加文字
     * $position = win10不支持中文字体,请添加英文或数组字体,标点符号也不能有
     * $xOffset x坐标的偏移量，$yOffset x坐标的偏移量
     * ffmpeg  -i source.mp4 -vf drawtext=fontcolor=white:fontsize=60:fontfile=arial.ttf:text="hei guy":x=main_w/2-30:y=main_h/2-30:enable='between(t\,2\,6)' out.mp4
     */
    function FontVideo($sourceFile='',$outPutFile='',$string="",$fontFile="",$fontSize=60,$fontColor="red",$xOffset=30,$yOffset=30,$startSecond=0,$endSecond=10){
        if (!file_exists($sourceFile)||!is_file($fontFile)){
            return false;//"file not exist!";
        }
        if(!$endSecond){
            return false;
        }

        $fontFile = $fontFile?$fontFile:$this->sowarePath.'simyou.ttf';
        $Symbol = array(
            '"','“',',',"，",'!','！','@','#','$','%','^','&','*','(','（',')','）','+','-','—','>','》',
            '<','《','.','。','?','？','/','、','`','·','-',
        );
        $string = str_replace($Symbol," ",$string);
        $outPut   = $returnVar = "";
        $position = "x=$xOffset:y=$yOffset";

        exec($this->sowarePath . "ffmpeg -i $sourceFile -vf drawtext=fontcolor=$fontColor:fontsize=$fontSize:fontfile=$fontFile:text=\"$string\":$position:enable='between(t\,{$startSecond}\,{$endSecond})' $outPutFile",$outPut,$returnVar);

        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }
    /**
     * 视频加水印 $file = ‪D:/source.mp4,$outPutFile=d:/compression.mp4
     * $position = (leftTop,rightTop,leftBottom,rightBottom,middle);左上角,右上角,左下角,右下角
     * $xOffset x坐标的偏移量，$yOffset x坐标的偏移量
     * ffmpeg -i E:\ffmpeg\bin\source_cut_zip.mp4 -i E:\ffmpeg\bin\logo.png -filter_complex "overlay=30:30" E:\ffmpeg\bin\source_cut_zip_logo.mp4
     */
    function WaterVideo($sourceFile='',$outPutFile='',$logoFile="",$position="leftTop",$xOffset=30,$yOffset=30){
        if (!file_exists($sourceFile)||!is_file($logoFile)){
            return false;//"file not exist!";
        }
        $outPut   = $returnVar = "";
        switch($position){
            case 'leftTop':
                $position = "$xOffset:$yOffset";break;
            case 'rightTop':
                $position = "main_w-overlay_w-$xOffset:$yOffset";break;
            case 'leftBottom':
                $position = "$xOffset:main_h-overlay_h-$yOffset";break;
            case 'rightBottom':
                $position = "main_w-overlay_w-$xOffset:main_h-overlay_h-$yOffset";break;
            case 'middle':
                $position = "overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2";break;
            default:
                $position = "$xOffset:$yOffset";
        }

        $H264 = PHP_OS!='WINNT'?" -vcodec libx264 ":' ';

        exec($this->sowarePath . "ffmpeg -i $sourceFile -i $logoFile -filter_complex \"overlay=$position\" $H264 $outPutFile",$outPut,$returnVar);

        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }
    /**
     * 视频裁剪 $file = ‪D:/source.mp4,$outPutFile=d:/compression.mp4
     * ffmpeg  -ss 00:00:00 -t 00:01:05 -i E:\ffmpeg\bin\source.mp4 -vcodec copy -acodec copy E:\ffmpeg\bin\source_cut.mp4
     */
    function CutVideo($sourceFile='',$outPutFile='',$startCut='00:00:00',$cutLen='00:01:00'){
        if (!file_exists($sourceFile)){
            return false;//"file not exist!";
        }
        $outPut   = $returnVar = "";
        $start    = $startCut?trim($startCut):$startCut;
        $videoLen = $cutLen?trim($cutLen):$cutLen;

        exec($this->sowarePath . "ffmpeg -ss $start -t $videoLen -i $sourceFile -vcodec copy -acodec copy $outPutFile",$outPut,$returnVar);

        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }
    /**
     * 视频压缩 $file = ‪D:\source.mp4,$outPutFile=d:/compression.mp4,$rate码率，网页一般800-1200就ok，$maxRate默认$rate+200
     */
    function VideoCompression($sourceFile='',$outPutFile='',$width=1280,$height=720,$rate=800,$maxRate=1000){
        if (!file_exists($sourceFile)){
            return false;//"file not exist!";
        }
        $outPut=$returnVar = "";
        $width   = intval($width);
        $rate    = intval($rate);
        $maxRate = intval($maxRate)>0?$maxRate:$rate+200;

        $H264 = PHP_OS!='WINNT'?" -vcodec libx264 ":' ';
        exec($this->sowarePath . "ffmpeg -i $sourceFile -vf scale=$width:$height -b:v {$rate}k -bufsize {$rate}k -maxrate {$maxRate}k $H264 $outPutFile",$outPut,$returnVar);

        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }
    /**
     * 视频格式转换 $file = ‪D:\jd.mp4,$outPutFile=d:/conversion.avi‪D:\m3c.sql
     */
    function formatConversion($file='',$outPutFile=''){
        if (!file_exists($file)) {
            return false;//"file not exist!";
        }
        $outPut=$returnVar = "";
        exec($this->sowarePath . "ffmpeg -i $file $outPutFile",$outPut,$returnVar);
        if($returnVar==1){
            return false;//命令执行失败
        }else{
            return $outPutFile;//命令执行成功,返回路径
        }
    }
    /**
     * 视频提取音频 ‪D:\jd.mp4
     */
    function ExtractSound($file='',$outPutMp3=''){
        if (!is_file($file)) {
            return false;//"file not exist!";
        }

        passthru($this->sowarePath . "ffprobe -v quiet -show_format -show_streams -print_format json " . $file);
        $video_info = ob_get_contents();
        ob_end_clean();

        $result = json_decode($video_info, true);
    }
    /**
     * ffmpeg获取视频信息 written:Denny Yang
     * */
    function VideoInfo($file=''){
        if(!is_file($file)){
            return false;//"file not exist!";
        }

        ob_start();
        passthru($this->sowarePath."ffprobe -v quiet -show_format -show_streams -print_format json ".$file);
        $video_info = ob_get_contents();
        ob_end_clean();

        $result = json_decode($video_info,true);

        $ret['width'] = isset($result['streams'][0]['width'])?$result['streams'][0]['width']:0;//宽度
        $ret['height'] = isset($result['streams'][0]['height'])?$result['streams'][0]['height']:0;//高度
        $ret['sample_aspect_ratio'] = isset($result['streams'][0]['sample_aspect_ratio'])?$result['streams'][0]['sample_aspect_ratio']:0;//比例
        $ret['display_aspect_ratio']= isset($result['streams'][0]['display_aspect_ratio'])?$result['streams'][0]['display_aspect_ratio']:0;
        $ret['r_frame_rate']        = isset($result['streams'][0]['r_frame_rate'])?$result['streams'][0]['r_frame_rate']:0;//帧数
        $ret['avg_frame_rate']      = isset($result['streams'][0]['avg_frame_rate'])?$result['streams'][0]['avg_frame_rate']:0;//平均帧数
        $ret['video_duration']      = isset($result['streams'][0]['duration'])?$result['streams'][0]['duration']:0;//视频时长
        $ret['video_bit_rate']      = isset($result['streams'][0]['bit_rate'])?$result['streams'][0]['bit_rate']:0;//视频码率
        $ret['audio_bit_rate']      = isset($result['streams'][1]['bit_rate'])?$result['streams'][1]['bit_rate']:0;//声音码率,jd商品5-600kbps
        $ret['filename']            = isset($result['format']['filename'])?$result['format']['filename']:0;//文件名称
        $ret['nb_streams']          = isset($result['format']['nb_streams'])?$result['format']['nb_streams']:0;
        $ret['format_name']         = isset($result['format']['format_name'])?$result['format']['format_name']:0;//格式
        $ret['start_time']          = isset($result['format']['start_time'])?$result['format']['start_time']:0;//开始时间
        $ret['duration']            = isset($result['format']['duration'])?$result['format']['duration']:0;//时长
        $ret['size']                = isset($result['format']['size'])?$result['format']['size']:0;//文件大小k

        return $ret;
    }
}

?>
