<?php
namespace Nrwtaylor\StackAgentThing;

class Annualweather extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function loadClimateDataHourly()
    {
        $path = "climate-data-gcca-yvr";

        // This is the longest hourly dataset in British Columbia.
        // Dating back to the 1950s when records began at YVR Vancouver Airport.
        //        $path = "climate-data-gcca-yvr";
        //$path = "";
        $number = "1108395";
        //$number = "1108447";
        $place = "BC";

        // Another place and another number for testing against.
        //        $place = "NT";
        //        $number = "2204100";
        //        $period = "daily";
        $period = "hourly";
        $static_resource =
            "en_climate_" . $period . "_" . $place . "_" . $number . "_";

        //        $static_resource =
        //            "en_climate_daily_BC_1108447_";

        //en_climate_daily_BC_1108447_1939_P1D.csv
        if ($period == "daily") {
            $static_postfix = "_P1D.csv";
        }

        if ($period == "hourly") {
            $static_postfix = "_P1H.csv";
        }
var_dump("Start");
        // 12-2022_P1H.csv
        $contents = "";
        $librex_agent = new Librex($this->thing, $contents);
        $index = 0;
        $years = range(1937, 2022);
        $months = range(1, 12);
        $climate_data_points = [];
        foreach ($years as $i => $year) {
            foreach ($months as $j => $month) {
                $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);
                $index += 1;
                $resource =
                    $static_resource .
                    $padded_month .
                    "-" .
                    $year .
                    $static_postfix;
                $this->thing->console("Loading file.");
                $file = $this->resource_path . $path . "/" . $resource;
                //            $file = "/tmp/" . $path . "/" . $resource;
                //est-coldsnap-days.csvvar_dump($file);
                //exit();
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

                        /*

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
*/
                        $climate_array = $this->parseCsv($line, $field_names);
                        //
                        //var_dump($climate_array);
                        //exit();
                        $climate_data_points[] = $climate_array;
                        //array_merge($climate_data_points, $climate_array);
                    }
                } else {
                    //                    echo "Does not exist.";
                }
            }
        }

        //var_dump($climate_data_points);

        var_dump("Hourly data loaded");
        $this->climate_data_points = $climate_data_points;

        //$day_points = [];
        //$points = [];
        $coldSnapLengthDays = 0;
        $count = count($climate_data_points);
        //var_dump($count);
        //exit();

        //$p = [];
        $processed_points = [];

        foreach ($climate_data_points as $i => $j) {
            //var_dump($i);
            //var_dump($j);
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

            $temperature = floatval(trim($j["Temp (°C)"], "\""));
            //            $temperatureMin = floatval(trim($j["Min Temp (°C)"], "\""));
            //            $centimetresSnow = floatval(trim($j["Snow on Grnd (cm)"], "\""));
            //echo $temperatureMax ." " .  $j["Max Temp (°C)"] . " / " . $temperatureMin . " " . $j["Min Temp (°C)"] . "\n";
            //             $day_points[$days] = $j;
            //            echo intval(($i / $count) * 100) . " " . $days . "\n";
//            if ($j["Temp (°C)"] == '""') {
//                continue;
//            }

            if (!isset($processed_points[$days])) {
                $processed_points[$days] = [];
            }

            if (!isset($processed_points[$days][$year])) {
                $processed_points[$days][$year] = [];
            }
            //            var_dump($days, $year);

            //if ($temperature == null) {continue;}

            //            array_push($processed_points[$days][$year], $temperature);
            $processed_points[$days][$year][] = $temperature;

            //var_dump($processed_points[$days][$year]);
        }

        var_dump("Hourly data points processed");
        foreach (range(0, 366) as $day) {
//            $day_line = "";
            $day_line_min = "";
            $day_line_max = "";

            foreach ($years as $year) {
if (isset($processed_points[$day][$year])) {continue;}
var_dump("day year", $day, $year, $processed_points[$day][$year]);
                $minMax = maxMinData($processed_points[$day][$year]);

                //var_dump($day, $year);
                //var_dump($processed_points[$day][$year]);
                //$day_line = $day_line . "," . $processed_points[$day][$year];

                $day_line_min = $day_line_min . "," . $minMax["min"];
                $day_line_max = $day_line_max . "," . $minMax["max"];
            }

            //$day_line = $day_line . "\n";

            $day_line_min = $day_line_min . "\n";
            $day_line_max = $day_line_max . "\n";
            echo $day_line_min;

            //echo $day_line;

            file_put_contents(
                "/tmp/test4-annualweather-min.csv",
                $day_line_min,
                FILE_APPEND | LOCK_EX
            );

            file_put_contents(
                "/tmp/test4-annualweather-max.csv",
                $day_line_max,
                FILE_APPEND | LOCK_EX
            );
        }
        exit();
        return $processed_points;
    }

    public function maxMinData($arr)
    {
        var_dump($arr);

        foreach ($arr as $x) {
            if (!isset($min)) {
                $min = $x;
            }
            if (!isset($min)) {
                $min = $x;
            }

            if ($x < $min) {
                $min = $x;
            }
            if ($y > $max) {
                $max = $x;
            }

            return ["min" => $min, "max" => $max];
        }
    }

    function loadClimateDataDaily()
    {
        $path = "climate-data-gcca-yvr";

        // This is the longest hourly dataset in British Columbia.
        // Dating back to the 1950s when records began at YVR Vancouver Airport.
        //        $path = "climate-data-gcca-yvr";
        //$path = "";
        //$number = "1108395";
        $number = "1108447";
        $place = "BC";

        // Another place and another number for testing against.
        //        $place = "NT";
        //        $number = "2204100";
        //        $period = "daily";
        $period = "daily";
        //        $static_resource =
        //            "en_climate_" . $period . "_" . $place . "_" . $number . "_";

        $static_resource = "en_climate_daily_BC_1108447_";

        //en_climate_daily_BC_1108447_1939_P1D.csv
        if ($period == "daily") {
            $static_postfix = "_P1D.csv";
        }

        if ($period == "hourly") {
            $static_postfix = "_P1H.csv";
        }

        // 12-2022_P1H.csv
        $contents = "";
        $librex_agent = new Librex($this->thing, $contents);
        $index = 0;
        $years = range(1937, 2022);
        $months = range(1, 12);
        $climate_data_points = [];
        foreach ($years as $i => $year) {
            //      foreach ($months as $j => $month) {
            //        $padded_month = str_pad($month, 2, "0", STR_PAD_LEFT);
            $index += 1;
            $resource =
                $static_resource .
                //          $padded_month .
                //        "-" .
                $year .
                $static_postfix;
            $this->thing->console("Loading file.");
            $file = $this->resource_path . $path . "/" . $resource;
            //            $file = "/tmp/" . $path . "/" . $resource;
            //est-coldsnap-days.csvvar_dump($file);
            //exit();
            if (file_exists($file)) {
                //echo $file . "\n";
                $contents = file_get_contents($file);
                $librex_agent->getLibrex("unnamed-" . $index, $contents);

                $lines = $librex_agent->linesLibrex();

                foreach ($lines as $i => $line) {
                    //echo $line. "\n";
                    // Transform weather record CSV headers to PHP object.
                    //"Longitude (x)","Latitude (y)","Station Name","Climate ID","Date/Time","Year","Month","Day","Data Quality","Max Temp (°C)","Max Temp Flag","Min Temp (°C)","Min Temp Flag","Mean Temp (°C)",>

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
                    //
                    //var_dump($climate_array);
                    //exit();
                    $climate_data_points[] = $climate_array;
                    //array_merge($climate_data_points, $climate_array);
                }
            } else {
                //                    echo "Does not exist.";
                //  }
            }
        }

        //var_dump($climate_data_points);

        //        var_dump("Data loaded");
        $this->climate_data_points = $climate_data_points;

        //$day_points = [];
        //$points = [];
        $coldSnapLengthDays = 0;
        $count = count($climate_data_points);
        //var_dump($count);
        //exit();

        //$p = [];
        $processed_points = [];

        foreach ($climate_data_points as $i => $j) {
            //var_dump($i);
            //var_dump($j);
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

            //            $temperature = floatval(trim($j["Temp (°C)"], "\""));
            $temperatureMin = floatval(trim($j["Min Temp (°C)"], "\""));
            $temperatureMax = floatval(trim($j["Max Temp (°C)"], "\""));

            //            $centimetresSnow = floatval(trim($j["Snow on Grnd (cm)"], "\""));
            //echo $temperatureMax ." " .  $j["Max Temp (°C)"] . " / " . $temperatureMin . " " . $j["Min Temp (°C)"] . "\n";
            //             $day_points[$days] = $j;
            //            echo intval(($i / $count) * 100) . " " . $days . "\n";

            if ($j["Min Temp (°C)"] == '""') {
                continue;
            }

            if ($j["Max Temp (°C)"] == '""') {
                continue;
            }

            if (!isset($processed_points[$days])) {
                $processed_points[$days] = [];
            }

            if (!isset($processed_points[$days][$year])) {
                $processed_points[$days][$year] = [];
            }
            //            var_dump($days, $year);

            //if ($temperature == null) {continue;}

            //            array_push($processed_points[$days][$year], $temperature);
            $processed_points[$days][$year] = [
                "minimum" => $temperatureMin,
                "maximum" => $temperatureMax,
            ];

            //var_dump($processed_points[$days][$year]);
        }

        var_dump("points processed");
        // Make headers

        foreach ($years as $year) {
            //var_dump($day, $year);
            //var_dump($processed_points[$day][$year]);
            $day_line =
                $day_line .
                "" .
                "minimum " .
                $year .
                "," .
                "maximum " .
                $year .
                ",";
        }

        $day_line = $day_line . "\n";

        echo $day_line;

        file_put_contents(
            "/tmp/test2-daily-annualweather.csv",
            $day_line,
            FILE_APPEND | LOCK_EX
        );

        //}

        foreach (range(0, 366) as $day) {
            $day_line = "";

            foreach ($years as $year) {
                //var_dump($day, $year);
                //var_dump($processed_points[$day][$year]);
                $day_line =
                    $day_line .
                    "" .
                    $processed_points[$day][$year]["minimum"] .
                    "," .
                    $processed_points[$day][$year]["maximum"] .
                    ",";
            }

            $day_line = $day_line . "\n";

            echo $day_line;

            file_put_contents(
                "/tmp/test2-daily-annualweather.csv",
                $day_line,
                FILE_APPEND | LOCK_EX
            );
        }
        exit();
        return $processed_points;
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
echo "doClimateData start";
        $this->loadClimateDataHourly();
echo "doClimateData loadClimateDataHourly";
exit();
        //$this->loadClimateDataDaily();
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
