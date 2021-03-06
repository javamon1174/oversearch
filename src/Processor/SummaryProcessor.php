<?php
/**
 * Overwarch crawler from blizzard
 *
 * 오버워치 전적 검색
 *
 * Created on 2016. 11.
 * @package      Javamon/Oversearch/Processor
 * @category     parser
 * @license      http://opensource.org/licenses/MIT
 * @author       javamon <javamon1174@gmail.com>
 * @link         http://javamon.be
 * @version      1.2.1
 */
namespace Javamon\OverSearch\Processor;

use Javamon\OverSearch\Config as Config;

class SummaryProcessor extends SearchProcesser
{
    use Config;

    protected $viewer;

    public function SummaryProcesser()
    {
        $this->setUserNameData2Var();
        $this->SetViewerClassObj();
    }

    public function __construct() { }

    /**
     * this method processes the parsed data
     * @access protected
     * @param   String  $data_from_blizzard     : data from blizzard
     * @return  String  $summary_data           : summary processed data
     */
    protected function SummayData2Array($data_from_blizzard) {

        if (empty($data_from_blizzard))
        {
            $patal_error['msg'] = 'parser not working';
            $this->viewer->Data2JsonViewer(null, $patal_error);
            die;
        }

        $pattern = '/<h3 class=\"card-heading\">.*<\/p><\/div><\/div>/';
        preg_match_all($pattern, $data_from_blizzard, $matches, PREG_OFFSET_CAPTURE, 3);
        $section_data = explode('svg>' , $matches[0][0][0]);
        $summary_data = $this->getUserSummary($section_data, $data_from_blizzard);

        $this->RemoveResouce($data_from_blizzard);
        $this->RemoveResouce($section_data);
        return $summary_data;
    }

    /**
     * get summary from parssing data
     * @access private
     * @param   String  $section_data           : Partial data from blizzard
     * @param   String  $data_from_blizzard     : data from blizzard
     * @return  String  $summary                : summary user_summary_data data
     */
    private function getUserSummary($section_data, $data_from_blizzard) {
      $level_img = "";
      $icon = "";
      $level = "";
      $com_grade = "";

      preg_match('/https:\/\/blzgdapipro-a.akamaihd.net\/game\/playerlevelrewards\/0x0250000000000[A-Za-z0-9]+_Rank.png/',
                  $data_from_blizzard, $level_img);
      preg_match('/src=".*.png" class/', $data_from_blizzard, $icon);
      preg_match('/<div class=\"u-vertical-center\">[0-9]{1,10}<\/div>/', $data_from_blizzard, $level);
      preg_match('/<div class=\"u-align-center h6\">[0-9]{1,10}<\/div>/', $data_from_blizzard, $com_grade);

      $icon = str_replace('src="', "", $icon);
      $icon = str_replace('" class', "", $icon);
      $avg_solo_kill = explode('상위' , $section_data[7]);
      $summary =
          (array(
                ':icon' => $icon[0],
                ':level' => strip_tags($level[0]),
                ':com_grade' => strip_tags($com_grade[0]),
                ':user_name' => USERNAME,
                ':avg_kill' => str_replace("처치 - 평균", "", strip_tags($section_data[0])),
                ':avg_damage' => str_replace("준 피해 - 평균", "", strip_tags($section_data[1])),
                ':avg_death' => str_replace("죽음 - 평균", "", strip_tags($section_data[2])),
                ':avg_Murderous' => str_replace("결정타 - 평균", "", strip_tags($section_data[3])),
                ':avg_heal' => str_replace("치유 - 평균", "", strip_tags($section_data[4])),
                ':avg_contributions_kill' => str_replace("임무 기여 처치 - 평균", "", strip_tags($section_data[5])),
                ':avg_contributions_time' => str_replace("임무 기여 시간 - 평균", "", strip_tags($section_data[6])),
                ':avg_solo_kill' => str_replace("단독 처치 - 평균", "", strip_tags($avg_solo_kill[0])),
                ':level_img' => $level_img[0],
                ':analy' => $this->GetUserAnaly(str_replace("준 피해 - 평균", "", strip_tags($section_data[1])),
                            str_replace("임무 기여 처치 - 평균", "", strip_tags($section_data[5])),
                            str_replace("임무 기여 시간 - 평균", "", strip_tags($section_data[6]))),
          ));
      return $summary;
    }

    /**
     * Analyze based on user summary data
     * @access private
     * @param   Integer  $avg_damage                : average damage
     * @param   Integer  $avg_contributions_kill    : average contributions_kill
     * @param   Integer  $avg_contributions_time    : average contributions_time
     * @return  String   $analy                     : Analyze data
     */
    private function GetUserAnaly($avg_damage = 1, $avg_contributions_kill = 1, $avg_contributions_time = 1) {
        $avg_damage = str_replace(",", "", $avg_damage);
        $avg_damage = (int) $avg_damage;
        $temp = explode(":", $avg_contributions_time);
        $minuts = (int) $temp[0];
        $minuts = ($minuts * 60);
        $sec = (int) $temp[1];
        $avg_contributions_time = ($minuts + $sec);

        //damage)
        switch ($avg_damage) {
            case  $avg_damage<= 3000:
                $analy[0] = 1;
                break;
            case  $avg_damage > 3000 && $avg_damage <= 6000:
                $analy[0] = 2;
                break;
            case  $avg_damage > 6000 && $avg_damage <= 9000:
                $analy[0] = 3;
                break;
            case  $avg_damage > 9000 && $avg_damage <= 12000:
                $analy[0] = 4;
                break;
            case  $avg_damage > 12000:
                $analy[0] = 5;
                break;
        }

        //avg_contributions_kill
        switch ($avg_contributions_kill) {
            case  $avg_contributions_kill <= 5:
                $analy[1] = 1;
                break;
            case  $avg_contributions_kill > 5 && $avg_contributions_kill <= 10:
                $analy[1] = 2;
                break;
            case  $avg_contributions_kill > 10 && $avg_contributions_kill <= 15:
                $analy[1] = 3;
                break;
            case  $avg_contributions_kill > 15 && $avg_contributions_kill <= 20:
                $analy[1] = 4;
                break;
            case  $avg_contributions_kill > 20:
                $analy[1] = 5;
                break;
        }

        //avg_contributions_time
        switch ($avg_contributions_time) {
            case  $avg_contributions_time <= 10:
                $analy[2] = 1;
                break;
            case  $avg_contributions_time > 10 && $avg_contributions_time <= 30:
                $analy[2] = 2;
                break;
            case  $avg_contributions_time > 30 && $avg_contributions_time <= 60:
                $analy[2] = 3;
                break;
            case  $avg_contributions_time > 60 && $avg_contributions_time <= 120:
                $analy[2] = 4;
                break;
            case  $avg_contributions_time > 120:
                $analy[2] = 5;
                break;
        }

        $analy = floor(($analy[0]+$analy[1]+$analy[2])/3);
        $grade = [
            "nodata",
            "bug",
            "slave",
            "human",
            "nobility",
            "god",
        ];
        return $grade[($analy)];
    }
}
