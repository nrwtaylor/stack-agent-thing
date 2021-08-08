<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Table extends Agent
{
    public function init()
    {
        $this->dataTable();
    }

    public function readSubject()
    {
    }

    public function dataTable()
    {
        $data = [];
        $data['X'] = [
            'index' => 4,
            'head_code' => 'X',
            'alias' => 'X',
            'flag' => 'X',
            'day' => 'X',
            'run_at' => 'X',
            'end_at' => 'X',
            'runtime' => 'X',
            'available' => 'X',
            'quantity' => 'X',
            'consist' => 'X',
            'route' => 'X',
        ];

        $this->data = $data;
    }

    public function getTable()
    {
        //        $table = [['a' => ['b', 'c', 'd']], ['e' => ['f', 'g', 'h']]];

        $table = [
            'index' => ['INDEX' => 7],
            'head_code' => ['HEAD' => 4],
            'alias' => ['ALIAS' => 10],
            'flag' => ['FLAG' => 6],
            'day' => ['DAY' => 4],

            'run_at' => ['RUNAT' => 6],
            'end_at' => ['ENDAT' => 6],

            'runtime' => ['RUNTIME' => 8],

            'available' => ['AVAILABLE' => 6],
            'quantity' => ['QUANTITY' => 9],
            'consist' => ['CONSIST' => 6],
            'route' => ['ROUTE' => 6],
        ];

        $this->table = $table;
    }

    public function makeSMS()
    {
        $sms = "TABLE | Text TEXT. Or TXT.";
        $this->thing_report['sms'] = $sms;
        $this->sms = $sms;
    }

    public function makeTXT($table = null)
    {
        if ($table == null) {
            if (isset($this->table)) {
                $table = $this->table;
            }
        }

        $t = $this->textTable($table);

        $this->txt = $t;
        $this->thing_report['txt'] = $t;
    }

    public function cellTable($variable)
    {
        $key = key($variable);
        $value = $variable[$key];

// TODO review
        if (is_int($value)) {return true;}

        $width = $value[key($value)];

        $t = " " . str_pad($key, $value, " ", STR_PAD_LEFT);

        return $t;
    }

    public function headerTable()
    {
        $this->getTable();
        $t = "";
        foreach ($this->table as $i => $table_variable) {
            $variable_name = key($table_variable);
            $value = $variable_name;
            $width = $table_variable[$value];

            $variable = [$value => $width];

            $t .= $this->cellTable($variable);
        }

        return $t;
    }

    public function rowTable($row_id)
    {
        $variable = $this->data[$row_id];
        $t = "";
        foreach ($this->table as $key => $x) {
            $value = $variable[$key];

            $width = 5;
            $k = $this->table[$key];

            $title = key($k);

            $width = $k[$title];

            $v = [$value => $width];
            $t .= $this->cellTable($v);
        }

        return $t;
    }

    public function textTable($data = null)
    {
        $this->getTable();
        if ($data == null and !isset($this->data)) {
            $this->getData();
            $data = $this->data;
        }

        $this->data = $data;
        $t = "";
        $t .= $this->headerTable();
        $t .= "\n";

        if ($this->data != null) {
            foreach ($this->data as $row_id => $variable) {
                $t .= $this->rowTable($row_id);
                $t .= "\n";
            }
	}

        return $t;
    }
}
