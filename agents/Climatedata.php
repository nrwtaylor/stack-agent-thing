<?php
namespace Nrwtaylor\StackAgentThing;

class ClimateData extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function loadClimateData()
    {
        //    $path = "climate-data-gcca-yvr";

        $path = "climate-data-gcca-yvr";
        $number = "1108395";
        $place = "BC";
//        $place = "NT";
//        $number = "2204100";
        $static_resource = "en_climate_hourly_" . $place . "_" . $number . "_";
        $static_postfix = "_P1H.csv";
        // 12-2022_P1H.csv

        $librex_agent = new Librex($this->thing, $contents);

        $years = range(1950, 2022);
        $months = range(0, 12);
        $climate_data_points = [];
        foreach ($years as $i => $year) {
            foreach ($months as $j => $month) {
                $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);

                $resource =
                    $static_resource .
                    $padded_month .
                    "-" .
                    $year .
                    $static_postfix;

                $file = $this->resource_path . $path . "/" . $resource;
                if (file_exists($file)) {
                    echo "exists";
                    $contents = file_get_contents($file);

                    $librex_agent->getLibrex("unnamed", $contents);

                    //$contents = $librex_agent->contents;
                    $lines = $librex_agent->linesLibrex();
                    foreach ($lines as $i => $line) {
                        // ﻿"Longitude (x)","Latitude (y)","Station Name","Climate ID","Date/Time (LST)","Year","Month","Day","Time (LST)","Temp (°C)","Temp Flag","Dew Point Temp (°C)","Dew Point Temp Flag","Rel Hum (%)","Rel Hum Flag","Wind Dir (10s deg)","Wind Dir Flag","Wind Spd (km/h)","Wind Spd Flag","Visibility (km)","Visibility Flag","Stn Press (kPa)","Stn Press Flag","Hmdx","Hmdx Flag","Wind Chill","Wind Chill Flag","Weather""
                        $field_names = [
                            "Longitude (x)",
                            "Latitude (y)",
                            "Station Name",
                            "Climate ID",
                            "Date/Time (LST)",
                            "Year",
                            "Month",
                            "Day",
                            "Time (LST)",
                            "Temp (°C)",
                            "Temp Flag",
                            "Dew Point Temp (°C)",
                            "Dew Point Temp Flag",
                            "Rel Hum (%)",
                            "Rel Hum Flag",
                            "Wind Dir (10s deg)",
                            "Wind Dir Flag",
                            "Wind Spd (km/h)",
                            "Wind Spd Flag",
                            "Visibility (km)",
                            "Visibility Flag",
                            "Stn Press (kPa)",
                            "Stn Press Flag",
                            "Hmdx",
                            "Hmdx Flag",
                            "Wind Chill",
                            "Wind Chill Flag",
                            "Weather",
                        ];

                        $climate_array = $this->parseCsv($line, $field_names);
                        $climate_data_points[] = $climate_array;
                    }
                } else {
                    //                    echo "Does not exist.";
                }
            }
        }

        $this->climate_data_points = $climate_data_points;

        $time_stamps = [
            "00:00",
            "01:00",
            "02:00",
            "03:00",
            "04:00",
            "05:00",
            "06:00",
            "07:00",
            "08:00",
            "09:00",
            "10:00",
            "11:00",
            "12:00",
            "13:00",
            "14:00",
            "15:00",
            "16:00",
            "17:00",
            "18:00",
            "19:00",
            "20:00",
            "21:00",
            "22:00",
            "23:00",
            "24:00",
        ];

        $arr_handler = new Arr($this->thing, "arr");

        $days = range(0, 31);

        // Test
        //$months = range(1, 1);
        //$days = range(5, 6);
        //$time_stamps = ["05:00"];

        $point_histories = [];

        $day_count = 0;
        foreach ($months as $i => $month) {
            foreach ($days as $j => $day) {
                foreach ($time_stamps as $k => $time_stamp) {

                    $day_count += 1;

                    $padded_day = str_pad($day, 2, "0", STR_PAD_LEFT);
                    $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);

                    $filter = [
                        "month" => $padded_month,
                        "day" => $padded_day,
                        "time_stamp" => $time_stamp,
                    ];

                    $filtered_data_points = $arr_handler->filterFieldsArr(
                        $this->climate_data_points,
                        $filter
                    );
                    $point_label = $path . "-" . implode("-",$filter);
                    $point_histories[$point_label] = $filtered_data_points;
                }
                var_dump($day_count);
            }
            var_dump("month",$day_count);
        }

        $meta = ["path" => $path];
$climate_data_array = ['meta'=>$meta,'data'=>$point_histories];

$output_file_name = "/tmp/" . $path . "_time_sequence.json";

        file_put_contents($output_file_name, json_encode($climate_data_array)
        );

        $data = json_decode(
            file_get_contents($output_file_name),
            true
        );


        var_dump($data['meta']);
        //var_dump($new_variable[1]); // Not sure why a 0 and 1 index.

        return $climate_data_points;
    }

    public function filterArr($climate_data_points, $filter_array = null)
    {
        $filtered_data_points = array_filter($climate_data_points, function (
            $value
        ) use ($filter_array) {
            $filter_month = $filter_array["month"];
            $filter_day = $filter_array["day"];

            $month = str_replace('"', "", $value["Month"]);
            $day = str_replace('"', "", $value["Day"]);
            $time_stamp = str_replace('"', "", $value["Time (LST)"]);

            $filter_time_stamp = $filter_array["time_stamp"];

            return $month == $filter_month and
                $day == $filter_day and
                $time_stamp == $filter_time_stamp;
        });

        /*
    ["Year"]=>
    string(6) ""1950""
    ["Month"]=>
    string(4) ""10""
    ["Day"]=>
    string(4) ""28""
*/
        return $filtered_data_points;
    }

    function run()
    {
        $this->doClimateData();
    }

    public function doClimateData()
    {
        $this->loadClimateData();
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CLIMATE DATA | " . strtolower($v) . ".";

            $this->cat_message = $response; // mewsage?
        } else {
            $this->cat_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------
    /*
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }
*/
    function makeSMS()
    {
        $this->node_list = ["cat" => ["cat", "dog"]];
        $this->sms_message = "" . $this->cat_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks("cat");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
