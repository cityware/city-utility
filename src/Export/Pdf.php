<?php

namespace Cityware\Utility\Export;

use Cityware\Pdf as ZfPdf;

/**
 * Description of Grid
 * @category   Cityware
 * @package    Cityware\Utility\Export
 * @subpackage Pdf
 * @copyright  Copyright (c) 2011-2011 Cityware Technologies BRA Inc. (http://www.cityware.com.br)
 */
class Pdf
{
    protected $_arrayForm, $_arrayLocale, $_arrayData;
    protected $_pdf;
    protected $_page, $_totalPages, $_currentPage;
    protected $_cellFontSize = 8, $_cell;
    protected $_width = 0, $_height, $_type, $_attr;
    protected $_styles;
    protected $_font;
    protected $_la;
    protected $_altura, $_largura, $_largura1;
    protected $_style, $_styleText, $_topo, $_td, $_td2;

    /**
     * @param array $options
     */
    public function __construct($options = Array())
    {
        $this->_arrayForm = (isset($options['ini']) and !empty($options['ini'])) ? $options['ini'] : null;
        $this->_arrayLocale = (isset($options['translate']) and !empty($options['translate'])) ? $options['translate'] : null;
    }

    /**
     * @param  array                         $arrayForm
     * @return \Cityware\Datagrid\Export\Pdf
     */
    public function setArrayForm(array $arrayForm)
    {
        $this->_arrayForm = $arrayForm;

        return $this;
    }

    /**
     * @param  array                         $arrayLocale
     * @return \Cityware\Datagrid\Export\Pdf
     */
    public function setArrayLocale($arrayLocale)
    {
        $this->_arrayLocale = $arrayLocale;

        return $this;
    }

    /**
     * @param  array $data
     * @return Xml
     */
    public function setData(array $data)
    {
        $this->_arrayData = $data;

        return $this;
    }

    /**
     * Renderiza o documento PDF
     * @param array $options
     */
    public function render(array $options = array())
    {
        $options = $this->optionsTratament($options);
        $this->_pdf = new ZfPdf\PdfDocument();
        $this->_font = ZfPdf\Font::fontWithName(ZfPdf\Font::FONT_HELVETICA);

        //$options['colors'] = array_merge($colors, (array) $options['colors']);

        $this->_la = '';
        $larg = $this->calculateCellSize();
        $this->_cellFontSize = 8;

        // Define os estilos do documento
        $this->defineStyles($options);

        // Pega o total de páginas
        $this->getTotalPages($options);

        // Adiciona página ao documento PDF
        $this->addPage($options);

        // Adicionar logomarca se definido nas opções
        $this->addLogo($options);

        // Adiciona Titulo e Subtitulo de acordo com as opções definidas
        $this->addTitle($options);

        //Iniciar a contagem de paginas
        $pagina = 1;

        // Desenha o rodapé do documento
        $this->drawFooter($pagina, $options);

        $this->_page->setFont($this->_font, $this->_cellFontSize);

        $iLarg = 0;
        foreach ($larg as $final) {
            $this->_cell[$iLarg] = round($final * ($this->_page->getWidth() - 80) / array_sum($larg));
            $iLarg++;
        }

        $cellsCount = count($this->_arrayForm['gridfieldsconfig']);
        $this->_largura = ($this->_page->getWidth() - 80) / $cellsCount;
        $this->_altura = $this->_page->getHeight() - 120;

        // Desenha os nomes dos campos
        $this->drawFieldName($this->_topo, $this->_styleText);

        $this->_page->setStyle($this->_style);

        if (is_array($this->_arrayData) and !empty($this->_arrayData)) {

            $ia = 0;
            $aa = 0;
            foreach ($this->_arrayData as $value) {
                if ($this->_altura <= 80) {

                    // Adiciona página ao documento PDF
                    $this->addPage($options);

                    $pagina++;

                    // Adicionar logomarca se definido nas opções
                    $this->addLogo($options);

                    // Adiciona Titulo e Subtitulo de acordo com as opções definidas
                    $this->addTitle($options);

                    //set font
                    $this->_altura = $this->_page->getHeight() - 120;

                    // Desenha o rodapé do documento
                    $this->drawFooter($pagina, $options);

                    //
                    $this->_largura1 = 40;
                    $this->_page->setFont($this->_font, $this->_cellFontSize + 1);

                    // Desenha os nomes dos campos
                    $this->drawFieldName($this->_topo, $this->_styleText);
                }

                $this->_la = 0;
                $this->_altura = $this->_altura - 16;
                $i = 0;
                $tdf = $ia % 2 ? $this->td : $this->td2;
                $a = 1;

                //A linha horizontal
                //$centrar = round(($this->_page->getWidth() - 80) / 2) + 30;
                if ((int) $this->_la == 0) {
                    $this->_largura1 = 40;
                } else {
                    $this->_largura1 = $this->_cell[$i - 1] + $this->_largura1;
                }
                $this->_la = 0;

                ////////////
                //Vamos saber quantas linhas tem este registo
                $nlines = array();
                $nl = 0;
                foreach ($value as $value2) {
                    $line = $this->widthForStringUsingFontSize(strip_tags(trim($value2)), $this->_font, 8);
                    $nlines[] = ceil($line / $this->_cell[$nl]);
                    $nl++;
                }

                sort($nlines);
                foreach ($value as $value1) {
                    $value1 = strip_tags(trim($value1));
                    if ((int) $this->_la == 0) {
                        $this->_largura1 = 40;
                    } else {
                        $this->_largura1 = $this->_cell[$i - 1] + $this->_largura1;
                    }

                    $this->_page->setStyle($tdf);
                    $this->_page->drawRectangle($this->_largura1, $this->_altura - 4, $this->_largura1 + $this->_cell[$i] + 1, $this->_altura + 12);
                    $this->_page->setStyle($this->_styleText);
                    $this->_page->drawText($value1, $this->_largura1 + 2, $this->_altura, 'UTF-8');
                    $this->_la = $this->_largura1;
                    $i++;
                    $a++;
                }

                $aa++;
                $ia++;
            }
        }
        header('Content-Description: File Transfer');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header("Content-Type: application/pdf");
        header('Content-Disposition: attachment; filename="' . $options['name'] . '.pdf"');
        header('Content-Transfer-Encoding: binary');

        echo $this->_pdf->render();
        die();
    }

    /**
     * Função de calculo de largura da uma string de acordo com o tamanho da fonte
     * @copyright http://n4.nabble.com/Finding-width-of-a-drawText-Text-in-Zend-Pdf-td677978.html
     * @param $string
     * @param $font
     * @param $fontSize
     */
    private function widthForStringUsingFontSize($string, $font, $fontSize = 8)
    {
        @$drawingString = \iconv('', 'UTF-16BE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;

        return $stringWidth;
    }

    /**
     * Função que calcula os tamanhos das celulas do grid
     * @return array
     */
    private function calculateCellSize()
    {
        $this->_font = ZfPdf\Font::fontWithName(ZfPdf\Font::FONT_HELVETICA);
        $i = 0;
        $larg = Array();

        foreach ($this->_arrayForm['gridfieldsconfig'] as $field => $value) {
            if ($field == 'dta_cadastro' or $field == 'dta_atualizacao') {
                $larg[$i] = 4;
            } elseif ($field == 'ind_status') {
                $larg[$i] = 2;
            } elseif ($value['type'] == 'primarykey') {
                $larg[$i] = 2;
            } else {
                $fieldName = $this->_arrayLocale->translate($field);
                $larg[$i] = $this->widthForStringUsingFontSize($fieldName, $this->_font, 8);
            }
            $i++;
        }
        /*
          foreach ($this->_arrayData as $value) {
          $i = 0;
          foreach ($value as $value2) {
          $value2 = strip_tags($value2);
          if ($larg[$i] < strlen($value2)) {
          $larg[$i] = strlen($value2);
          }
          $i++;
          }
          }
         */

        return $larg;
    }

    /**
     * Tratamento das opções de configuração
     * @param  array $options
     * @return array
     */
    private function optionsTratament(array $options = array())
    {
        if (!isset($options['orientation']) or empty($options['orientation'])) {
            $options['orientation'] = 'LANDSCAPE';
        }
        if (!isset($options['size']) or empty($options['size'])) {
            $options['size'] = 'A4';
        }
        if (!isset($options['title']) or empty($options['title'])) {
            $options['title'] = 'Export Datagrid Cityware';
        }
        if (!isset($options['subtitle']) or empty($options['subtitle'])) {
            $options['subtitle'] = 'Export Datagrid Cityware';
        }
        if (!isset($options['footer']) or empty($options['footer'])) {
            $options['footer'] = 'Export Datagrid Cityware';
        }
        if (!isset($options['name']) or empty($options['name'])) {
            $options['name'] = date('Y_m_d_H_i_s');
        }
        if (!isset($options['noPagination']) or empty($options['noPagination'])) {
            $options['noPagination'] = 0;
        }

        $options['colors'] = array('title' => '#000000',
            'subtitle' => '#111111',
            'footer' => '#111111',
            'header' => '#AAAAAA',
            'row1' => '#EEEEEE',
            'row2' => '#FFFFFF',
            'sqlexp' => '#BBBBBB',
            'lines' => '#111111',
            'hrow' => '#E4E4F6',
            'text' => '#000000',
            'filters' => '#F9EDD2',
            'filtersBox' => '#DEDEDE');

        return $options;
    }

    /**
     * Pega a contagem total de páginas
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function getTotalPages(array $options = array())
    {
        if (strtoupper($options['orientation']) == 'LANDSCAPE' && strtoupper($options['size']) == 'A4') {
            $this->_totalPages = ceil(count($this->_arrayData) / 26);
        } elseif (strtoupper($options['orientation']) == 'LANDSCAPE' && strtoupper($options['size']) == 'LETTER') {
            $this->_totalPages = ceil(count($this->_arrayData) / 27);
        } else {
            $this->_totalPages = ceil(count($this->_arrayData) / 37);
        }
        if ($this->_totalPages < 1) {
            $this->_totalPages = 1;
        }

        return $this;
    }

    /**
     * Define os estilos do documento
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function defineStyles(array $options = array())
    {
        $this->_style = new ZfPdf\Style();
        $this->_style->setFillColor(new ZfPdf\Color\Html($options['colors']['lines']));

        $this->_topo = new ZfPdf\Style();
        $this->_topo->setFillColor(new ZfPdf\Color\Html($options['colors']['header']));

        $this->td = new ZfPdf\Style();
        $this->td->setFillColor(new ZfPdf\Color\Html($options['colors']['row2']));

        $this->td2 = new ZfPdf\Style();
        $this->td2->setFillColor(new ZfPdf\Color\Html($options['colors']['row1']));

        $this->_styleText = new ZfPdf\Style();
        $this->_styleText->setFillColor(new ZfPdf\Color\Html($options['colors']['text']));

        return $this;
    }

    /**
     * Adiciona nova página ao documento PDF
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function addPage(array $options = array())
    {
        // Add new page to the document
        if (strtoupper($options['size'] = 'LETTER') && strtoupper($options['orientation']) == 'LANDSCAPE') {
            $this->_page = $this->_pdf->newPage(ZfPdf\Page::SIZE_LETTER_LANDSCAPE);
        } elseif (strtoupper($options['size'] = 'LETTER') && strtoupper($options['orientation']) != 'LANDSCAPE') {
            $this->_page = $this->_pdf->newPage(ZfPdf\Page::SIZE_LETTER);
        } elseif (strtoupper($options['size'] != 'A4') && strtoupper($options['orientation']) == 'LANDSCAPE') {
            $this->_page = $this->_pdf->newPage(ZfPdf\Page::SIZE_A4_LANDSCAPE);
        } else {
            $this->_page = $this->_pdf->newPage(ZfPdf\Page::SIZE_A4);
        }

        $this->_page->setStyle($this->_style);
        $this->_pdf->pages[] = $this->_page;
        $this->_page->setFont($this->_font, 14);

        return $this;
    }

    /**
     * Adicionar logomarca se definido nas opções
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function addLogo(array $options = array())
    {
        if (isset($options['logo']) and file_exists($options['logo'])) {
            $image = ZfPdf\Image::imageWithPath($options['logo']);
            list ($this->_width, $this->_height, $this->_type, $this->_attr) = getimagesize($options['logo']);
            $this->_page->drawImage($image, 40, $this->_page->getHeight() - $this->_height - 40, 40 + $this->_width, $this->_page->getHeight() - 40);
        }

        return $this;
    }

    /**
     * Adiciona Titulo e Subtitulo de acordo com as opções definidas
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function addTitle(array $options = array())
    {
        $this->_page->drawText($options['title'], $this->_width + 40, $this->_page->getHeight() - 70, 'UTF-8');
        $this->_page->setFont($this->_font, $this->_cellFontSize);
        $this->_page->drawText($options['subtitle'], $this->_width + 40, $this->_page->getHeight() - 80, 'UTF-8');

        return $this;
    }

    /**
     * Desenha os campos em formato tabela
     * @param  type                          $this->_topo
     * @param  type                          $this->_styleText
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function drawFieldName()
    {
        $iTitulos = 0;
        $this->_page->setFont($this->_font, $this->_cellFontSize + 1);
        foreach ($this->_arrayForm['gridfieldsconfig'] as $field => $value) {
            if ((int) $this->_la == 0) {
                $this->_largura1 = 40;
            } else {
                $this->_largura1 = $this->_cell[$iTitulos - 1] + $this->_largura1;
            }

            $this->_page->setStyle($this->_topo);
            $this->_page->drawRectangle($this->_largura1, $this->_altura - 4, $this->_largura1 + $this->_cell[$iTitulos] + 1, $this->_altura + 12);
            $this->_page->setStyle($this->_styleText);

            $fieldName = $this->_arrayLocale->translate($field);

            $this->_page->drawText($fieldName, $this->_largura1 + 2, $this->_altura, 'UTF-8');
            $this->_la = $this->_largura1;

            $iTitulos++;
        }
        $this->_page->setFont($this->_font, $this->_cellFontSize);

        return $this;
    }

    /**
     * Desenha o rodapé do documento
     * @param  int                           $pagina
     * @param  array                         $options
     * @return \Cityware\Datagrid\Export\Pdf
     */
    private function drawFooter($pagina, array $options = array())
    {
        $this->_page->drawText($options['footer'], 40, 40, 'UTF-8');
        if (@$options['noPagination'] != 1) {
            $this->_page->drawText(' Página N. ' . $pagina . '/' . ($this->_totalPages), $this->_page->getWidth() - (strlen(' Página N. ') * $this->_cellFontSize) - 50, 40, 'UTF-8');
        }

        return $this;
    }

}
