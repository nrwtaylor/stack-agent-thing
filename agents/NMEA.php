<?php
/**
 * NMEA.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// http://aprs.gids.nl/nmea/
// https://www.tronico.fi/OH6NT/docs/NMEA0183.pdf
// Not for navigation.
// Experimental.

// dev https://opencpn.org/flyspray/index.php?do=details&task_id=2554

class NMEA extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = [
            "nmea" => ["kplex", "seatalk", "nmea2000", "opencpn"],
        ];
        $this->colour_indicators = ["red", "green"];
    }

    function get()
    {
        // Take a look at this thing for IChing variables.

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "nmea",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["nmea", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->nmea = $this->thing->json->readVariable(["nmea", "nmea"]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->json->writeVariable(["nmea", "nmea"], $this->nmea);
    }

    public function talkeridNMEA($text)
    {
        return $this->parseNMEA($text)["talker_id"];
    }

    public function sentenceNMEA($text)
    {
        return $this->parseNMEA($text)["sentence"];
    }

    public function parseNMEA($text)
    {
        if ($text === null) {
            return true;
        }
        if ($text == "null") {
            return true;
        }

        $parts = $this->explodeNMEA($text);
        if ($parts === true) {
            return true;
        } // error state

        $sentence_identifier = $parts[0];
        $nmea_talker_id = substr($sentence_identifier, 1, 2);
        $nmea_sentence = strtolower(substr($sentence_identifier, 3, 3));

        $nmea_array = $this->{$nmea_sentence . "NMEA"}($text);

        if ($nmea_array === false) {
            $nmea_array["recognized_sentence"] = "N";
        } else {
            $nmea_array["recognized_sentence"] = "Y";
        }
        $nmea_array["sentence_identifier"] = $sentence_identifier;
        $nmea_array["talker_id"] = $nmea_talker_id;
        $nmea_array["sentence"] = $nmea_sentence;

        return $nmea_array;
    }

    public function explodeNMEA($text)
    {
        $parts = explode(",", $text);
        $last_index = array_key_last($parts);

        $last_parts = explode("*", $parts[$last_index]);
        if (count($last_parts) === 2) {
            $parts[$last_index] = $last_parts[0];
            $parts[] = $last_parts[1];
            $checksum = $last_parts[1];
        } else {
            return true;
        }

        return $parts;
    }

    public function validateNMEA($text)
    {
        $parts = explode("*", $text);
        if (!isset($parts[1])) {
            return false;
        }

        $provided_checksum = $parts[1];

        $test_string = ltrim($parts[0], "\$");

        $computed_checksum = 0;
        foreach (str_split($test_string) as $i => $character) {
            $byte = ord($character);
            $computed_checksum ^= $byte;
        }

        $computed_checksum = strtoupper(dechex($computed_checksum));
        $computed_checksum = str_pad($computed_checksum, 2, "0", STR_PAD_LEFT);

        $provided_checksum = rtrim($provided_checksum);
        if ($computed_checksum == $provided_checksum) {
            return true;
        }

        return false;
    }
    // APB - Autopilot Sentence "B"
    public function apbNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $status_a = $parts[1];
        $status_b = $parts[2];
        $cross_track_error_magnitude = $parts[3];
        $direction_to_steer = $parts[4];
        $cross_track_units = $parts[5];
        $arrival_circle_entered = $parts[6];
        $perpendicular_passed_at_waypoint = $parts[7];
        $bearing_origin_to_destination = $parts[8];
        $bearing_origin_to_destination_magnetic_true = $parts[9];
        $destination_waypoint_id = $parts[10];
        $bearing_present_position_to_destination = $parts[11];
        $bearing_present_position_to_destination_magnetic_true = $parts[12];
        $heading_to_steer_to_destination_waypoint = $parts[13];
        $heading_to_steer_to_destination_waypoint_magnetic_true = $parts[14];

        $apb = [
            "status_a" => $status_a,
            "status_b" => $status_b,
            "cross_track_error_magnitude" => $cross_track_error_magnitude,
            "direction_to_steer" => $direction_to_steer,
            "cross_track_units" => $cross_track_units,
            "arrival_circle_entered" => $arrival_circle_entered,
            "perpendicular_passed_at_waypoint" => $perpendicular_passed_at_waypoint,
            "bearing_origin_to_destination" => $bearing_origin_to_destination,
            "bearing_origin_to_destination_magnetic_true" => $bearing_origin_to_destination_magnetic_true,
            "destination_waypoint_id" => $destination_waypoint_id,
            "bearing_present_position_to_destination" => $bearing_present_position_to_destination,
            "bearing_present_position_to_destination_magnetic_true" => $bearing_present_position_to_destination_magnetic_true,
            "heading_to_steer_to_destination_waypoint" => $heading_to_steer_to_destination_waypoint,
            "heading_to_steer_to_destination_waypoint_magnetic_true" => $heading_to_steer_to_destination_waypoint_magnetic_true,
        ];
        return $apb;
    }

    // XTE - Cross-Track Error, Measured
    public function xteNMEA($text)
    {
        $parts = $this->explodeNMEA($text);

        $status_1 = $parts[1];
        $status_2 = $parts[2];

        $cross_track_error_magnitude = $parts[3];
        $direction_to_steer = $parts[4];
        $cross_track_units = $parts[5];
        $checksum = $parts[6];

        $xte = [
            "status_1" => $status_1,
            "status_2" => $status_2,
            "cross_track_error_magnitude" => $cross_track_error_magnitude,
            "direction_to_steer" => $direction_to_steer,
            "cross_track_units" => $cross_track_units,
            "checksum" => $checksum,
        ];
        return $xte;
    }

    // RMB - Recommended Minimum Navigation Information
    public function rmbNMEA($text)
    {
        $parts = $this->explodeNMEA($text);

        $status = $parts[1];
        $cross_track_error = $parts[2];
        $direction_to_steer = $parts[3];
        $to_waypoint_id = $parts[4];
        $from_waypoint_id = $parts[5];

        $destination_waypoint_latitude = $parts[6];
        $destination_waypoint_latitude_north_south = $parts[7];

        $destination_waypoint_longitude = $parts[8];
        $destination_waypoint_longitude_east_west = $parts[9];

        $range_to_destination_in_nautical_miles = $parts[10];
        $bearing_to_destination_in_degrees_true = $parts[11];

        $destination_closing_velocity_in_knots = $parts[12];
        $arrival_status = $parts[13];

        $rmb = [
            "status" => $status,
            "cross_track_error" => $cross_track_error,
            "direction_to_steer" => $direction_to_steer,
            "to_waypoint_id" => $to_waypoint_id,
            "from_waypoint_id" => $from_waypoint_id,

            "destination_waypoint_latitude" => $destination_waypoint_latitude,
            "destination_waypoint_latitude_north_south" => $destination_waypoint_latitude_north_south,

            "destination_waypoint_longitude" => $destination_waypoint_longitude,
            "destination_waypoint_longitude_east_west" => $destination_waypoint_longitude_east_west,

            "range_to_destination_in_nautical_miles" => $range_to_destination_in_nautical_miles,
            "bearing_to_destination_in_degrees_true" => $bearing_to_destination_in_degrees_true,

            "destination_closing_velocity_in_knots" => $destination_closing_velocity_in_knots,
            "arrival_status" => $arrival_status,
        ];

        return $rmb;
    }

    // GLL - Geographic Position - Latitude/Longitude
    public function gllNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $current_latitude = $parts[1];
        $current_latitude_north_south = $parts[2];
        $current_longitude = $parts[3];
        $current_longitude_east_west = $parts[4];
        $checksum = $parts[5];

        $gll = [
            "current_latitude" => $current_latitude,
            "current_latitude_north_south" => $current_latitude_north_south,
            "current_longitude" => $current_longitude,
            "current_longitude_east_west" => $current_longitude_east_west,
            "checksum" => $checksum,
        ];

        return $gll;
    }

    // GSA - GPS DOP and active satellites
    public function gsaNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $mode_1 = $parts[1];
        $mode_2 = $parts[2];

        $sv = [];

        $sv[0] = $parts[3];
        $sv[1] = $parts[4];
        $sv[2] = $parts[5];
        $sv[3] = $parts[6];
        $sv[4] = $parts[7];
        $sv[5] = $parts[8];
        $sv[6] = $parts[9];
        $sv[7] = $parts[10];
        $sv[8] = $parts[11];
        $sv[9] = $parts[12];
        $sv[10] = $parts[13];
        $sv[11] = $parts[14];

        $pdop = $parts[15];
        $hdop = $parts[15];
        $vdop = $parts[17];

        $gsa = [
            "mode_1" => $mode_1,
            "mode_2" => $mode_2,
            "SV_IDs" => $sv,
            "pdop" => $pdop,
            "hdop" => $hdop,
            "vdop" => $vdop,
        ];

        return $gsa;
    }

    // GSV - Satellites in view
    public function gsvNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $total_messages_of_this_type = $parts[1];
        $message_number = $parts[2];
        $total_number_of_SVs_in_view = $parts[3];

        $SV_PRN_number = [];
        $elevation_in_degrees = [];
        $azimuth_degrees_from_true_north = [];
        $SNR = [];

        $SV_PRN_number[1] = $parts[4];
        $elevation_in_degrees[1] = $parts[5];
        $azimuth_degrees_from_true_north[1] = $parts[6];
        $SNR[1] = $parts[7];

        $SVs = [];

        $SVs[1] = [
            "SV_PRN_number" => $SV_PRN_number[1],
            "elevation_in_degrees" => $elevation_in_degrees[1],
            "azimuth_degrees_from_true_north" =>
                $azimuth_degrees_from_true_north[1],
            "SNR" => $SNR[1],
        ];

        $SV_PRN_number[2] = $parts[8];

        if (count($parts) == 9) {
            $checksum = $parts[8];
        } else {
            $elevation_in_degrees[2] = $parts[9];
            $azimuth_degrees_from_true_north[2] = $parts[10];
            $SNR[2] = $parts[11];

            $SVs[2] = [
                "SV_PRN_number" => $SV_PRN_number[2],
                "elevation_in_degrees" => $elevation_in_degrees[2],
                "azimuth_degrees_from_true_north" =>
                    $azimuth_degrees_from_true_north[2],
                "SNR" => $SNR[2],
            ];
        }

        if (count($parts) == 13) {
            $checksum = $parts[12];
        } else {
            $SV_PRN_number[3] = $parts[12];
            $elevation_in_degrees[3] = $parts[13];
            $azimuth_degrees_from_true_north[3] = $parts[14];
            $SNR[3] = $parts[15];

            $SVs[3] = [
                "SV_PRN_number" => $SV_PRN_number[3],
                "elevation_in_degrees" => $elevation_in_degrees[3],
                "azimuth_degrees_from_true_north" =>
                    $azimuth_degrees_from_true_north[3],
                "SNR" => $SNR[3],
            ];
        }

        if (count($parts) == 17) {
            $checksum = $parts[16];
        } else {
            $SV_PRN_number[4] = $parts[16];
            $elevation_in_degrees[4] = $parts[17];
            $azimuth_degrees_from_true_north[4] = $parts[18];
            $SNR[4] = $parts[19];

            $SVs[4] = [
                "SV_PRN_number" => $SV_PRN_number[4],
                "elevation_in_degrees" => $elevation_in_degrees[4],
                "azimuth_degrees_from_true_north" =>
                    $azimuth_degrees_from_true_north[4],
                "SNR" => $SNR[4],
            ];
        }

        $gsv = [
            "total_messages_of_this_type" => $total_messages_of_this_type,
            "message_number" => $message_number,
            "total_number_of_SVs_in_view" => $total_number_of_SVs_in_view,
            "SVs" => $SVs,
        ];
        return $gsv;
    }

    // RMC - Recommended Minimum Navigation Information
    public function rmcNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $time_stamp = $parts[1];
        $validity = $parts[2];
        $current_latitude = $parts[3];
        $current_latitude_north_south = $parts[4];

        $current_longitude = $parts[5];
        $current_longitude_east_west = $parts[6];

        $speed_in_knots = $parts[7];

        $true_course = $parts[8];

        $date_stamp = $parts[9];
        $variation = $parts[10];
        $variation_east_west = $parts[11];
        $checksum = $parts[12];

        $rmc = [
            "time_stamp" => $time_stamp,
            "validity" => $validity,
            "current_latitude" => $current_latitude,
            "current_latitude_north_south" => $current_latitude_north_south,
            "current_longitude" => $current_longitude,
            "current_longitude_east_west" => $current_longitude_east_west,
            "speed_in_knots" => $speed_in_knots,
            "true_course" => $true_course,
            "date_stamp" => $date_stamp,
            "variation" => $variation,
            "variation_east_west" => $variation_east_west,
            "checksum" => $checksum,
        ];

        return $rmc;
    }

    // GGA - Global Positioning System Fix Data
    public function ggaNMEA($text)
    {
        $parts = $this->explodeNMEA($text);
        $time = $parts[1]; // UTC of position
        $latitude = $parts[2];
        $latitude_north_south = $parts[3];

        $longitude = $parts[4];
        $longitude_east_west = $parts[5];

        $fix_quality = $parts[6];

        // Number of satellites in use, 00 - 12
        $number_of_satellites = $parts[7];
        $horizontal_dilution_of_precision = $parts[8];
        $altitude_above_mean_sea_level = $parts[9];
        $altitude_units = $parts[10];
        $height_of_mean_sea_level_above_WGS84_earth_ellipsoid = $parts[11];
        $units_of_the_geoid_seperation = $parts[12];
        $time_since_last_DGPS_update = $parts[13];
        $DGPS_reference_station_id = $parts[14];
        $checksum = $parts[15];

        $gga = [
            "fix_time" => $time,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "fix_quality" => $fix_quality,
            "number_of_satellites" => $number_of_satellites,
            "horizontal_dilution_of_precision" => $horizontal_dilution_of_precision,
            "altitude_above_mean_sea_level" => $altitude_above_mean_sea_level,
            "altitude_units" => $altitude_units,
            "height_of_mean_sea_level_above_WGS84_earth_ellipsoid" => $height_of_mean_sea_level_above_WGS84_earth_ellipsoid,
            "units_of_the_geoid_seperation" => $units_of_the_geoid_seperation,
            "time_since_last_DGPS_update" => $time_since_last_DGPS_update,
            "DGPS_reference_station_id" => $DGPS_reference_station_id,
            "checksum" => $checksum,
        ];

        return $gga;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isNMEA($text)
    {
        // NMEA strings appear to start with $ and have 5 alpha following.
        if (substr($text, 0, 1) !== '\$') {
            if (ctype_alpha(substr($text, 1, 5))) {
                return true;
            }
        }
        return false;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();
        $message_thing = new Message($this->thing, $this->thing_report);
    }

    public function makeSMS()
    {
        $sms_message = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function extractNMEA($text)
    {
        return true;
    }

    public function readNMEA($text)
    {
        return $this->parseNMEA($text);
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));

        $nmea = false;

        if ($filtered_input != "") {
            $nmea = $this->extractNMEA($filtered_input);

            if ($nmea !== false) {
                $this->response .= "Saw a NMEA of " . $nmea . "°. ";
                $this->nmea = $nmea;
            }
        }

        if ($nmea === false) {
            $this->response .= "Did not hear a nmea. ";
        }
    }
}
