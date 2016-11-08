<?php
/**
 * Created by PhpStorm.
 * User: UyenTTT
 * Date: 21/09/2015
 * Time: 9:22 AM
 */

namespace Utils;
use Session;

class commonUtils
{
    const ITEM_PER_PAGE_DEFAULT = 30;
    const MANAGER_COMPANY_NAME = '';

    const TIME_KEEP_COOKIE              = 4500;

    const INSERT_SUCCESSFULLY   = "Thêm mới thành công";
    const INSERT_UNSUCCESSFULLY = "Thêm mới không thành công";

    const IMPORT_SUCCESSFULLY   = "Import thành công";
    const IMPORT_UNSUCCESSFULLY = "Import thất bại! Vui lòng kiểm tra loại Import hoặc tập tin tài lên.";

    const EDIT_SUCCESSFULLY     = "Sửa thành công";
    const EDIT_UNSUCCESSFULLY   = "Sửa không thành công";

    const DELETE_SUCCESSFULLY   = "Xóa thành công";
    const DELETE_UNSUCCESSFULLY = "Xóa không thành công";
    const DELETE_ISSET_CHILD    = "Dữ liệu đang được sử dụng, không thể xóa.";

    const CANG_LON_CANG_TOT    = "Càng lớn càng tốt";
    const CANG_NHO_CANG_TOT    = "Càng nhỏ càng tốt";
    const DAT_KHONG_DAT        = "Đạt/Không đạt";
    const GOAL_TYPE_PARENT     = "Thuộc về mục tiêu cấp 1";

    const ACTION_INSERT     = 1;
    const ACTION_EDIT       = 2;
    const ACTION_DELETE     = 3;
    const ACTION_IMPORT     = 4;
    const ACTION_OVERRIDE   = 5;

    const NUMBER_AFTER_DOT  = 4;
    const DF_NUMBER_AFTER_DOT  = 1;

    const DEFAULT_PASSWORD = 123456;
    /**
     * Define list color for format excel
     */
    const COLOR_GOAL    = 'FFFF00';
    const COLOR_BROW    = 'CC9933';
    const COLOR_DARK    = 'DDDDDD';
    const COLOR_RED     = 'FF0000';
    const COLOR_GREEN   = '66FF33';

    /**
     * define const import
     */


    /**
     * Print array with pre tag
     * @param $arr
     */
    public static function pr($arr)
    {
        print_r("<pre>");
        print_r($arr);
        print_r("</pre>");
    }

    /**
     * Convert object to array (recursive)
     * @param $array
     * @return array
     */
    public static function objectToArray($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = self::objectToArray($value);
                }
                if (is_object($value)) {
                    $array[$key] = self::objectToArray((array)$value);
                }
            }
        }
        if (is_object($array)) {
            return self::objectToArray((array)$array);
        }
        return $array;
    }

    /**
     * Convert array to json
     * @param $args
     * @return string
     */
    public static function arrayToJson($args) {
        if (!is_array($args)) {
            if ($args) {
                $args = (array)$args;
            } else {
                $args = array();
            }
        }

        return json_encode($args);
    }

    /**
     * Convert json to array
     * @param $args
     * @return string
     */
    public static function jsonToArray($dataJson, $assoc = false) {
        return (array)json_decode($dataJson, $assoc);
    }

    /**
     * Format date to view dd/mm/YYYY
     */
    public static function formatDate($value)
    {
        $temp = substr($value, 8, 2) . '/' . substr($value, 5, 2) . '/' . substr($value, 0, 4);
        return $temp;
    }

    /**
     * Format date to view yyyy/mm/dd
     */
    public static function formatDateYMD($value)
    {
        $temp = substr($value, 6, 4) . '/' . substr($value, 3, 2) . '/' . substr($value, 0, 2);
        return $temp;
    }

    public static function formatDateYMDT($value)
    {
        $temp = substr($value, 6, 4) . '-' . substr($value, 3, 2) . '-' . substr($value, 0, 2);
        return $temp;
    }

    public static function buildTreeProductType(array $elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTreeProductType($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                    $element['state'] = array('opened' => true);
                }

                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * @param $arr
     * @param $key
     * @return int
     */
    public static function issetArray($arr, $key)
    {
        foreach ($arr as $val) {
            if ($val['parent_id'] == $key) {
                return 1;
            }
        }
        return -1;
    }

    /**
     * @param $key
     * @param $arr
     * @return int
     */
    public static function checkIssetKeyInArray($key, $arr)
    {
        foreach ($arr as $goal) {
            if ($goal['id'] == $key) {
                return 1;
            }
        }
        return 0;
    }



    /**
     * Create data for chart
     * @param $arrData
     * @param $arrMonth
     * @return string
     */
    public static function createArrayForChart($arrData, $arrMonth) {
        $lineChartData = '{
                            labels: [';
        for($i = 0;$i < count($arrMonth);$i++){
            $lineChartData .= '"Tháng '.$arrMonth[$i].'",';
        }
        $lineChartData.='],datasets: [' ;
        for($i = 0;$i < count($arrData);$i++){
            //commonUtils::pr($arrData[$i]['label']);die;
            $lineChartData .= '{
                        label:"'.$arrData[$i]['label'].'",
                        fillColor:"'.$arrData[$i]['fillColor'].'",
                        strokeColor:"'.$arrData[$i]['strokeColor'].'",
                        pointHighlightFill:"'.$arrData[$i]['pointHighlightFill'].'",
                        pointHighlightStroke:"'.$arrData[$i]['pointHighlightStroke'].'",
                        pointColor:"'.$arrData[$i]['pointColor'].'",
                        pointStrokeColor:"'.$arrData[$i]['pointStrokeColor'].'",
                        data:'.$arrData[$i]['data'].',
            },';
        }
        $lineChartData.=']}';
        return $lineChartData;
    }

    /**
     * Number format
     * @param null $number
     * @return string
     */
    public static function numberFormat($number = null) {
        if($number) {
            $money = $number;
        }

        return number_format($money);
    }

    /**
     * @param $floatValue
     * @return string
     * Format a value with type is float to show with dot between
     */
    public static function formatFloatValue($floatValue, $NUMBER){
        return number_format($floatValue
            , $NUMBER /*Số chữ số sau dấu thập phân*/
            , '.' /*Ký tự phân cách phần thập phân và phần nguyên*/
            , ',' /*Ký tự phân cách phần nghìn*/
        );
    }

    public static function defaultMonth() {
        $arrDefaultMonth = array();
        for($i = 1; $i <= 12; $i++ ){
            $arrDefaultMonth[] = $i;
        }

        return $arrDefaultMonth;
    }

    public static function getArrYear($objectDB) {
        $arrYear = array();
        foreach($objectDB as $object){
            if(!in_array($object->year, $arrYear)){
                $arrYear[] = $object->year;
            }
        }

        return $arrYear;
    }


    public static function checkMonthValid($value){
        return (isset($value) && $value != 0 && $value!= null && $value > 0 && $value <= 12 && is_int((int)($value))) ? (int)trim($value) : '';
    }

    public static function checkDataValid($value){
        return (isset($value) && $value!= null) ? $value : '';
    }

    public static function checkYearValid($value){
        return (isset($value) && $value >= 2015 && $value!= null && is_int((int)($value))) ? (int)($value) : '';
    }

    public static function getArraySheets($value){

        $listSheetIndex = explode(',', $value);
        $arrSheets = array();
        foreach($listSheetIndex as $sheetIndex){
            $sheetIndex = trim($sheetIndex);
            if(
                !in_array($sheetIndex, $arrSheets)
                && $sheetIndex != ""
                && is_numeric($sheetIndex)
                && $sheetIndex > 0
            ){
                $arrSheets[] = (int)$sheetIndex - 1;
            }
        }

        return $arrSheets;
    }

    public static function compareTwoString($value, $valueCompare){
        return (strtolower(trim($value)) == strtolower(trim($valueCompare))) ? 1 : 0;
    }

    public static function checkValueNumeric($value){
        return (isset($value) && $value != null && is_numeric($value)) ? $value : 0;
    }


    public static function renderActionName($value){
        $result = '';
        switch($value){
            case 0:
                $result = 'Tất cả';
                break;
            case self::ACTION_IMPORT:
                $result = 'Import';
                break;
            case self::ACTION_INSERT:
                $result = 'Thêm mới';
                break;
            case self::ACTION_EDIT:
                $result = 'Cập nhật';
                break;
            case self::ACTION_DELETE:
                $result = 'Xóa';
                break;
            case self::ACTION_OVERRIDE:
                $result = 'Ghi đè tập tin';
                break;
        }
        return $result;
    }

    public static function arrAction(){

        $arrKey = array(
              0 => array('id' => 0, 'name' => 'Tất cả')
            , 1 => array('id' => self::ACTION_IMPORT, 'name' => 'Import')
            , 2 => array('id' => self::ACTION_INSERT, 'name' => 'Thêm mới')
            , 3 => array('id' => self::ACTION_EDIT, 'name' => 'Cập nhật')
            , 4 => array('id' => self::ACTION_DELETE,'name' => 'Xóa')
            , 5 => array('id' => self::ACTION_OVERRIDE, 'name' => 'Ghi đè tập tin')
        );
        return $arrKey;

    }

    public static function keywordDenied(){
        return $arrKey = array("'", '"', "`");
    }

    public static function findMin($array, $key) {

        $min = $array[0][$key];
        foreach($array as $a){
            if($a[$key] < $min){
                $min = $a[$key];
            }
        }

        return $min;

    }

}

