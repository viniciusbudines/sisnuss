<?php /* $Id: addedit_folder.php 2016 2011-08-07 07:08:46Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/files/addedit_folder.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$file_folder_parent = intval(w2PgetParam($_GET, 'file_folder_parent', 0));
$folder = intval(w2PgetParam($_GET, 'folder', 0));

// add to allow for returning to other modules besides Files
$referrerArray = parse_url($_SERVER['HTTP_REFERER']);
$referrer = $referrerArray['query'] . $referrerArray['fragment'];

// check permissions for this record
$perms = &$AppUI->acl();
$canAuthor = canAdd('files');
$canEdit = canEdit('files');

// check permissions
if (!$canAuthor && !$folder) {
	$AppUI->redirect('m=public&a=access_denied');
}

if (!$canEdit && $folder) {
	$AppUI->redirect('m=public&a=access_denied');
}

// check permissions for this record
if ($folder == 0) {
	$canEdit = $canAuthor;
}
if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CFileFolder();
if ($folder > 0) {
	$canDelete = $obj->canDelete($msg, $folder);
}

$q = new w2p_Database_Query();
$q->addTable('file_folders');
$q->addQuery('file_folders.*');
$q->addWhere('file_folder_id=' . $folder);
$obj = null;
$q->loadObject($obj);

// load the record data
if (!$obj && $folder > 0) {
	$AppUI->setMsg('File Folder');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$folders = getFolderSelectList();
// setup the title block
$ttl = $folder ? 'Edit File Folder' : 'Add File Folder';
$titleBlock = new CTitleBlock($ttl, 'folder5.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=files', 'files list');
if ($canEdit && $folder > 0) {
	$titleBlock->addCrumbDelete('delete file folder', $canDelete, $msg);
}
$titleBlock->show();

?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.folderFrm;
	var msg = '';
	if (f.file_folder_name.value.length < 1) {
		msg += "\n<?php echo $AppUI->_('Folder Name'); ?>";
		f.file_folder_name.focus();
	}
	if( msg.length > 0) {
		alert('<?php echo $AppUI->_('Please type'); ?>:' + msg);
	} else {
		f.submit();
	}
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('Delete Folder'); ?>" )) {
		var f = document.folderFrm;
		f.del.value='1';
		f.submit();
	}
}
</script>
<form name="folderFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="dosql" value="do_folder_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_folder_id" value="<?php echo $folder; ?>" />
	<input type="hidden" name="redirect" value="<?php echo $referrer; ?>" />
    <table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">
        <tr>
            <td width="100%" valign="top" align="center">
                <table cellspacing="1" cellpadding="2" width="60%">
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Subfolder of'); ?>:</td>
                        <td align="left">
                        <?php
                            $parent_folder = ($folder > 0) ? $obj->file_folder_parent : $file_folder_parent;
                            echo arraySelectTree($folders, 'file_folder_parent', 'style="width:175px;" class="text"', $parent_folder);
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Folder Name'); ?>:</td>
                        <td align="left">
                            <input type="text" class="text" id="ffn" name="file_folder_name" value="<?php echo $obj->file_folder_name; ?>" maxlength="255" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
                        <td align="left">
                            <textarea name="file_folder_description" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_folder_description; ?></textarea>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?'); ?>')){location.href = '?<?php echo $referrer; ?>';}" />
            </td>
            <td align="right">
                <input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>