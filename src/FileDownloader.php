<?php
namespace Th;

/**
 * Helper class for handling file downloads.
 * 
 * @author Thomas Marinissen
 */
class FileDownloader {
    /**
     * constant variable that holds the error code if a file is of the wrong
     * MIME type.
     */
    const FILE_WRONG_MIME = 1;

    /**
     * constant variable that holds the error code if a file is of the wrong
     * extension.
     */
    const FILE_WRONG_EXTENSION = 2;

    /**
     * constant variable that is used whenever the requested download directory
     * is invalid
     */
    const INVALID_DOWNLOAD_DIR = 3;

    /**
     * Destination directory for the download file. The default destination is 
     * the /tmp/ directory
     * 
     * @var string
     */
    private $downloadDir = "/tmp/";
    
    /**
     * The source url of the file to download
     * 
     * @var string
     */
    private $url;
    
    /**
     * The original name of the file
     * 
     * @var string
     */
    private $nameOriginal;

    /**
     * Original name of the downloaded file.
     * 
     * @var string
     */
    private $name;

    /**
     * The MIME type of the downloaded file
     * 
     * @var string|null
     */
    private $type = null;
    
    /**
     * The download file extension
     * 
     * @var string|null
     */
    private $extension = null;

    /**
     * Array of the allowed file types for an download.
     * 
     * @var array|null
     */
    private $allowedTypes = null;

    /**
     * Array class attribute that defines what extensions are allowed.
     * 
     * @var array|null
     */
    private $allowedExtensions = null;

    /**
     * Constructor method.
     * 
     * @param string        The download file url
     * @param array|null    The allowed file mime types
     * @param array|null    The allowed file extensions
     * @param string        The download directory
     */
    public function __construct($url, $allowedTypes = null, $allowedExtension = null, $downloadDir = null) {
        // set the different class variables
        $this->setAllowedTypes($allowedTypes)
            ->setAllowedExtensions($allowedExtension)
            ->setDownloadDir($downloadDir)
            ->parse($url)
            ->setName($this->nameOriginal());
    }
    
    /**
     * Get the download file url
     * 
     * @return string               The url of the file to download
     */
    public function url() {
        return $this->url;
    }
    
    /**
     * Get the original name
     * 
     * @return string                   The original download file name
     */
    public function nameOriginal() {
        return $this->nameOriginal;
    }

    /**
     * Function for getting the download directory name.
     * 
     * @return string               The download file directory path
     */
    public function downloadDir() {
        return $this->downloadDir;
    }

    /**
     * Method for getting the download destination (directory + path).
     * 
     * @return string               The download file path including the file name
     */
    public function downloadFilePath() {
        return $this->downloadDir() . $this->name();
    }

    /**
     * Returns the original file name.
     * 
     * @return string               The original file name
     */
    public function name() {
        return $this->name;
    }

    /**
     * Method that returns the MIME type of the download file.
     *  
     * @return string               The file MIME type
     */
    public function mimeType() {
        // if the type is known, return it
        if (!is_null($this->type)) {
            return $this->type;
        }
        
        // make sure the file is available to check
        $this->downloadFile();
        
        // get the file info
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        
        // get the MIME type of the file 
        $mime= finfo_file($finfo, $this->downloadFilePath());
        
        // clode the file info resource
        finfo_close($finfo);
        
        // store and return the file MIME type
        return $this->type = $mime;
    }
    
    /**
     * Get the download file extension
     * 
     * @return  string              The file extension
     */
    public function extension() {
        return $this->extension;
    }

    /**
     * Method that returns an array with the allowed MIME types. Will return an
     * empty array if there are no restrictions for the MIME type.
     * 
     * @return array                The allowed MIME types
     */
    public function allowedTypes() {
        return $this->allowedTypes;
    }

    /**
     * Method that returns the array with the allowed file extensions for the
     * file to download.
     * 
     * @return array                The allowed download file extensions
     */
    public function allowedExtensions() {
        return $this->allowedExtensions;
    }
    
    /**
     * Set the name for the download file
     * 
     * @param   string                                 The name for the download file
     * @return  \Th\FileDownloader                     The instance of this to make chaining possible
     */
    public function setName($name) {
        // get the path info of the given name
        $pathinfo = pathinfo($name);
        
        // get the base file name
        $filename = $pathinfo['filename'];
        
        // set the file name
        $this->name = $filename . '.' . $this->extension();

        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the destination download directory. It checks if the requested
     * directory is a valid directory, if not, it throws a new DownloadException.
     * 
     * @param   string                                              The directory to download the file to
     * @return  \Th\FileDownloader                                  The instance of this, to make chaining possible
     * 
     * @throws  \Th\FileDownloader\Exceptions\DownloadException     Thrown if the the directory is not a valid directory.
     */
    public function setDownloadDir($dir) {
        // if no download dir is given, return
        if (is_null($dir)) {
            return $this;
        }
        
        // if the download directory is not a valid directory, throw an exception
        if (!is_dir($dir)) {
            throw new \Th\FileDownloader\Exceptions\DownloadException(\Th\FileDownloader::INVALID_DOWNLOAD_DIR);
        }
        
        // set the download directory
        $this->downloadDir = $dir;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the allowed MIME types.
     * 
     * @param   array                         The allowed mime types for download
     * @return  \Th\FileDownloader            The instance of this, to make chaining possible
     */
    public function setAllowedTypes($allowedTypes) {
        $this->allowedTypes = $allowedTypes;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that sets the allowed file extensions
     * 
     * @param   array                         The allowed file extensions for downlaod
     * @return  \Th\FileDownloader            The instance of this, to make chaining possible
     */
    public function setAllowedExtensions($allowedExtensions) {
        $this->allowedExtensions = $allowedExtensions;
        
        // done, return the instance of this, to make chaining possible
        return $this;
    }

    /**
     * Method that checks if the download file is of the allowed MIME type.
     * 
     * @return boolean                                              Whether the download file MIME type is allowed for download
     * @throws \Th\FileDownloader\Exceptions\DownloadException      Thrown if the MIME type of the download file is not allowed
     */
    public function validateFileType() {
        // if the allowed types is null, all file types are allowed
        if (is_null($this->allowedTypes())) {
            return true;
        }
        
        // if the download file type is not in the list with allowed file types,
        // or if the allowed mime types list is not an array, throw a new exception
        if (!is_array($this->allowedTypes()) || !in_array($this->mimeType(), $this->allowedTypes())) {
            throw new \Th\FileDownloader\Exceptions\DownloadException(\Th\FileDownloader::FILE_WRONG_MIME);
        }
        
        // everything ok
        return true;
    }

    /**
     * Method that checks if the download file is of the allowed extension.
     * 
     * @return boolean                                              Whether the download file extension is an allowed extension
     * @throws \Th\FileDownloader\Exceptions\DownloadException      Thrown if the extension of the download file is not allowed
     */
    public function validateExtension() {
        // if the allowed extension value is null, all file extensions are allowed
        if (is_null($this->allowedExtensions())) {
            return true;
        }
        
        // if the allowed extensions list is not an array or if the downloaded file
        // extension is not in the list of allowed extensions, throw an exception
        if (!is_array($this->allowedExtensions()) || !in_array($this->extension(), $this->allowedExtensions())) {
            throw new \Th\FileDownloader\Exceptions\DownloadException(\Th\FileDownloader::FILE_WRONG_EXTENSION);
        }
        
        // everything ok
        return true;
    }

    /**
     * Method that downloads the file
     * 
     * @return boolean                                              Whether the download was successful
     * @throws \Th\FileDownloader\Exceptions\DownloadException      Thrown if an error occures while downloading the file
     */
    public function download() {
        // check if the file extension is allowed
        $this->validateExtension();       
        
        // download the file
        $this->downloadFile();
        
        // check the mime type for the file, if it is of the wrong type, unset the
        // file and throw the error on
        try {
            $this->validateFileType();
        } catch (Exception $exception) {
            // remove the file, the mime type is not allowed
            unlink($this->downloadFilePath());
            
            // throw the error on
            throw $exception;
        }

        // everything ok
        return true;
    }

    /**
     * Set the url, original name and original extension based on the download url
     *
     * @param   string                        The download file url
     * @return  \Th\FileDownloader            The instance of this, to make chaining possible
     */
    private function parse($url) {
        // set the download file url
        $this->url = $url;

        // parse the url, to remove possible query parameters, otherwise it is not possible to fetch the extension
        $urlParsed = parse_url($url);

        // get the path info of the given name
        $pathinfo = pathinfo($urlParsed['path']);

        // get the base file name
        $this->nameOriginal = $pathinfo['filename'];

        // set the file name
        $this->extension = $pathinfo['extension'];

        // done, return the instance of this, to make chaining possible
        return $this;
    }
    
    /**
     * Helper function to download the file
     * 
     * @throws \Th\FileDownloader\Exceptions\DownloadException          Throw an error if it is not possible to download the file
     */
    private function downloadFile() {
        // if the file exists already, return out, file already was downloaded
        if (file_exists($this->downloadFilePath())) {
            return;
        }
        
        // download the file
        $file = file_get_contents($this->url());

        // if it was not possible to get the file content, throw a new error
        if ($file === false) {
            throw new \Th\FileDownloader\Exceptions\DownloadException(99);
        }

        // try writing the file to disk
        $downloadResult = file_put_contents($this->downloadFilePath(), $file);

        // if it is not possible to download the file, return
        if ($downloadResult === false) {
            throw new \Th\FileDownloader\Exceptions\DownloadException(99);
        }
    }

}
