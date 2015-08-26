<?php
namespace Th\FileDownloader\Exceptions;

/**
 * Exception class for handling exceptions while downloading files. The error codes can be found in the FileDownloader
 * class.
 *
 * @author Thomas Marinissen
 */
class DownloadException extends \Exception {

    /**
     * Create a new Download exception. The message sent is defined in the
     * privat codeToMessage method of this class.
     *
     * @param   int             The error message identifier
     */
    public function __construct($code) {
        // get the error message
        $message = $this->codeToMessage($code);

        // call the parent
        parent::__construct($message, $code);
    }

    /**
     * Method that switches the error code constants till it finds the messages
     * that belongs to the error code and then returns the message.
     *
     * @param   int                 The error code
     * @return  string              The error message
     */
    private function codeToMessage($code) {
        // get the error message belong to the given code
        switch ($code) {
            case \Th\FileDownloader::FILE_WRONG_MIME:
                $message = "File is of the wrong MIME type";
                break;
            case \Th\FileDownloader::FILE_WRONG_EXTENSION:
                $message = "File is of the wrong extension";
                break;
            case \Th\FileDownloader::INVALID_DOWNLOAD_DIR:
                $message = "User set download directory does not exist";
                break;
            default:
                $message = "Unknown download error";
                break;
        }

        // done, return the error message
        return $message;
    }
}
