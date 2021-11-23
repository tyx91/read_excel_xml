<?php
/**
 * Created by PhpStorm.
 *
 * @author 田玉星<tianyuxing@sciall.org.cn>
 * @email tianyuxing@sciall.org.cn
 * User: Yasin
 * Date: 2020/9/17
 * Time: 10:42
 */
namespace Src;
/**
 *  */
class Read
{
    protected static $find = [" ", "　", "\n", "\r", "\t", "\"", "\'", "#N/A",' ',' '];
    protected static $replace = ["", "", "", "", "","","","",'',''];

    /**
     * 读取Excel格式的xml文件
     * 注意：需使用Microsoft Office软件将excel文件另存为xml格式
     * @Author : Yasin
     *
     * @param string $sourceFile xml文件路径
     *
     * @return \Generator
     */
    public static function excelXml(string $sourceFile):\Generator
    {
        $reader = new \XMLReader();
        $reader->open($sourceFile);
        //读取文件
        while($reader->read()) {
            $rowData = [];
            if ( $reader->name != 'Row' || $reader->nodeType!=1) {
                continue;
            }
            $outerXml = $reader->readOuterXML(); // 获取当前整个 object 内容（字符串）
            //过滤掉非法字符
            $outerXml = str_replace(['&#'], [''], $outerXml);
            $xmlObject = simplexml_load_string($outerXml); // 转换成 SimpleXMLElement 对象
            //计算元素总个数
            $elementCount =  count($xmlObject->Cell);
            $ns = $xmlObject->getDocNamespaces();
            $index = 1;
            for($i=0;$i<$elementCount;$i++){
                //如果xml数据的节点有index属性，则将属性设置为字段的key
                $attrIndex = (int) $xmlObject->Cell[$i]->attributes($ns['ss'])->Index;
                if($attrIndex){
                    $index = $attrIndex;
                }
                $content= (string)$xmlObject->Cell[$i]->Data;
                $rowData[ $index ]['data'] = str_replace(self::$find, self::$replace, $content);
                $index++;
            }
            yield $rowData;
        }
        $reader->close();
    }

    /**
     * 数据转换
     *
     * @Author : Yasin
     *
     * @param array $getField    在excel中需要提取的字段
     * @param array $sourceData  excelXml方法返回的结果
     * @param array $appendData  每行数据补充的字段
     * @param bool  $forceAssign 数据列不存在时 是否需要强制赋值
     *
     * @return array
     */
    public static function  transData(array $getField, array $sourceData, array $appendData=[], bool $forceAssign=false):array
    {
        if(empty($getField)){
            return [];
        }
        if(empty($sourceData)){
            exit("源数据不能为空".PHP_EOL);
        }
        //xml设置转换
        $xmlData = [];
        foreach ($getField as $column=>$field){
            //取出数据列中的值
            if($forceAssign ==false && !isset($sourceData[$column]['data'])){
                exit("数据源文件第{$column}列不存在".PHP_EOL);
            }
            $xmlData[$field] = $sourceData[$column]['data']?? '';
        }
        //数据补充,补充源数据不存在或者为空的值
        if($appendData){
            foreach ($appendData as $key=>$val){
                if(empty($xmlData[$key])){
                    $xmlData[$key] = $val;
                }
            }
        }
        return $xmlData;
    }

    /**
     * 记录日志
     *
     * @Author : Yasin
     *
     * @param string $content     日志内容
     * @param string $userLogFile 自定义日志文件
     * @param bool   $isClean     是否清空日志文件内容
     */
    protected function writeLog(string $content, bool $isClean = false, string $userLogFile = '')
    {
        $logFile = $userLogFile? $userLogFile: $this->logFile;
        $logDir = dirname($logFile);
        //如日志文件夹不存在则创建
        if (!is_dir($logDir) &&  !mkdir($logDir, 0777, true)) {
            die('日志文件夹创建失败:' . $logDir . PHP_EOL);
        }
        $context = ($isClean? null: FILE_APPEND);
        file_put_contents($logFile, $content . PHP_EOL, $context);
    }

    /**
     * 输出日志到显示屏
     *
     * @Author : Yasin
     *
     * @param string $content
     * @param int    $count 换行符个数
     */
    public static function echoLog(string $content, int $count = 1)
    {
        echo $content . str_repeat(PHP_EOL, $count);
    }

    //返回未编码的json
    public static  function arrayToJson(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);;
    }

}