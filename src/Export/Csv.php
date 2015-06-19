<?php

namespace Cityware\Utility\Export;

/**
 * Description of Grid
 * @category   Cityware
 * @package    Cityware\Utility\Export
 * @subpackage Csv
 * @copyright  Copyright (c) 2011-2011 Cityware Technologies BRA Inc. (http://www.cityware.com.br)
 */
class Csv
{
    protected $dataTitles;
    protected $delimiter;
    protected $enclosure;
    protected $encloseAll;
    protected $data;
    protected $dataConcat = Array();
    protected $filename = 'datagridexport';

    /**
     * @param array  $data
     * @param string $delimiter
     * @param string $enclosure
     * @param bool   $encloseAll
     */
    public function __construct(array $data = null, $delimiter = ';', $enclosure = '"', $encloseAll = false)
    {
        $this->data = $data;
        $this->setDelimiter($delimiter);
        $this->setEnclosure($enclosure);
        $this->setEncloseAll($encloseAll);
    }

    /**
     * @param  type $dataTitles
     * @return Xml
     */
    public function setDataTitles($dataTitles)
    {
        $this->dataTitles = $dataTitles;

        return $this;
    }

    /**
     * @param  array $data
     * @return Xml
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  type $filename
     * @return Csv
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param  string                    $delimiter
     * @return Csv
     * @throws \InvalidArgumentException if delimiter is not a single character.
     */
    public function setDelimiter($delimiter)
    {
        if (!is_string($delimiter) || strlen($delimiter) != 1) {
            throw new \InvalidArgumentException('Delimiter must be a single character.');
        }
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param  string                    $enclosure
     * @return Csv
     * @throws \InvalidArgumentException if enclosure is not a single character.
     */
    public function setEnclosure($enclosure)
    {
        if (!is_string($enclosure) || strlen($enclosure) != 1) {
            throw new \InvalidArgumentException('Enclosure must be a single character.');
        }
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Determines if every field should be enclosed by $enclosure.
     * If false (default) then field will only be enclosed if it contains a space, the
     * delimiter character, or the enclosure character.
     *
     * @param  bool $encloseAll
     * @return Csv
     */
    public function setEncloseAll($encloseAll)
    {
        $this->encloseAll = (bool) $encloseAll;

        return $this;
    }

    /**
     * @return string CSV output.
     */
    public function render()
    {
        $delimiter_esc = preg_quote($this->delimiter, '/');
        $enclosure_esc = preg_quote($this->enclosure, '/');
        $encl = $this->enclosure;
        $str = '';

        array_push($this->dataConcat, $this->dataTitles);
        foreach ($this->data as $value) {
            array_push($this->dataConcat, $value);
        }

        foreach ($this->dataConcat as $row) {
            $output = array();
            foreach ($row as $field) {
                // Enclose fields containing $delimiter, $enclosure or whitespace
                if ($this->encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                    $output[] = $encl . str_replace($encl, $encl . $encl, $field) . $encl;
                } else {
                    $output[] = $field;
                }
            }
            $str .= implode($this->delimiter, $output) . PHP_EOL;
        }

        // send first headers
        ob_end_clean();

        header('Content-Description: File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header("Content-Type: application/csv");
        header('Content-Disposition: attachment; filename="' . $this->filename . '.csv"');
        header('Content-Transfer-Encoding: binary');

        echo $str;

        exit;
    }

}
