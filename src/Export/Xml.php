<?php
namespace Cityware\Utility\Export;

/**
 * Description of Grid
 * @category   Cityware
 * @package    Cityware\Utility\Export
 * @subpackage Xml
 * @copyright  Copyright (c) 2011-2011 Cityware Technologies BRA Inc. (http://www.cityware.com.br)
 */
class Xml
{
    protected $dataTitles;
    protected $data;
    protected $xml;
    protected $filename = 'datagridexport';

    /**
     * @param array  $data
     * @param string $delimiter
     * @param string $enclosure
     * @param bool   $encloseAll
     */
    public function __construct(array $dataTitles = null, array $data = null)
    {
        $this->data = $data;
        $this->dataTitles = $dataTitles;
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
     * @return Xml
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    public function buildTitles()
    {
        $this->xml .= "<titles>".PHP_EOL;
        $count = 0;
        foreach ($this->dataTitles as $value) {
            $this->xml .= "<column" . $count . "><![CDATA[" . strip_tags($value) . "]]></column" . $count . ">" . PHP_EOL;
            $count++;
        }
        $this->xml .= "</titles>".PHP_EOL;

        return $this;
    }

    public function buildGrid()
    {
        $this->xml .= "<results>".PHP_EOL;
        foreach ($this->data as $value) {
            $this->xml .= "<row>".PHP_EOL;
            $count = 0;
            foreach ($value as $value2) {
                $this->xml .= "<column" . $count . "><![CDATA[" . strip_tags($value2) . "]]></column" . $count . ">".PHP_EOL;
                $count++;
            }
            $this->xml .= "</row>".PHP_EOL;
        }
        $this->xml .= "</results>".PHP_EOL;

        return $this;
    }

    /**
     * @return string Xml output.
     */
    public function render()
    {
        $this->xml = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;

        $this->xml .= "<grid>".PHP_EOL;
        $this->buildTitles();
        $this->buildGrid();
        $this->xml .= "</grid>".PHP_EOL;

        ob_end_clean();
        header('Content-Description: File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header("Content-Type: application/xml");
        header('Content-Disposition: attachment; filename="' . $this->filename . '.xml"');
        header('Content-Transfer-Encoding: binary');
        echo $this->xml;
        exit;
    }

}
