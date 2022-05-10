<?php
namespace Nrwtaylor\StackAgentThing;

use Overtrue\ChineseCalendar\Calendar;

class Chinesecalendar extends Agent
{
    public $var = "hello";

    function init()
    {
        //dev

        date_default_timezone_set("PRC");

        $this->chinese_calendar = new \Overtrue\ChineseCalendar\Calendar();

        //$result = $calendar->solar(2017, 5, 5); // 阳历
        //$result = $calendar->lunar(2017, 4, 10); // 阴历
        //$result = $calendar->solar(2017, 5, 5, 23) // 阳历，带 $hour 参数
    }

    function run()
    {
        $this->doChineseCalendar();
    }

    public function doChineseCalendar()
    {
        $year = $this->parsed_date["year"];
        $month = $this->parsed_date["month"];
        $day = $this->parsed_date["day"];

        $gregorian_text = "gregorian " . $year . " " . $month . " " . $day;
        if (isset($this->timestamp)) {
            $gregorian_text .= " " . $this->timestamp;
        }
        $result = $this->chinese_calendar->solar($year, $month, $day); // 阳历

        /*
array(32) {
  ["lunar_year"]=>
  string(4) "2019"
  ["lunar_month"]=>
  string(2) "04"
  ["lunar_day"]=>
  string(2) "16"
  ["lunar_hour"]=>
  NULL
  ["lunar_year_chinese"]=>
  string(12) "二零一九"
  ["lunar_month_chinese"]=>
  string(6) "四月"
  ["lunar_day_chinese"]=>
  string(6) "十六"
  ["lunar_hour_chinese"]=>
  NULL
  ["ganzhi_year"]=>
  string(6) "己亥"
  ["ganzhi_month"]=>
  string(6) "己巳"
  ["ganzhi_day"]=>
  string(6) "丁巳"
  ["ganzhi_hour"]=>
  NULL
  ["wuxing_year"]=>
  string(6) "土水"
  ["wuxing_month"]=>
  string(6) "土火"
  ["wuxing_day"]=>
  string(6) "火火"
  ["wuxing_hour"]=>
  NULL
  ["color_year"]=>
  string(3) "黄"
  ["color_month"]=>
  string(3) "黄"
  ["color_day"]=>
  string(3) "红"
  ["color_hour"]=>
  NULL
  ["animal"]=>
  string(3) "猪"
  ["term"]=>
  NULL
  ["is_leap"]=>
  bool(false)
  ["gregorian_year"]=>
  string(4) "2019"
  ["gregorian_month"]=>
  string(2) "05"
  ["gregorian_day"]=>
  string(2) "20"
  ["gregorian_hour"]=>
  NULL
  ["week_no"]=>
  int(1)
  ["week_name"]=>
  string(9) "星期一"
  ["is_today"]=>
  bool(false)
  ["constellation"]=>
  string(6) "金牛"
  ["is_same_year"]=>
  bool(true)
}
*/

        $lunar_text =
            "lunar year/month/day " .
            $result["lunar_year"] .
            " " .
            $result["lunar_month"] .
            " " .
            $result["lunar_day"];
        $lunar_chinese_text =
            $result["lunar_year_chinese"] .
            " " .
            $result["lunar_month_chinese"] .
            " " .
            $result["lunar_day_chinese"];
        $animal_text = "animal " . $result["animal"];
        $animal_english_text = $this->wordChinese($result["animal"]);

        $chinese_calendar_date_text =
            $lunar_text .
            " " .
            $lunar_chinese_text .
            " " .
            $animal_text .
            " " .
            $animal_english_text .
            " [" .
            $gregorian_text .
            "]";

        if ($this->agent_input == null) {
            $response = "CHINESE CALENDAR | " . $chinese_calendar_date_text;

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function deprecate_computeEaster($year = null)
    {
        /*
Tip: The date of Easter Day is defined as the Sunday after the first full moon which falls on or after the Spring Equinox (21st March).
https://www.w3schools.com/php/func_cal_easter_days.asp
https://www.php.net/manual/en/function.easter-date.php
*/
        $day_count = easter_date($year);

        $datum = new \DateTime();
        $datum->setTimestamp($day_count);

        $t = $this->timestampTime($datum);

        $text = $datum->format("j F Y");

        return $text;
    }

    function makeSMS()
    {
        $this->node_list = ["easter" => ["easter"]];
        $this->sms_message = "" . $this->message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks("easter");
        $this->thing_report["choices"] = $choices;
    }

    public function scoreChinesecalendar($text)
    {
        if (strpos("chinese calendar", $text) !== false) {
            $this->score = 10;
            return;
        }

        if (stripos("chinesecalendar", $word) !== false) {
            $this->score = 10;
            return;
        }

        if (stripos("calendar chinese", $word) !== false) {
            $this->score = 10;
            return;
        }

        if (stripos("calendarchinese", $word) !== false) {
            $this->score = 10;
            return;
        }
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->scoreChinesecalendar($input);
        $filtered_input = strtolower($input);
        $filtered_text = str_replace("chinese calendar", "", $filtered_input);
        $filtered_text = str_replace("chinesecalendar", "", $filtered_input);

        $parsed_date = date_parse($filtered_input);

        if (
            $parsed_date["year"] == false or
            $parsed_date["month"] == false or
            $parsed_date["day"] == false
        ) {
            // $this->timestamp = $this->humanTime();
            $this->timestamp = date("c", time());
            //$timestamp = time();
            $parsed_date = date_parse($this->timestamp);
        }

        $this->parsed_date = $parsed_date;
        return;
    }
}
