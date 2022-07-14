<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RenamingFileTempory
 *
 * @uses
 * renaming the file on Temporary purpose to send the file some where with specific name and after send delete file
 * form temporary folder with folder
 * 
 * @how its work
 * create folder in archive with random number after the send file delete file with folder name
 * 
 * @author user
 */
class RenamingFileTemporary {

    private $filename;
    private $required_filename;
    private $rand_dir;

    /*
     * set file name with directory
     * @params $filename
     */

    function setFilename($filename) {
        $this->filename = $filename;
    }

    /*
     * get file name
     * @return filename
     */

    function getFilename() {
        return $this->filename;
    }

    /*
     * set @variable required_filename
     * @params $required_filename
     */

    function setRequired_filename($required_filename) {
        $this->required_filename = $required_filename;
    }

    /*
     * get required file name
     * @return required_filename
     */

    function getRequired_filename() {
        return $this->required_filename;
    }

    /*
     * set random directory path
     * @params $rand_dir
     */

    function setRand_dir($rand_dir) {
        $this->rand_dir = $rand_dir;
    }

    /*
     * get random directory path
     * @return rand_dir
     */

    function getRand_dir() {
        return $this->rand_dir;
    }

    /*
     * its use for copy the file at temporary random genrated path
     *  
     * @required 
     * set filename, set required filename
     * 
     * @return copied path with name {$updated_filename}
     */

    function rename_file() {
        $random_num = rand(50000,100000);

        $from_file = $this->filename;
        create_directory(TMP_RENAMING_PATH . $random_num);

        $ext = pathinfo($from_file, PATHINFO_EXTENSION);
        $updated_filename = TMP_RENAMING_PATH . $random_num . '/' . $this->required_filename . '.' . $ext;
        $this->setRand_dir(TMP_RENAMING_PATH . $random_num);

        copy($from_file, $updated_filename);

        return $updated_filename;
    }

    /*
     * delte temporary directory after no need of file
     */

    function delete_temp() {
        $files = glob($this->rand_dir . '/{,.}*', GLOB_BRACE);

        if (!empty($files)) {
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    @unlink($file); // delete file
            }
        }

        @rmdir($this->rand_dir);
    }

}
