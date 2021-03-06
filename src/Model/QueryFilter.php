<?php
/**
 * Overwarch crawler from blizzard
 *
 * 오버워치 전적 검색
 *
 * Created on 2016. 11.
 * @package      Javamon/Oversearch/Model
 * @category     parser
 * @license      http://opensource.org/licenses/MIT
 * @author       javamon <javamon1174@gmail.com>
 * @link         http://javamon.be
 * @version      1.2.1
 */
namespace Javamon\OverSearch\Model;

use Javamon\OverSearch\Config as Config;

class QueryFilter
{
    use Config;

    public $user_idx;
    private $db_resouce;

    public function __construct() { }

    /**
     * Query generation model
     *
     * @access public
     * @param String $func : Request query
     * @param Array $data : Information for creating query
     * @return Array $query : sql query
    */
    function QueryInit($mk_query_mathod, $mk_query_count = 1)
    {
        //function call
        return $this->$mk_query_mathod($mk_query_count);
    }

    private function CheckUserQuery()
    {
        return "SELECT update_date FROM tb_time WHERE user_idx = (SELECT user_idx FROM tb_summary WHERE user_name = :user_name);";
    }

    private function GetUserIndex()
    {
        return 'SELECT `user_idx` FROM `tb_summary` WHERE user_name = :user_name';
    }

    private function InsertSummaryQuery()
    {
        $query = "INSERT INTO `tb_summary` (`icon`, `level`, `com_grade`, `user_name`, `avg_kill`, `avg_damage`, `avg_death`,
                  `avg_Murderous`, `avg_heal`, `avg_contributions_kill`, `avg_contributions_time`, `avg_solo_kill`, `level_img`, `analy`)
                  VALUES (:icon, :level, :com_grade, :user_name, :avg_kill, :avg_damage, :avg_death, :avg_Murderous, :avg_heal,
                  :avg_contributions_kill, :avg_contributions_time, :avg_solo_kill, :level_img, :analy)";
        return $query;
    }

    //needs to Refactoring
    private function InsertFrequencyQuery($hero_time_data_count)
    {
        $query = "INSERT INTO `tb_frequency` (`user_idx`, `hero`, `freq_index`, `time`,  `win`, `outcome`, `accuracy`, `K/D`, `simul_kill`, `con_kill`)  VALUES ";
        $query_value = "";
        for ($i=0; $i < $hero_time_data_count ; $i++)
        {
            $query_value = $query_value."('".$this->user_idx."', ?, ?, ?, ?, ?, ?, ?, ?, ?),";
        }
        $query_value = substr($query_value, 0, -1); $query_value = $query_value.";";
        return $query.$query_value;
    }

    //needs to Refactoring
    private function InsertStatisticsQuery($statistics_data_count)
    {
        $query = "INSERT INTO `tb_heroes` (`user_idx`, `hero`, `category`, `title`, `value`) VALUES ";
        $query_value = "";
        for ($i=0; $i < $statistics_data_count ; $i++)
        {
            $query_value = $query_value."('".$this->user_idx."', ?, ?, ?, ?),";
        }
        $query_value = substr($query_value, 0, -1); $query_value = $query_value.";";
        return $query.$query_value;
    }

    private function InsertTimeQuery()
    {
        $query = "INSERT INTO `tb_time` (`user_idx`, `update_date`) VALUES (:user_idx, :update_date);";
        return $query;
    }

    private function DeleteUserData()
    {
        // return "DELETE F.*, H.*, S.*, T.* FROM tb_frequency F, tb_heroes H, tb_summary S, tb_time T
        //         WHERE F.user_idx=:user_idx AND H.user_idx=:user_idx AND S.user_idx=:user_idx AND T.user_idx=:user_idx;";
        foreach ($this->query_tables as $key => $table) {
            $query[] = "DELETE FROM $table WHERE `user_idx` = :user_idx;";
        }
        return $query;
    }

    private function SelectFrequency()
    {
        // return "SELECT * FROM  `tb_frequency` WHERE  `user_idx` =:user_idx ORDER BY  `tb_frequency`.`freq_index` ASC ;";
        return "SELECT hero, freq_index, win, outcome, `K/D`, `time` FROM `tb_frequency`
                WHERE  `user_idx` =:user_idx ORDER BY  `tb_frequency`.`freq_index` ASC ;";
    }

    private function SelectHeroes()
    {
      return "SELECT hero, category, title, value FROM  `tb_heroes` WHERE  `user_idx` =:user_idx;";
    }

    private function SelectSummary()
    {
      return "SELECT * FROM tb_summary S, tb_time T WHERE S.user_idx = :user_idx AND T.user_idx = :user_idx;";
    }

    private function SelectAllData()
    {
        $query = "";
        $query = $query."SELECT hero, freq_index, win, outcome, `K/D`, `time` FROM `tb_frequency`
                         WHERE `user_idx` =:user_idx ORDER BY  `tb_frequency`.`freq_index` ASC ;";
        $query  = $query."SELECT hero, category, title, value FROM  `tb_heroes` WHERE  `user_idx` =:user_idx;";
        $query  = $query."SELECT * FROM tb_summary S, tb_time T WHERE S.user_idx = :user_idx AND T.user_idx = :user_idx;";
        return $query;
    }
}
