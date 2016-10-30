<?php
/**
 * Created by PhpStorm.
 * User: chaunp
 * Date: 2/1/2016
 * Time: 3:49 PM
 */
namespace Convenient;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Cell;

class excelUtils
{

    /* *****************************************************************************************************************
     * Define const for color
     * ****************************************************************************************************************/

    const COLOR_BROW    = 'CC9933';
    const COLOR_DARK    = 'DDDDDD';
    const COLOR_RED     = 'FF0000';
    const COLOR_GREEN   = '66FF33';
    const COLOR_YELLOWG = '9ACD32';
    /* *****************************************************************************************************************
     * Define const for font
     * ****************************************************************************************************************/

    const FONT_CALIBRI = "Calibri";
    /* *****************************************************************************************************************
     * Define Style format
     * ****************************************************************************************************************/
    const PATH_BLANK             = "public/exportTemplate/blank.xlsx";
    const STYLE_NUMBER           = '###,###,###,##0.000';
    const ALERT_MONTH_YEAR       = "Không thay đổi tiêu đề file, vị trí dữ liệu Thời gian áp dụng. ";
    const DEFAULT_ZOOM           = 80;
    const TITLE_BACKUP_GOAL_AREA = "MẪU IN LƯU KẾ HOẠCH";
    const TITLE_BACKUP_ILPO_AREA = "MẪU IN LƯU THỰC HIỆN";
    const TITLE_COMPANY          = 'Phòng/Đài/MBF HCM: ';
    const TITLE_AREA             = 'Tổ/Quận/Huyện: ';
    const TITLE_POSITION         = 'Chức danh: ';
    /* *****************************************************************************************************************
     * Define function
     * ****************************************************************************************************************/
    public static function fillBackGroundColor($objPHPExcel, $range, $bgColor){
        $objPHPExcel->getActiveSheet()
            ->getStyle($range)
            ->getFill()
            ->setFillType(PHPEXCEL_Style_fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($bgColor)
        ;
    }

    public static function setWrapText($objPHPExcel, $range){
        $objPHPExcel->getActiveSheet()->getStyle($range)->getAlignment()->setWrapText(true);
    }

    public static function setRowHeight($objPHPExcel, $range, $rowHeight){
        $objPHPExcel->getActiveSheet()->getRowDimension($range)->setRowHeight($rowHeight);

    }

    /* *****************************************************************************************************************
     * @param $objPHPExcel
     * @param $range
     * @param $type
     * 1: Number
     * 2: Datetime
     * ****************************************************************************************************************/
    public static function formatCell($objPHPExcel, $range, $type, $styleFormat){
        switch($type) {
            case 1:
                $objPHPExcel->getActiveSheet()->getStyle($range)->getNumberFormat()->setFormatCode($styleFormat);
                break;
            case 2:

                break;
        }

    }

    public static function loadFile($path){
        return PHPExcel_IOFactory::load($path);
    }

    public static function setZoomSheet($objPHPExcel, $percent){
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale($percent);
    }

    public static function activeSheet($objPHPExcel, $sheetIndex){
        return $objPHPExcel->setActiveSheetIndex($sheetIndex);
    }

    public static function setCellValue($objPHPExcel, $cell, $value){
        $objPHPExcel->getActiveSheet()->setCellValue($cell, $value);
    }

    public static function mergeCells($objPHPExcel, $range){
        $objPHPExcel->getActiveSheet()->mergeCells($range);
    }

    public static function setHorizontal($objPHPExcel, $range, $style){
        $objPHPExcel->getActiveSheet()
            ->getStyle($range)
            ->getAlignment()
            ->setHorizontal($style);
    }

    public static function setVertical($objPHPExcel, $range, $style){
        $objPHPExcel->getActiveSheet()
            ->getStyle($range)
            ->getAlignment()
            ->setVertical($style);
    }

    public static function setBorderCell($objPHPExcel, $range, $arrStyle){
        $objPHPExcel->getActiveSheet()->getStyle($range)->applyFromArray($arrStyle);

    }

    public static function setBold($objPHPExcel, $range){
        $objPHPExcel->getActiveSheet()->getStyle($range)->getFont()->setBold(true);
    }

    public static function setDefaultFont($objPHPExcel, $font){
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName($font);
    }

    public static function setDefaultFontSize($objPHPExcel, $size){
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize($size);
    }

    public static function setFontSize($objPHPExcel, $range, $size){
        $objPHPExcel->getActiveSheet()->getStyle($range)->getFont()->setSize($size);
    }

    public static function setColumnWidth($objPHPExcel, $column, $size){
        $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($size);

    }

    public static function setFreezePane($objPHPExcel, $range){
        $objPHPExcel->getActiveSheet()->freezePane($range);
    }

    public static function getIndexColumn($column){
        return \PHPExcel_Cell::columnIndexFromString($column);
    }

    public static function getLabelColumn($indexColumn){
        return  \PHPExcel_Cell::stringFromColumnIndex($indexColumn);
    }

    public static function createSheet($objPHPExcel, $sheetIndex){
        $objPHPExcel->createSheet($sheetIndex);
    }

    public static function setTitle($objPHPExcel, $value){
        $objPHPExcel->getActiveSheet()->setTitle($value);
    }

    public static function getObjectSheetNames($objPHPExcel){
        return $objPHPExcel->getSheetNames();

    }

    public static function getNumOfSheet($objPHPExcel){
        return $objPHPExcel->getSheetCount();
    }

    public static function setFontColor($objPHPExcel, $range, $color){
        $objPHPExcel->getActiveSheet()->getStyle($range)->getFont()->getColor()->setRGB($color);
    }
    public static function styleBorder(){
        return $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
    }

    public static function styleBorderLasted(){
        return $styleLast = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )

            )
        );
    }

    public static function styleBorderChild(){
        return $styleChildRows = array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOTTED
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
    }

    public static function setlandScape($objPHPExcel){
        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    }

    public static function setPageA4($objPHPExcel){
        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    public static function removeSheet($objPHPExcel, $indexSheet){
        $objPHPExcel->removeSheetByIndex($indexSheet);
    }
}