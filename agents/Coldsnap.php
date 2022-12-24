<?php
namespace Nrwtaylor\StackAgentThing;

class ColdSnap extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function loadClimateData()
    {
        //    $path = "climate-data-gcca-yvr";

        // This is the longest hourly dataset in British Columbia.
        // Dating back to the 1950s when records began at YVR Vancouver Airport.
        $path = "climate-data-gcca-yvr";
        //$number = "1108395";
        $number = "1108447";
        $place = "BC";

        // Another place and another number for testing against.
        //        $place = "NT";
        //        $number = "2204100";
        $period = "daily";

        $static_resource =
            "en_climate_" . $period . "_" . $place . "_" . $number . "_";
        $static_postfix = "_P1D.csv";
        // 12-2022_P1H.csv
        $contents = "";
        $librex_agent = new Librex($this->thing, $contents);
        $index = 0;
        $years = range(1950, 2022);
        $months = range(1, 12);
        $climate_data_points = [];
        foreach ($years as $i => $year) {
            //            foreach ($months as $j => $month) {
            //                $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);
            $index += 1;
            $resource =
                $static_resource .
                //                    $padded_month .
                //                    "-" .
                $year .
                $static_postfix;
            $this->thing->console("Loading file.");
            $file = $this->resource_path . $path . "/" . $resource;
            if (file_exists($file)) {
                //echo $file . "\n";
                $contents = file_get_contents($file);
                $librex_agent->getLibrex("unnamed-" . $index, $contents);

                $lines = $librex_agent->linesLibrex();

                foreach ($lines as $i => $line) {
                    //echo $line. "\n";
                    // Transform weather record CSV headers to PHP object.
                    //"Longitude (x)","Latitude (y)","Station Name","Climate ID","Date/Time","Year","Month","Day","Data Quality","Max Temp (°C)","Max Temp Flag","Min Temp (°C)","Min Temp Flag","Mean Temp (°C)","Mean Temp Flag","Heat Deg Days (°C)","Heat Deg Days Flag","Cool Deg Days (°C)","Cool Deg Days Flag","Total Rain (mm)","Total Rain Flag","Total Snow (cm)","Total Snow Flag","Total Precip (mm)","Total Precip Flag","Snow on Grnd (cm)","Snow on Grnd Flag","Dir of Max Gust (10s deg)","Dir of Max Gust Flag","Spd of Max Gust (km/h)","Spd of Max Gust Flag"

                    $field_names = [
                        "Longitude (x)",
                        "Latitude (y)",
                        "Station Name",
                        "Climate ID",
                        "Date/Time",
                        "Year",
                        "Month",
                        "Day",
                        "Data Quality",
                        "Max Temp (°C)",
                        "Max Temp Flag",
                        "Min Temp (°C)",
                        "Min Temp Flag",
                        "Mean Temp (°C)",
                        "Mean Temp Flag",
                        "Heat Deg Days (°C)",
                        "Heat Deg Days Flag",
                        "Cool Deg Days (°C)",
                        "Cool Deg Days Flag",
                        "Total Rain (mm)",
                        "Total Rain Flag",
                        "Total Snow (cm)",
                        "Total Snow Flag",
                        "Total Precip (mm)",
                        "Total Precip Flag",
                        "Snow on Grnd (cm)",
                        "Snow on Grnd Flag",
                        "Dir of Max Gust (10s deg)",
                        "Dir of Max Gust Flag",
                        "Spd of Max Gust (km/h)",
                        "Spd of Max Gust Flag",
                    ];

                    $climate_array = $this->parseCsv($line, $field_names);
                    $climate_data_points[] = $climate_array;
                }
            } else {
                //                    echo "Does not exist.";
            }
            //           }
        }
        var_dump("loaded");
        $this->climate_data_points = $climate_data_points;

        //$day_points = [];
        //$points = [];
$coldSnapLengthDays = 0;
        $count = count($climate_data_points);
        foreach ($climate_data_points as $i => $j) {
            //var_dump($j);
            $year = intval(trim($j["Year"], "\""));
            $month = intval(trim($j["Month"], "\""));
            $day = intval(trim($j["Day"], "\""));
            //var_dump($year, $month, $day);

            if ($year === 0) {
                continue;
            }
            if ($month === 0) {
                continue;
            }
            if ($day === 0) {
                continue;
            }
            //var_dump($year);
            //exit();
            $datetime1 = date_create("01-01-" . $year);
            $datetime2 = date_create($day . "-" . $month . "-" . $year);
            $interval = date_diff($datetime1, $datetime2);

            $days = intval($interval->format("%R%a"));

            //       $days = 0;

//            $prior3DayTemperatureMax = $prior2DayTemperatureMax;
//            $prior3DayTemperatureMin = $prior2DayTemperatureMin;
//            $prior3DayCentimetresSnow = $prior2DayCentimetresSnow;


//            $prior2DayTemperatureMax = $priorDayTemperatureMax;
//            $prior2DayTemperatureMin = $priorDayTemperatureMin;
//            $prior2DayCentimetresSnow = $priorDayCentimetresSnow;


            $priorDayTemperatureMax = $temperatureMax;
            $priorDayTemperatureMin = $temperatureMin;
            $priorDayCentimetresSnow = $centimetresSnow;

            $temperatureMax = floatval(trim($j["Max Temp (°C)"], "\""));
            $temperatureMin = floatval(trim($j["Min Temp (°C)"], "\""));
            $centimetresSnow = floatval(trim($j["Snow on Grnd (cm)"], "\""));
//echo $temperatureMax ." " .  $j["Max Temp (°C)"] . " / " . $temperatureMin . " " . $j["Min Temp (°C)"] . "\n";
            //             $day_points[$days] = $j;
//            echo intval(($i / $count) * 100) . " " . $days . "\n";


            if ( ($j["Max Temp (°C)"] == '""') and  ($j["Min Temp (°C)"] == '""')) {
continue;
}


//            $t = $day . "," . implode(",", $j)  . "\r\n";
            //echo $t;




    //        echo $t . "\r\n";


            $is2dayColdSnap =
                ($priorDayTemperatureMax <= 0.0 and
                $priorDayTemperatureMin <= -5.0 and
                $temperatureMax <= 0.0 and
                $temperatureMin <= -5.0);

echo $days . " " .($is2dayColdSnap ? "Y" :"N"). "/" . ($is3dayColdSnap ? "Y" :"N") . "/" . ($is4dayColdSnap ? "Y":"N") . "  " . $temperatureMin. " ". $temperatureMax . " --- ".
$priorDayTemperatureMin ." " . $priorDayTemperatureMax ." --- " . "\n";

if ($is2dayColdSnap) {

$coldSnapLengthDays +=1;

}


            $t = $days . "," . $coldSnapLengthDays. ",". implode(",", $j)  . "\r\n";


//if (false) {
            if (!$is2dayColdSnap and $coldSnapLengthDays > 0) {
                file_put_contents(
                    //                "/tmp/climatedata-yvr-day-count-" . $days . ".csv",
                    "/tmp/coldsnap-days.csv",
                    $t,
                    FILE_APPEND | LOCK_EX
                );
                $coldSnapLengthDays = 0;
            }

            //}
        }
        exit();
        $this->day_points = $day_points;
        return $this->day_points;
        exit();
        //var_dump($d);
        //exit();

        return;
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

        $days = range(1, 31);

        // Test
        //$months = range(1, 1);
        //$days = range(5, 6);
        //$time_stamps = ["05:00"];

        $point_histories = [];

        $day_count = 0;
        $month_count = 0;
        foreach ($months as $i => $month) {
            foreach ($days as $j => $day) {
                // Get all the records for a particular day count.
                // Days since start of year.
                // Save day count as line in file for that day.
                $day_count += 1;
                foreach ($time_stamps as $k => $time_stamp) {
                    //                    $day_count += 1;
                    $padded_day = str_pad($day, 2, "0", STR_PAD_LEFT);
                    $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);

                    $filter = [
                        "month" => $padded_month,
                        "day" => $padded_day,
                        "time_stamp" => $time_stamp,
                    ];

                    //var_dump($this->climate_data_points);
                    //echo "\n";
                    //var_dump("ClimateData filter", $filter);
                    //                    $filtered_data_points = $arr_handler->filterFieldsArr(
                    //                        $this->climate_data_points,
                    //                        $filter
                    //                    );

                    //var_dump($this->climate_data_points);
                    //var_dump($filtered_data_points);
                    //var_dump($filter);
                    //                    $point_label = $path . "-" . implode("-",$filter);

                    //                    $point_history = ['event'=>$filtered_data_points, "eventAt"=>implode("-", $filter)];

                    // There is not enough memory space (by PHP default) to deal with a variable of point_histories.
                    // Running this code on my development machine has the processed killed by about month 12.
                    //           $point_histories[] = $point_history;
                    //$year = $point_history['event']["Year"];
                    //var_dump($year);
                    // So instead serialize the processed rows and save them as line rows in a working file.
                    //$serialized_point_history = json_encode($point_history) . "\r\n";

                    // Per month-day. All recorded hours.
                    //file_put_contents("/tmp/test5-". $month ."-" .$day .".txt", $serialized_point_history, FILE_APPEND | LOCK_EX);

                    //foreach($filtered_data_points as $z => $filtered_data_point) {
                    $t = implode(",", $filtered_data_point) . "\r\n";

                    //$serialized_point_history = json_encode($point_history) . "\r\n";
                    //file_put_contents("/tmp/climatedata-yvr-day-count-". $day_count .".jsonArray", $serialized_point_history, FILE_APPEND | LOCK_EX);
                    file_put_contents(
                        "/tmp/climatedata-yvr-day-count-" . $day_count . ".csv",
                        $t,
                        FILE_APPEND | LOCK_EX
                    );
                    //}

                    // Per (year) day. All recorded hours.

                    //file_put_contents("/tmp/test5-". $year_start_day . ".txt", $serialized_point_history, FILE_APPEND | LOCK_EX);

                    // Resulting in an 8 Byte increase per (9x25)? records.
                    // More manageable for server memory.
                }
                echo "day count " . $day_count . "\n";
                echo memory_get_usage() . " " . "Bytes ";
            }
            // Reset day count
            //$day_count = 0;
            $month_count += 1;
            echo "Processed month " . $month_count;
        }

        //        $meta = ["path" => $path];

        $climate_data_array = [
            "from" => "climatedata",
            "to" => "merp",
            "subject" => $path,
            "agent_input" => $point_histories,
        ];

        $output_file_name = "/tmp/" . $path . "_time_sequence.json";

        file_put_contents($output_file_name, json_encode($climate_data_array));

        $data = json_decode(file_get_contents($output_file_name), true);

        var_dump($data["subject"]);
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
            //$array = ["miao", "miaou", "hiss", "prrr", "grrr"];
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
