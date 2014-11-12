<?php
/**
 * Created by Antoine on 12/11/14
 * at 10:45
 */

namespace oat\tao\model\media;


interface MediaManagement {

    /**
     * @param array $file data of file to upload
     * @param string $path of the directory in which to upload
     * @return boolean the upload was successful or not
     */
    public function upload($file, $path);


    /**
     * @param $filename
     * @return boolean the suppression was successful
     */
    public function delete($filename);


} 