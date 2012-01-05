<?php /* $Id: reports.class.php 1533 2010-12-18 08:41:46Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/reports/reports.class.php $ */

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision: 1533 $
 */

class CReport {
    protected $reportFilename = '';
    protected $tempDir = '';
    protected $expiresIn = 30;

    public function __construct()
    {
        $baseString = time();
        $this->reportFilename = md5($baseString);
        $this->tempDir = w2PgetConfig('root_dir').'/files/temp';
    }

    public function getFilename()
    {
        return $this->reportFilename;
    }

    public function hook_cron()
    {
        // number of seconds in $this->expiresIn days
        $expires = 60 * 60 * 24 * $this->expiresIn;

        if ($handle = opendir($this->tempDir)) {
            while (false !== ($file = readdir($handle))) {
                if ('.pdf' == substr($file, -4)) {
                    $fullPath = $this->tempDir.'/'.$file;
                    $fileAge = filemtime($fullPath);
                    if ((time() - $fileAge) >= $expires && is_writable($fullPath)) {
                        unlink ($fullPath);
                    }
                }
            }
        }
    }

}