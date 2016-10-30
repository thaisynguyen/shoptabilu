<?php namespace App\Http\Controllers;
use Session;
use Utils\commonUtils;

class HomeController extends AppController {
    
    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $arrData = array(0 => array('label'                => 'Điểm thực hiện',
                                    'fillColor'            => 'rgba(101,203,236,0.2)',
                                    'strokeColor'          => 'rgba(101,203,236,1)',
                                    'pointColor'           => 'rgba(101,203,236,1)',
                                    'pointStrokeColor'     => '#fff',
                                    'pointHighlightFill'   => '#fff',
                                    'pointHighlightStroke' => 'rgba(101,203,236,1)',
                                    'data'                 => '[65, 59, 80, 81, 56, 55, 40]'),
                         1 => array('label'                => 'Điểm chuẩn',
                                     'fillColor'            => 'rgba(114,102,186,0.2)',
                                     'strokeColor'          => 'rgba(114,102,186,1)',
                                     'pointColor'           => 'rgba(114,102,186,1)',
                                     'pointStrokeColor'     => '#fff',
                                     'pointHighlightFill'   => '#fff',
                                     'pointHighlightStroke' => 'rgba(151,187,205,1)',
                                     'data'                 => '[28, 48, 40, 19, 86, 27, 90]'));
        $arrMonth = array(1, 2, 3, 4, 5, 6, 7);
        $lineChartData = commonUtils::createArrayForChart($arrData,$arrMonth);
        return view('admin.statistics.statistics')->with('lineChartData',$lineChartData);

    }

}
