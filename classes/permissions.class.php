<?php /* $Id: permissions.class.php 1808 2011-04-12 17:08:04Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/classes/permissions.class.php $ */

class w2Pacl extends w2p_Extensions_Permissions {

	public function __construct($opts = null)
	{
		parent::__construct($opts);
		trigger_error("w2Pacl has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Extensions_Permissions instead.", E_USER_NOTICE );
	}
}