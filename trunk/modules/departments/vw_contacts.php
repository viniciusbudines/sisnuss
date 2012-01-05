<?php /* $Id: vw_contacts.php 1533 2010-12-18 08:41:46Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/departments/vw_contacts.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $dept_id, $dept, $company_id;
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">

<tr><th><?php echo $AppUI->_('Name'); ?></th><th><?php echo $AppUI->_('Email'); ?></th><th><?php echo $AppUI->_('Telephone'); ?></th></tr>
<?php
$contacts = CDepartment::getContactList($AppUI, $dept_id);

$contact = new CContact();
foreach ($contacts as $contact_id => $contact_data) {
	$contact->contact_id = $contact_id;

    echo '<tr><td><a href="./index.php?m=contacts&a=view&contact_id=' . $contact_data['contact_id'] . '">' . $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'] . '</a></td>';
    echo '<td>' . w2p_email($contact_data['contact_email']) . '</td>';
	echo '<td>' . $contact_data['contact_phone'] . '</td></tr>';
}
?>
	<tr>
		<td colspan="3" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class="button" value="<?php echo $AppUI->_('new contact'); ?>" onclick="javascript:window.location='./index.php?m=contacts&a=addedit&company_id=<?php echo $company_id; ?>&company_name=<?php echo $dept['company_name']; ?>&dept_id=<?php echo $dept['dept_id']; ?>&dept_name=<?php echo $dept['dept_name']; ?>'">			
		</td>
	</tr>
</table>