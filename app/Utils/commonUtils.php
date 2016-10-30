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
    // Group type
    const GROUP_TYPE_NONE               = 0;//KHONG CO LOAI MUC TIEU
    const GROUP_TYPE_BIGGER_IS_BETTER   = 1;//CANG LON CANG TOT
    const GROUP_TYPE_SMALLER_IS_BETTER  = 2;//CANG NHO CANG TOT
    const GROUP_TYPE_PASSED             = 3;//DAT / KHONG DAT
    const GROUP_TYPE_NOT_PASSED         = 4;//KHONG DAT
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
    const IP_SERVER_LDAP_248 = "10.151.70.248";
    const IP_SERVER_LDAP_249 = "10.151.70.249";

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
    // formular
    const FORMULA_LAY1SO                    = 1;
    const FORMULA_TU_NHAP                   = 2;
    const FORMULA_TRUNG_BINH_CONG           = 3;
    const FORMULA_TONG_NVBH                 = 4;
    const FORMULA_TONG_KAM_AM               = 5;
    const FORMULA_TONG_CVKHCN_CVKHDN        = 6;
    const FORMULA_TONG_CVKHCN_CHT           = 7;
    const FORMULA_TONG_GDV                  = 8;
    const FORMULA_TONG_CVKHCN_CVKHDN_CHT    = 9;
    const FORMULA_TONG_CVKHCN               = 10;
    const FORMULA_TONG_CVKHDN               = 11;
    const FORMULA_TONG_CVKHDN_CHT           = 12;

    const POSITION_CODE_TQ      = 'TQ';
    const POSITION_CODE_CHT     = 'CHT';
    const POSITION_CODE_GDV     = 'GDV';
    const POSITION_CODE_NVBH    = 'NVBH';
    const POSITION_CODE_KAM_AM  = 'KAM/AM';
    const POSITION_CODE_CV_KHCN = 'CV KHCN';
    const POSITION_CODE_CV_KHDN = 'CV KHDN';
    const POSITION_CODE_GDV_K   = 'GDV-K';
    const POSITION_CODE_CTV     = 'CTV';
    /******************************************************************************************************************/
    const CORPORATION_CODE = 'MBF';
    const PERCENT_CODE = 'Ptr';
    /******************************************************************************************************************/
    #Title import
    const TITLE_IMPORT_EMPLOYEE         = "IMPORT DANH SÁCH NHÂN VIÊN";
    const TITLE_IMPORT_COMPANY_GOAL     = "IMPORT TỶ TRỌNG CHO PHÒNG/ĐÀI/MBF HCM";
    const TITLE_IMPORT_AREA_GOAL        = "IMPORT TỶ TRỌNG TỔ/QUẬN/HUYỆN";
    const TITLE_IMPORT_POSITION_GOAL    = "IMPORT TỶ TRỌNG CHỨC DANH";
    const TITLE_IMPORT_GOAL_AREA        = "IMPORT KẾ HOẠCH (CHỈ TIÊU) CHO TỔ/QUẬN/HUYỆN";
    const TITLE_IMPORT_GOAL_POSITION    = "IMPORT KẾ HOẠCH(CHỈ TIÊU) CHO CHỨC DANH";
    const TITLE_IMPORT_GOAL_EMPLOYEE    = "IMPORT KẾ HOẠCH (CHỈ TIÊU) CHO NHÂN VIÊN";
    const TITLE_IMPORT_PERFORM_POSITION = "IMPORT THỰC HIỆN CHO CHỨC DANH";
    const TITLE_IMPORT_PERFORM_EMPLOYEE = "IMPORT THỰC HIỆN CHO NHÂN VIÊN";
    const TITLE_IMPORT_GOAL             = "IMPORT MỤC TIÊU";
    const TITLE_IMPORT_CORPORATION      = "IMPORT TỶ TRỌNG CHO CÔNG TY";
    const TITLE_IMPORT_PERFORM_CTV      = "IMPORT THỰC HIỆN CHO CÔNG TÁC VIÊN";
    /*******************************************************************************************************************
     * List array formula in bottom of report
    /******************************************************************************************************************/
    # Array for report Goal Area (KẾ KHOẠCH KHU VỰC)
    public static function arrFormulaGoalArea($rowCalculate, $typeExport)
    {
        switch ($typeExport) {
            case 0:/*exportTargetCompany(Tỷ trọng Công ty)*/
                $array = array(
                    1 => array(
                        'id' => $rowCalculate + 1
                    , 'name' => '             - Tỷ trọng '
                    , 'description' => ' = Tự nhập.'
                    ),
                    2 => array(
                        'id' => $rowCalculate + 2
                    , 'name' => '             - Điểm chuẩn '
                    , 'description' => ''
                    ),
                    3 => array(
                        'id' => $rowCalculate + 3
                    , 'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' =  (Tỷ trọng / Tổng tỷ trọng mục tiêu cha) * 100.'
                    ),
                    4 => array(
                        'id' => $rowCalculate + 4
                    , 'name' => '                 b) Mục tiêu con '
                    , 'description' => ' =  (Tỷ trọng / Tổng tỷ trọng mục tiêu con) * Điểm chuẩn của mục tiêu cha.'
                    ),
                    5 => array(
                        'id' => $rowCalculate + 5
                    , 'name' => '             - Điểm thực hiện '
                    , 'description' => ' =  Tổng điểm thực hiện(KPI) của tất cả các Phòng/Đài/MBF HCM.'
                    ),
                    6 => array(
                        'id' => $rowCalculate + 6
                    , 'name' => '             - Tỷ lệ đạt '
                    , 'description' => ' =  Điểm thực hiện / Điểm chuẩn'
                    )
                );
                break;
            case 1:/*exportTargetCompany(Tỷ trọng Phòng/Đài/MBF HCM) =================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Công ty:' # ----------------------------------------------------------------
                    , 'description' => ''
                    ),
                    1 => array(
                        'name' => '             + Tỷ trọng '
                    , 'description' => ' = Tỷ trọng của công ty.'
                    ),
                    2 => array(
                        'name' => '             + Điểm chuẩn '
                    , 'description' => ''
                    ),
                    3 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (Tỷ trọng / Tổng tỷ trọng mục tiêu cha) * 100.'
                    ),
                    4 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Tỷ trọng / Tổng tỷ trọng mục tiêu con) * Điểm chuẩn của mục tiêu cha.'
                    ),
                    5 => array(
                        'name' => '             + Điểm thực hiện '
                    , 'description' => ' = Tổng điểm thực hiện(KPI) của tất cả các Phòng/Đài/MBF HCM'
                    ),
                    6 => array(
                        'name' => '             + Tỷ lệ đạt '
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn'
                    ),
                    7 => array(
                        'name' => '         - Mỗi Phòng/Đài/MBF HCM '# ---------------------------------------------------
                    , 'description' => ''
                    ),
                    8 => array(
                        'name' => '             + Điểm chuẩn '
                    , 'description' => ''
                    ),
                    9 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (Tỷ trọng / Tổng tỷ trọng mục tiêu cha) * 100.'
                    ),
                    10 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Tỷ trọng / Tổng tỷ trọng mục tiêu con) * Điểm chuẩn của mục tiêu cha.'
                    ),
                    11 => array(
                        'name' => '             + Điểm chuẩn KPI '
                    , 'description' => ' = (Điểm chuẩn của công ty / tổng tỷ trọng của tất cả các Phòng/Đài/MBF HCM có tỷ trọng đối với mục tiêu đó) * tỷ trọng của từng Phòng/Đài/MBF HCM.'
                    ),
                    12 => array(
                        'name' => '             + Tỷ lệ đạt '
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn'
                    ),
                    13 => array(
                        'name' => '             + Điểm thực hiện '
                    , 'description' => ' = Tổng điểm thực hiện(KPI) của các Tổ/Quận/Huyện thuộc Phòng/Đài/MBF HCM'
                    )
                );
                break;
            case 2:/*exportTargetArea(Tỷ trọng Tổ/Quận/Huyện) ========================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Phòng/Đài/MBF HCM đang chọn.'
                    , 'description' => ''
                    ),
                    1 => array(
                        'name' => '             + Điểm chuẩn:'
                    , 'description' => ''
                    ),
                    2 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục tiêu đó.'
                    ),
                    3 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Điểm chuẩn của mục tiêu cha / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu đó.'
                    ),
                    4 => array(
                        'name' => '             + Điểm chuẩn KPI:'
                    , 'description' => ' = (Điểm chuẩn của Phòng/Đài/MBF HCM * Tổng tỷ trọng của tất cả cả các Tổ/Quận/Huyện có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    ),
                    5 => array(
                        'name' => '                 a) Mục tiêu cha/con '
                    , 'description' => ' = (Điểm chuẩn của công ty / tổng tỷ trọng của tất cả các Phòng/Đài/MBF HCM có tỷ trọng đối với mục tiêu đó) * tỷ trọng của Phòng/Đài/MBF HCM.'
                    ),
                    6 => array(
                        'name' => '             + Điểm thực hiện:'
                    , 'description' => ' = Tổng điểm thực hiện[KPI] của các Tổ/Quận/Huyện thuộc Phòng/Đài/MBF HCM.'
                    ),
                    7 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn.'
                    ),
                    8 => array(
                        'name' => '         - Mỗi Tổ/Quận/Huyện:'
                    , 'description' => ''
                    ),
                    9 => array(
                       'name' => '             + Điểm chuẩn:'
                    , 'description' => 'Là điểm được tính từ 100 phân bổ cho các mục tiêu cha và từ điểm chuẩn các mục tiêu cha đó tính điểm chuẩn cho các mục tiêu con.'
                    ),
                    10 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục tiêu đó.'
                    ),
                    11 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Điểm chuẩn của mục tiêu cha / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu đó.'
                    ),

                    12 => array(
                        'name' => '             + Điểm chuẩn KPI:'
                    , 'description' => ' Là điểm chuẩn do Phòng/Đài/MBF HCM phân bổ xuống đối với từng mục tiêu tương ứng.'
                    ),
                    13 => array(
                        'name' => '                 a) Mục tiêu cha/con '
                    , 'description' => ' = (Điểm chuẩn của Phòng/Đài/MBF HCM / Tổng tỷ trọng của tất cả cả các Tổ/Quận/Huyện có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    ),
                    14 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn.'
                    ),
                    15 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => ' = Tổng điểm thực hiện của tất cả các chức danh trong Tổ/Quận/Huyện.'
                    )
                );
                break;
            case 3:/*exportPriorityPosition(Tỷ trọng Chức danh) ======================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Tổ/Quận/Huyện đang chọn.'
                    , 'description' => ''
                    ),
                    1 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ''
                    ),
                    2 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng.'
                    ),
                    3 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Điểm chuẩn của mục tiêu cha / Tổng tỷ trọng mục tiêu con) * Tỷ trọng.'
                    ),
                    4 => array(
                        'name' => '             + Điểm chuẩn[KPI]'
                    , 'description' => 'Không phân biệt mục tiêu cha/con'
                    ),
                    5 => array(
                        'name' => '                  '
                    , 'description' => ' = (Điểm chuẩn của Phòng/Đài/MBF HCM  / Tổng tỷ trọng của tất cả Tổ/Quận/Huyện có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    ),
                    6 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn[KPI]'
                    ),
                    7 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => ' = Tổng điểm thực hiện của tất cả các chức danh trong Tổ/Quận/Huyện.'
                    ),
                    8 => array(
                        'name' => '         - Mỗi Chức danh'
                    , 'description' => ''
                    ),
                    9 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay'
                    ),
                    10 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ''
                    ),
                    11 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng.'
                    ),
                    12 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (Điểm chuẩn của mục tiêu cha / Tổng tỷ trọng mục tiêu con) * Tỷ trọng.'
                    ),
                    13 => array(
                        'name' => '             + Điểm chuẩn[KPI]'
                    , 'description' => 'Không phân biệt mục tiêu cha/con'
                    ),
                    14 => array(
                        'name' => '                  '
                    , 'description' => ' = (Điểm chuẩn của Tổ/Quận/Huyện  / Tổng tỷ trọng của tất cả Chức danh có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    )
                );
                break;
            case 4: /*exportGoalArea(Kế hoạch Tổ/Quận/Huyện) ========================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Phòng/Đài/MBF HCM đang chọn.'
                    , 'description' => ''
                    ),
                    1 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay.'
                    ),
                    2 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => 'Nhập tay.'
                    ),
                    3 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ''
                    ),
                    4 => array(
                        'name' => '                    a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục cha.'
                    ),
                    5 => array(
                        'name' => '                    b) Mục tiêu con '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu con.'
                    ),
                    6 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn'
                    ),
                    7 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => ' = Tổng điểm thực hiện của các Tổ/Quận/Huyện thuộc Phòng/Đài/MBF HCM'
                    ),
                    8 => array(
                        'name' => '         - Mỗi Tổ/Quận/Huyện'
                    , 'description' => ''
                    ),
                    9 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay'
                    ),
                    10 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => 'Nhập tay'
                    ),
                    11 => array(
                        'name' => '             + Điểm chuẩn[KPI]'
                    , 'description' => 'Không phân biệt mục tiêu cha/con'
                    ),
                    12 => array(
                        'name' => '                  '
                    , 'description' => ' = (Điểm chuẩn của Phòng/Đài/MBF HCM  / Tổng tỷ trọng của tất cả Tổ/Quận/Huyện có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    ),
                    13 => array(
                        'name' => '             + Điểm thực hiện[KPI]'
                    , 'description' => 'Tỷ lệ đạt * Điểm chuẩn[KPI]'
                    ),
                    14 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ''
                    ),
                    15 => array(
                        'name' => '                    a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục cha.'
                    ),
                    16 => array(
                        'name' => '                    b) Mục tiêu con '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu con.'
                    ),
                    17 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => 'Tổng diểm thực hiện của tất cả các chức danh trong Tổ/Quận/Huyện.'
                    ),
                    18 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn.'
                    )
                );
                break;
            case 5:/*exportGoalPosition(Kế hoạch Chức danh) ========================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Tổ/Quận/Huyện đang chọn.'
                    , 'description' => ''
                    ),
                    1 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay.'
                    ),
                    2 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => 'Nhập tay.'
                    ),
                    3 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ''
                    ),
                    4 => array(
                         'name' => '                a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục cha.'
                    ),
                    5 => array(
                         'name' => '                b) Mục tiêu con '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu con.'
                    ),
                    7 => array(
                       'name' => '             + Điểm thực hiện'
                    , 'description' => ' = Tổng điểm thực hiện của các Chức danh thuộc Tổ/Quận/Huyện'
                    ),
                    8 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn'
                    ),
                    9 => array(
                        'name' => '         - Mỗi Chức danh'
                    , 'description' => ''
                    ),
                    10 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay.'
                    ),
                    11 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => ' = Nhập tay.'
                    ),
                    12 => array(
                        'name' => '             + Điểm chuẩn KPI'
                    , 'description' => ' = (Điểm chuẩn Tổ/Quận/Huyện / Tổng tỷ trọng của tất cả Chức danh có tỷ trọng đối với mục tiêu đó) * tỷ trọng của mục tiêu đó.'
                    ),
                    13 => array(
                        'name' => '             + Thực hiện'
                    , 'description' => ''
                    ),
                    14 => array(
                        'name' => '                 > Đối với Chức danh không phải là Trưởng quận'
                    , 'description' => ''
                    ),
                    15 => array(
                        'name' => '                     a) Nếu công thức không phải là Lấy một số hoặc Trung bình cộng'
                    , 'description' => ' = Tổng thực hiện của tất cả nhân viên thuộc Tổ/Quận/Huyện đang xét.'
                    ),
                    16 => array(
                        'name' => '                     b)  Công thức lấy một số'
                    , 'description' => ' =  Thực hiện của bất kì nhân viên nào thuộc Tổ/Quận/Huyện đang xét.'
                    ),
                    17 => array(
                        'name' => '                 > Đối với Chức danh không phải là Trưởng quận:'
                    , 'description' => ' = Tổng thực hiện của tất cả nhân viên thuộc Tổ/Quận/Huyện đang xét. '
                    ),
                    18 => array(
                        'name' => '                     a) Lấy một số:'
                    , 'description' => ' = Thực hiện của bất kì nhân viên nào có chức danh không phải Trưởng Quận.'
                    ),
                    19 => array(
                        'name' => '                     b) Trung bình cộng:'
                    , 'description' => ' = Tương tự lấy một số.'
                    ),
                    20 => array(
                        'name' => '                     c) Tổng NVBH:'
                    , 'description' => ' = Thực hiện của chức danh nhân viên bán hàng.'
                    ),
                    21 => array(
                        'name' => '                     d) Tổng KAM/AM:'
                    , 'description' => ' = Thực hiện của chức danh KAM/AM'
                    ),
                    22 => array(
                        'name' => '                     e) Tổng CV KHDN & Tổng CV KHCN:'
                    , 'description' => ' = Tổng thực hiện của hai chức danh Chuyên viên quận KHCN và Chuyên viên quận KHDN.'
                    ),
                    23 => array(
                        'name' => '                     f) Tổng CV KHCN & Tổng CHT:'
                    , 'description' => ' = Tổng thực hiện của hai chức danh Chuyên viên quận KHCN và Cửa hàng trưởng.'
                    ),
                    24 => array(
                        'name' => '                     g) Tổng GDV:'
                    , 'description' => ' = Thực hiện của chức danh KAM/AM.'
                    ),
                    25 => array(
                        'name' => '                     h) Tổng CV PTTT:'
                    , 'description' => ' = Thực hiện của chức danh chuyên viên phát triển thị trường.'
                    ),
                    26 => array(
                        'name' => '                     i) Tự nhập:'
                    , 'description' => ' = Được tính khi import thực hiện cho nhân viên có chức danh là Trưởng Quận.'
                    ),
                    27 => array(
                        'name' => '             + Điểm thực hiện:'
                    , 'description' => ''
                    ),
                    28 => array(
                        'name' => '                 > Càng lớn càng tốt'
                    , 'description' => ' Điểm thực hiện = Thực hiện / Kế hoạch * Điểm chuẩn KPI (Chức danh).'
                    ),
                    29 => array(
                       'name' => '                 > Càng nhỏ càng tốt'
                    , 'description' => ' Điểm thực hiện = Kế hoạch / Thực hiện * Điểm chuẩn KPI (Chức danh).'
                    ),
                    30 => array(
                        'name' => '                 > Đạt / Không đạt'
                    , 'description' => ''
                    ),
                    31 => array(
                        'name' => '                     a) Thực hiện khác Kế hoạch:'
                    , 'description' => ' Điểm thực hiện = 0.'
                    ),
                    32 => array(
                        'name' => '                     b) Ngược lại:'
                    , 'description' => ' Điểm thực hiện = Điểm chuẩn KPI (Chức danh).'
                    )
                );
                break;
            case 6:
            /*exportGoalEmployee(Kế hoạch Nhân viên) ========================================================*/
                $array = array(
                    0 => array(
                        'name' => '         - Phần tổng lấy dữ liệu các cột từ Chức danh của sheet.'
                    , 'description' => ''
                    ),
                    1 => array(
                       'name' => '             + Kế hoạch'
                    , 'description' => 'Nhập tay.'
                    ),
                    2 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => 'Nhập tay.'
                    ),
                    3 => array(
                        'name' => '             + Điểm chuẩn[KPI]'
                    , 'description' => 'Không phân biệt mục tiêu cha/con'
                    ),
                    4 => array(
                        'name' => '                  '
                    , 'description' => ' = (Điểm chuẩn của Tổ/Quận/Huyện  / Tổng tỷ trọng của tất cả Chức danh có tỷ trọng đối với mục tiêu đó) * Tỷ trọng của mục tiêu đó.'
                    ),
                    5 => array(
                        'name' => '             + Thực hiện'
                    , 'description' => ''
                    ),
                    6 => array(
                        'name' => '                 a) Chức danh Trưởng quận:'
                    , 'description' => ' Chia làm 2 loại:'
                    ),
                    7 => array(
                        'name' => '                     1) Đối với các mục tiêu có công thức là tự nhập:'
                    , 'description' => '  Lấy số thực hiện của nhân viên có chức danh Trưởng quận đối với mục tiêu đó.'
                    ),
                    8 => array(
                        'name' => '                     2) Các mục tiêu còn lại:'
                    , 'description' => ' Áp dụng Công thức đối với từng mục tiêu'
                    ),
                    9 => array(
                        'name' => '                          2.1) Lấy một số'
                    , 'description' => ' Lấy 1 số thực hiện khác 0 của một chức danh bất kỳ đối với mục tiêu đó.'
                    ),
                    10 => array(
                        'name' => '                          2.2) Trung bình cộng'
                    , 'description' => ' Lấy 1 số thực hiện khác 0 của một chức danh bất kỳ đối với mục tiêu đó.'
                    ),
                    11 => array(
                        'name' => '                          2.3) Tổng NVBH'
                    , 'description' => ' Lấy số thực hiện của chức danh NVBH đối với mục tiêu đó.'
                    ),
                    12 => array(
                        'name' => '                          2.4) Tổng KAM/AM'
                    , 'description' => ' Lấy số thực hiện của chức danh KAM/AM đối với mục tiêu đó.'
                    ),
                    13 => array(
                        'name' => '                          2.5) Tổng CV KHDN + CV KHCN'
                    , 'description' => ' Lấy tổng số thực hiện của chức danh CV KHDN và CV KHCN đối với mục tiêu đó.'
                    ),
                    14 => array(
                        'name' => '                          2.6) Tổng CV KHCN + CHT'
                    , 'description' => ' Lấy tổng số thực hiện của chức danh CV KHCN và CHT đối với mục tiêu đó.'
                    ),
                    15 => array(
                        'name' => '                          2.7) Tổng GDV'
                    , 'description' => ' Lấy tổng số thực hiện của chức danh GDV và GDV-K đối với mục tiêu đó.'
                    ),
                    16 => array(
                        'name' => '                 b) Các chức danh còn lại:'
                    , 'description' => ' Nếu mục tiêu có công thức khác Lấy một số và Trung bình cộng thì Số thực hiện = Tổng số thực hiện của nhân viên có chức danh đối với mục tiêu đó.'
                    ),
                    17 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => ' Dựa theo loại mục tiêu'
                    ),
                    18 => array(
                        'name' => '                 a) Càng lớn càng tốt:'
                    , 'description' => ' = (Số thực hiện / Số kế hoạch) * Điểm chuẩn[KPI]. '
                    ),
                    19 => array(
                        'name' => '                 b) Càng bé càng tốt:'
                    , 'description' => ' = (Số kế hoạch / Số thực hiện) * Điểm chuẩn[KPI]. '
                    ),
                    20 => array(
                        'name' => '                 c) Đạt/ Không đạt :'
                    , 'description' => ' = 0 Nếu Số thực hiện khác Số kế hoạch, Ngược lại =  Điểm chuẩn[KPI]. '
                    ),
                    21 => array(
                        'name' => '         - Mỗi Nhân viên'
                    , 'description' => ''
                    ),
                    22 => array(
                        'name' => '             + Kế hoạch'
                    , 'description' => ' Nhập tay.'
                    ),
                    23 => array(
                        'name' => '             + Thực hiện'
                    , 'description' => ' Chia là 2 loại:'
                    ),
                    24 => array(
                        'name' => '                 a) Nhân viên có chức danh Trưởng quận:'
                    , 'description' => 'Chỉ import thực hiện đối với các mục tiêu có công thức Tự nhập, Các mục tiêu còn lại sẽ được cập nhật lúc cập nhật thực hiện cho chức danh Trưởng quận.'
                    ),
                    25 => array(
                        'name' => '                 b) Nhân viên có chức danh còn lại: '
                    , 'description' => 'Nhập tay.'
                    ),
                    26 => array(
                        'name' => '             + Tỷ trọng'
                    , 'description' => ' = Tỷ trọng của chức danh tương ứng.'
                    ),
                    27 => array(
                        'name' => '             + Điểm chuẩn'
                    , 'description' => ' = Điểm chuẩn của chức danh tương ứng.'
                    ),
                    28 => array(
                        'name' => '                 a) Mục tiêu cha '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu cha) * Tỷ trọng của mục cha.'
                    ),
                    29 => array(
                        'name' => '                 b) Mục tiêu con '
                    , 'description' => ' = (100 / Tổng tỷ trọng mục tiêu con) * Tỷ trọng của mục tiêu con.'
                    ),
                    30 => array(
                        'name' => '             + Điểm thực hiện'
                    , 'description' => ' Dựa theo loại mục tiêu'
                    ),
                    31 => array(
                        'name' => '                 a) Càng lớn càng tốt:'
                    , 'description' => ' = (Số thực hiện / Số kế hoạch) * Điểm chuẩn[KPI]. '
                    ),
                    32 => array(
                        'name' => '                 b) Càng bé càng tốt:'
                    , 'description' => ' = (Số kế hoạch / Số thực hiện) * Điểm chuẩn[KPI]. '
                    ),
                    33 => array(
                        'name' => '                 c) Đạt/ Không đạt :'
                    , 'description' => ' = 0 Nếu Số thực hiện khác Số kế hoạch, Ngược lại =  Điểm chuẩn[KPI]. '
                    ),
                    34 => array(
                        'name' => '             + Tỷ lệ đạt'
                    , 'description' => ' = Điểm thực hiện / Điểm chuẩn.'
                    )
                );
                break;
        }
        return $array;
    }
    /******************************************************************************************************************/
    /**
     * Create recursive tree
     * @param $flat
     * @param $pidKey
     * @param null $idKey
     * @return mixed
     * $targetGroup = array(
        array('id'=>100, 'parentID'=>0, 'name'=>'Group A', 'click'=>'', 'date' => ''),
        array('id'=>101, 'parentID'=>100, 'name'=>'Group A1', 'click'=>'', 'date' => ''),
        array('id'=>102, 'parentID'=>101, 'name'=>'小分類1', 'click'=>'100', 'date' => '2015-01-01'),
        array('id'=>103, 'parentID'=>101, 'name'=>'小分類2', 'click'=>'200', 'date' => '2015-01-01'),
        array('id'=>104, 'parentID'=>101, 'name'=>'小分類1', 'click'=>'300', 'date' => '2015-01-02')
        );
     *
     * buildTree($targetGroup, 'parentID', 'id')
     */
    public static function buildTree($flat, $pidKey, $idKey = null)
    {
        $grouped = array();
        foreach ($flat as $sub) {
            $grouped[$sub[$pidKey]][] = $sub;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $grouped, $idKey) {

            foreach ($siblings as $k => $sibling) {
                $id = $sibling[$idKey];


                if (isset($grouped[$id])) {

                    $sibling['children'] = $fnBuilder($grouped[$id]);
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        $tree = $fnBuilder($grouped[0]);

        return $tree;
    }

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
     * array goal_type
     */
    public static function arrGoalType($goalTypeId)
    {

        $array = array(
            0 => array(
                'id' => '1',
                'name' => 'Càng lớn càng tốt'
            ),
            1 => array(
                'id' => '2',
                'name' => 'Càng nhỏ càng tốt'
            ),
            2 => array(
                'id' => '3',
                'name' => 'Đạt/Không đạt'
            )
        );
        if ($goalTypeId > 0) {
            unset($array[$goalTypeId - 1]);
        }
        return $array;
    }

    /**
     * @param input a parent_key
     * @return name of parent_key
     */
    public static function renderGoalTypeName($value)
    {
        $name = '';
        switch ($value) {
            case 0:
                $name = self::GOAL_TYPE_PARENT;
                break;
            case 1:
                $name = self::CANG_LON_CANG_TOT;
                break;
            case 2:
                $name = self::CANG_NHO_CANG_TOT;
                break;
            case 3:
                $name = self::DAT_KHONG_DAT;
                break;
        }
        return $name;
    }

    public static function arrFormulaOfGoalType(){
        $array = array(0 => array('id'   => self::FORMULA_LAY1SO,
                                  'name' => 'Lấy 1 số'),
                       1 => array('id'   => self::FORMULA_TU_NHAP,
                                  'name' => 'Tự nhập' ),
                       2 => array('id'   => self::FORMULA_TRUNG_BINH_CONG,
                                  'name' => 'Trung bình cộng' ),
                       3 => array('id'   => self::FORMULA_TONG_NVBH,
                                 'name'  => 'Tổng NVBH'),
                       4 => array('id'   => self::FORMULA_TONG_KAM_AM,
                                'name'   => 'Tổng KAM/AM' ),
                       5 => array('id'   => self::FORMULA_TONG_CVKHCN_CVKHDN,
                                'name'   => 'Tổng CV KHCN và CV KHDN'),
                       6 => array('id'   => self::FORMULA_TONG_CVKHCN_CHT,
                                'name'   => 'Tổng CV KHCN và CHT'),
                       7 => array('id'   => self::FORMULA_TONG_GDV,
                                'name'   => 'Tổng GDV'),
                       8 => array('id'   => self::FORMULA_TONG_CVKHCN_CVKHDN_CHT,
                                'name'   => 'Tổng CV KHCN, CV KHDN, CHT'),
                       9 => array('id'   => self::FORMULA_TONG_CVKHCN,
                            'name'   => 'Tổng CV KHCN'),
                       10 => array('id'   => self::FORMULA_TONG_CVKHDN,
                            'name'   => 'Tổng CV KHDN'),
                       11 => array('id'   => self::FORMULA_TONG_CVKHDN_CHT,
                            'name'   => 'Tổng CV KHDN và CHT')
        );

        return $array;
    }


    public static function renderFormulaOfGoalType($value)
    {
        $formula = '';
        switch ($value) {
            case self::FORMULA_LAY1SO:
                $formula = 'Lấy 1 số';
                break;
            case self::FORMULA_TU_NHAP:
                $formula = 'Tự nhập';
                break;
            case self::FORMULA_TRUNG_BINH_CONG:
                $formula = 'Trung bình cộng';
                break;
            case self::FORMULA_TONG_NVBH:
                $formula = 'Tổng NVBH';
                break;
            case self::FORMULA_TONG_KAM_AM:
                $formula = 'Tổng KAM/AM';
                break;
            case self::FORMULA_TONG_CVKHCN_CVKHDN:
                $formula = 'Tổng CV KHCN và CV KHDN';
                break;
            case self::FORMULA_TONG_CVKHCN_CHT:
                $formula = 'Tổng CV KHCN và CHT';
                break;
            case self::FORMULA_TONG_GDV:
                $formula = 'Tổng GDV';
                break;
            case self::FORMULA_TONG_CVKHCN_CVKHDN_CHT:
                $formula = 'Tổng CV KHCN, CV KHDN, CHT';
                break;
            case self::FORMULA_TONG_CVKHCN:
                $formula = 'Tổng CV KHCN';
                break;
            case self::FORMULA_TONG_CVKHDN:
                $formula = 'Tổng CV KHDN';
                break;
            case self::FORMULA_TONG_CVKHDN_CHT:
                $formula = 'Tổng CV KHDN và CHT';
                break;
        }
        return $formula;
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

    public static function buildTreeTest(array $elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
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
     * @return mixed
     */
    public static function renderGoalName($key, $arr)
    {
        foreach ($arr as $goal) {
            if ($goal['id'] == $key) {
                return $goal['goal_name'];
            }
        }
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
     * @param $functionName
     * @param $action
     * @param $oldValue
     * @param $newValue
     * @param $createdUser
     * @param $createdDate
     * @return $arrayLog
     */
    public static function createArrayLog($functionName, $action, $url, $idRow, $oldValue, $newValue, $createdUser, $createdDate)
    {
        $arrayLog = array('function_name' => $functionName,
            'action' => $action,
            'url' => $url,
            'id_row' => $idRow,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_user' => $createdUser,
            'created_date' => $createdDate);
        return $arrayLog;
    }


    public static function arrMethodImport(){
        //khong thay doi id vi se dem so sanh voi access_level
        $listType = array(
            10 => array('id' => 4, 'name' => 'Người dùng')
            , 11 => array('id' => 1, 'name' => 'Mục tiêu')
            , 0 => array('id' => 7, 'name' => 'Tỷ trọng cho Công ty')
            , 1 => array('id' => 2, 'name' => 'Tỷ trọng cho Phòng/Đài/MBF HCM')
            , 3 => array('id' => 10,'name' => 'Tỷ trọng cho Tổ/Quận/Huyện (1..n)')
            , 4 => array('id' => 3, 'name' => 'Tỷ trọng cho chức danh (n..n)')
            , 5 => array('id' => 5, 'name' => 'Kế hoạch cho Tổ/Quận/Huyện (n..1)')
            , 6 => array('id' => 9, 'name' => 'Kế hoạch cho chức danh (n..n)')
            , 7 => array('id' => 6, 'name' => 'Kế hoạch cho nhân viên (n..n)')
            , 8 => array('id' => 11, 'name' => 'Thực hiện cho Cộng tác viên')
            , 9 => array('id' => 8, 'name' => 'Thực hiện cho nhân viên (1..n)')
//

        );
        return $listType;
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

    public static function calculatorIP($targetValue, $implement, $benchmark, $goalType){

        $implementPoint = 0;

        switch ($goalType) {
            case 1:
                $implementPoint = ($targetValue != 0) ? ($implement / $targetValue) * $benchmark : 0;
                break;
            case 2:

                if($implement == $targetValue || $implement == 0){
                    $implementPoint = $benchmark;
                }else if($implement != 0){
                    $implementPointTemp = ($targetValue / $implement) * $benchmark ;
                    $implementPoint = ($implementPointTemp > $benchmark) ? $benchmark : $implementPointTemp;
                }

                break;
            case 3:
                $implementPoint = ($implement == $targetValue) ? $benchmark : 0;
                break;
        }

        return $implementPoint;

    }

    public static function dateDifference($strFromDate , $strToDate , $differenceFormat = '%R%a' ){

        $fromDate   = date_create($strFromDate);
        $toDate     = date_create($strToDate);

        $interval   = date_diff($fromDate, $toDate);

        return $interval->format($differenceFormat);

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

    public static function checkIsView(){
        $level = Session::get('saccess_level');
        $isView = Session::get('sis_view');
        $view = false;

        if($level > 3 || $isView == 1){
            $view = true;
        }
        return $view;
    }
	
	public static function generalCode(){
        $code  = "NV";
        $temp = "";
        for ($i = 0; $i < 6; $i++){
            $temp .= mt_rand(0,9);
        }

        $str = $temp.str_replace('-','',date('Y-m-d')).str_replace('-','',date('h-m-s'));

        $size = strlen( $str );

        for( $i = 0; $i < 6; $i++ ) {
            $code .= $str[ rand( 0, $size - 1 ) ];
        }

        return $code;
    }

    /* *****************************************************************************************************************
     * @param $accessLevel
     * @param $key
     * @return int
     * 1: Công ty
     * 2: Phòng/Chi Nhánh/Đài
     * 3: Tố/Quận
     * 4: Nhóm/ Cửa hàng
     * 5: Nhân viên
     * ****************************************************************************************************************/
    public static function setPermissionImport($accessLevel){
        $arrKey = array();
        switch($accessLevel) {
            case 1:
                $arrKey = array(0, 1, 3, 4, 10, 11);
                break;
            case 2:
                //$arrKey = array(5); old access level, open this access level when case 3 OK
                $arrKey = array(5, 6, 7, 9);/** New access level for support new bie */
                break;
            case 3:
                $arrKey = array(6, 7, 9);
                break;
            case 4:
                $arrKey = array(6, 7, 9);
                break;
        }

        return $arrKey;
    }

    public static function renderTypeLock($typeNumber){
        $result = '';
        switch($typeNumber){
            case 0:
                $result = 'Tất cả';
                break;
            case 1:
                $result = 'Kế hoạch';
                break;
            case 2:
                $result = 'Thực hiện';
                break;
        }
        return $result;
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

    public static function arrPositionCode(){

        $arrKey = array(
          0 => self::POSITION_CODE_TQ
        , 1 => self::POSITION_CODE_CHT
        , 2 => self::POSITION_CODE_GDV
        , 3 => self::POSITION_CODE_CV_KHDN
        , 4 => self::POSITION_CODE_KAM_AM
        , 5 => self::POSITION_CODE_CV_KHCN
        , 6 => self::POSITION_CODE_NVBH
        );
        return $arrKey;

    }

    public static function tempPosition(){

        $arrKey = array(
        0 => array('id' => 0, 'name' => self::POSITION_CODE_TQ)
        , 1 => array('id' => 1, 'name' => self::POSITION_CODE_CHT)
        , 2 => array('id' => 2, 'name' => self::POSITION_CODE_GDV)
        , 3 => array('id' => 3, 'name' => self::POSITION_CODE_CV_KHDN)
        , 4 => array('id' => 4, 'name' => self::POSITION_CODE_KAM_AM)
        , 5 => array('id' => 5, 'name' => self::POSITION_CODE_CV_KHCN)
        , 6 => array('id' => 6, 'name' => self::POSITION_CODE_NVBH)
        );
        return $arrKey;

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

