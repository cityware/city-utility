<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of Files
 *
 * @author Fabricio
 */
class Files
{
    private $savePath, $originalPath;

    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;
    }

    public function getSavePath()
    {
        return $this->savePath;
    }

    public function setOriginalPath($originalPath)
    {
        $this->originalPath = $originalPath;
    }

    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    public function uploadFile()
    {
        try {
            return move_uploaded_file($this->originalPath, $this->savePath);
        } catch (Exception $exc) {
            throw new Exception('Erro no uoload de arquivo - ' . $exc->getTraceAsString(), 500);
        }
    }

    public function removefile()
    {
        return @unlink($this->originalPath);
    }

}
