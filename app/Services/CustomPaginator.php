<?php namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Utils\commonUtils;

class CustomPaginator {
    /**
     * Create custom paginator
     * @param $listData
     * @param $perPage
     * @param $pageNumber
     * @return LengthAwarePaginator
     */
	public static function renderLengthAwarePaginator($listData, $perPage, $pageNumber){
        // Start displaying items from this number;
        $offSet = ($pageNumber * $perPage) - $perPage;

        $itemsForCurrentPage = array_slice(commonUtils::objectToArray($listData), $offSet, $perPage);

        //convert to object
        $arrPerPage = array();
        foreach($itemsForCurrentPage as $item){
            array_push($arrPerPage, (object)$item);
        }

        //create custom paginator
        $data = new LengthAwarePaginator($arrPerPage, count($listData), $perPage, $pageNumber);
        return $data;
	}

}
