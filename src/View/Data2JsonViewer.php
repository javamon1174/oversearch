<?php
/**
 * Overwarch crawler from blizzard
 *
 * 오버워치 전적 검색
 *
 * Created on 2016. 11.
 * @package      Javamon/Oversearch/View
 * @category     parser
 * @license      http://opensource.org/licenses/MIT
 * @author       javamon <javamon1174@gmail.com>
 * @link         http://javamon.be
 * @version      1.2.1
 */
namespace Javamon\OverSearch\View;

use Javamon\OverSearch\Config as Config;

class Data2JsonViewer
{
    use Config;

    /**
     * Data2JsonViewer
     * @access public
     * @param Array $data : data from Blizzard
     * @param String $msg : error message
     * @return boolean $result : result of send to viewer
     */
    public function Data2JsonViewer($data = array(), $msg)
    {
        if (empty($msg))
        $this->ViewJsonType($data);

        else
        $this->ViewJsonType($msg);
    }

    public function __construct() { }

    /**
     * @access public
     * @param Array $view_data : View data
     * @return String $view_data_json : Json data
     */
    private function ViewJsonType($view_data)
    {
        echo json_encode($view_data);
        return true;
    }
}
