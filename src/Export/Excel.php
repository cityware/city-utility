<?php

namespace Cityware\Utility\Export;

/**
 * Description of Grid
 * @category   Cityware
 * @package    Cityware\Utility\Export
 * @subpackage Excel
 * @copyright  Copyright (c) 2011-2011 Cityware Technologies BRA Inc. (http://www.cityware.com.br)
 */
class Excel
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
        $this->xml .= '<Row>'.PHP_EOL;

        foreach ($this->dataTitles as $value) {
            $this->xml .= '<Cell><Data ss:Type="String">' . $value . '</Data></Cell>'.PHP_EOL;
        }
        $this->xml .= '</Row>'.PHP_EOL;

        return $this;
    }

    public function buildGrid()
    {
        if (!empty($this->data)) {
            foreach ($this->data as $row) {
                $this->xml .= '<Row>'.PHP_EOL;
                foreach ($row as $value) {
                    $type = !is_numeric($value) ? 'String' : 'Number';
                    $value = strip_tags($value);
                    $this->xml .= '<Cell><Data ss:Type="' . $type . '">' . $value . '</Data></Cell>'.PHP_EOL;
                }
                $this->xml .= '</Row>'.PHP_EOL;
            }
        }

        return $this;
    }

    /**
     * @return string Xml output.
     */
    public function render()
    {
        $this->xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . PHP_EOL;

        $this->xml .= '<Worksheet ss:Name="Cityware Export Datagrid Table" ss:Description="Cityware Export Datagrid Table"><Table>';
        $this->buildTitles();
        $this->buildGrid();
        $this->xml .= '</Table></Worksheet>';
        $this->xml .= '</Workbook>';

        ob_end_clean();
        header('Content-Description: File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header("Content-Type: application/excel");
        header('Content-Disposition: attachment; filename="' . $this->filename . '.xls"');
        header('Content-Transfer-Encoding: binary');
        echo $this->xml;
        exit;
    }
}
