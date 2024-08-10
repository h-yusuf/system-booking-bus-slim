<?php namespace App\Lib;

/**
 * Pagination
 *
 * @author    
 */
class Pagination
{
    static protected $url;

    public function __construct()
    {
        $this->url = $_SERVER["REQUEST_URI"];
    }

    //paging
    public static function pagination($query,$per_page = 10){
        $page = (int) (!isset($_GET["page"]) ? 1 : $_GET["page"]);
        $total = $query;
        $splitter = 2;
        $url1=self::$url."?page=";
        $page = ($page == 0 ? 1 : $page);
        $start = ($page - 1) * $per_page;

        $firstPage = 1;
        $prev = ($page == 1)? 1:$page - 1;

        $prev = $page - 1;
        $next = $page + 1;
        $lastpage = ceil($total/$per_page);
        $lpm1 = $lastpage - 1;
        $html = "";
        if($lastpage > 1)
        {
            $html .= '<ul class="hezpaging">';
            $html .= "<li class='details'>Page $page of $lastpage</li>";

            if ($page == 1) {
                $html.= "<li><a class='current'>First</a></li>";
                $html.= "<li><a class='current'>Prev</a></li>";
            }
            else {
                $html.= "<li><a href='".$url1."$firstPage'>First</a></li>";
                $html.= "<li><a href='".$url1."$prev'>Prev</a></li>";
            }

            if ($lastpage < 7 + ($splitter * 2)){
                for ($counter = 1; $counter <= $lastpage; $counter++){
                    if ($counter == $page)
                        $html.= "<li><a class='current'>$counter</a></li>";
                    else
                        $html.= "<li><a href='".$url1."$counter'>$counter</a></li>";
                }
            }
            elseif($lastpage > 5 + ($splitter * 2)){
                if($page < 1 + ($splitter * 2)){
                    for ($counter = 1; $counter < 4 + ($splitter * 2); $counter++){
                        if ($counter == $page)
                            $html.= "<li><a class='current'>$counter</a></li>";
                        else
                            $html.= "<li><a href='".$url1."$counter'>$counter</a></li>";
                    }
                    $html.= "<li class='dot'>...</li>";
                    $html.= "<li><a href='".$url1."$lpm1'>$lpm1</a></li>";
                    $html.= "<li><a href='".$url1."$lastpage'>$lastpage</a></li>";
                }
                elseif($lastpage - ($splitter * 2) > $page && $page > ($splitter * 2)){
                    $html.= "<li><a href='".$url1."1'>1</a></li>";
                    $html.= "<li><a href='".$url1."2'>2</a></li>";
                    $html.= "<li class='dot'>...</li>";
                    for ($counter = $page - $splitter; $counter <= $page + $splitter; $counter++){
                        if ($counter == $page)
                            $html.= "<li><a class='current'>$counter</a></li>";
                        else
                            $html.= "<li><a href='".$url1."$counter'>$counter</a></li>";
                    }
                    $html.= "<li class='dot'>..</li>";
                    $html.= "<li><a href='".$url1."$lpm1'>$lpm1</a></li>";
                    $html.= "<li><a href='".$url1."$lastpage'>$lastpage</a></li>";
                }else{
                    $html.= "<li><a href='".$url1."1'>1</a></li>";
                    $html.= "<li><a href='".$url1."2'>2</a></li>";
                    $html.= "<li class='dot'>..</li>";
                    for ($counter = $lastpage - (2 + ($splitter * 2)); $counter <= $lastpage; $counter++){
                        if ($counter == $page)
                            $html.= "<li><a class='current'>$counter</a></li>";
                        else
                            $html.= "<li><a href='".$url1."$counter'>$counter</a></li>";
                    }}}
            if ($page < $counter - 1){
                $html.= "<li><a href='".$url1."$next'>Next</a></li>";
                $html.= "<li><a href='".$url1."$lastpage'>Last</a></li>";
            }else{
                $html.= "<li><a class='current'>Next</a></li>";
                $html.= "<li><a class='current'>Next</a></li>";
            }
            $html.= "</ul>\n";
        }
        return $html;
    }

    public static function pageStarter($limit){
        $page = (int) (!isset($_GET["page"]) ? 1 : $_GET["page"]);
        return ($page * $limit) - $limit;
    }
}
