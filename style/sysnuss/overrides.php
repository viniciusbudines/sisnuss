<?php /* $Id: overrides.php 1569 2010-12-31 05:42:00Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/style/wps-redmond/overrides.php $ */

class CTitleBlock extends CTitleBlock_core {
}

##
##  This overrides the show function of the CTabBox_core function
##
class CTabBox extends CTabBox_core {
	function show($extra = '', $js_tabs = false) {
		global $AppUI, $w2Pconfig, $currentTabId, $currentTabName, $m, $a;
		$this->loadExtras($m, $a);
		$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
		if (!$uistyle) {
			$uistyle = 'web2project';
		}
		reset($this->tabs);
		$s = '';
		// tabbed / flat view options
		if ($AppUI->getPref('TABVIEW') == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
			$s .= '<a href="' . $this->baseHRef . 'tab=0">' . $AppUI->_('tabbed') . '</a> : ';
			$s .= '<a href="' . $this->baseHRef . 'tab=-1">' . $AppUI->_('flat') . '</a>';
			$s .= '</td>' . $extra . '</tr></table>';
			echo $s;
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr></table>';
			} else {
				echo '<img src="./style/'.$uistyle.'/images/shim.gif" height="10" width="1" alt="" />';
			}
		}

		if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
			// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</strong></td></tr><tr><td>';
				$currentTabId = $k;
				$currentTabName = $v[1];
				include $this->baseInc . $v[0] . '.php';
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
			// tabbed view
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td><table border="0" cellpadding="0" cellspacing="0">';

			if (count($this->tabs) - 1 < $this->active) {
				//Last selected tab is not available in this view. eg. Child tasks
				$this->active = 0;
			}
			foreach ($this->tabs as $k => $v) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$sel = ($k == $this->active) ? 'Selected' : '';
				$s .= '<td height="28" valign="middle" width="3"><img src="./style/' . $uistyle . '/images/tab' . $sel . 'Left.png" width="3" height="28" id="lefttab_' . $k . '"border="0" alt="" /></td>';
				$s .= '<td id="toptab_' . $k . '" valign="middle" nowrap="nowrap"';
				if ($js_tabs) {
					$s .= ' class="' . $class . '"';
				} else {
					$s .= ' background="./style/' . $uistyle . '/images/tab' . $sel . 'Bg.png"';
				}
				$s .= '>&nbsp;<a href="';
				if ($this->javascript) {
					$s .= 'javascript:' . $this->javascript . "({$this->active}, $k)";
				} elseif ($js_tabs) {
					$s .= 'javascript:show_tab(' . $k . ')';
				} else {
					$s .= $this->baseHRef . 'tab=' . $k;
				}
				$s .= '">' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</a>&nbsp;</td>';
				$s .= '<td valign="middle" width="3"><img id="righttab_' . $k . '" src="./style/' . $uistyle . '/images/tab' . $sel . 'Right.png" width="3" height="28" border="0" alt="" /></td>';
				$s .= '<td width="3" class="tabsp"><img src="./style/'.$uistyle.'/images/shim.gif" height="1" width="3" alt=""/></td>';
			}
			$s .= '</table></td></tr>';
			$s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 4 + 1) . '" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if ($this->tabs[$this->active][0] != '') {
				$currentTabId = $this->active;
				$currentTabName = $this->tabs[$this->active][1];
				if (!$js_tabs) {
					require $this->baseInc . $this->tabs[$this->active][0] . '.php';
				}
			}
			if ($js_tabs) {
				foreach ($this->tabs as $k => $v) {
					echo '<div class="tab" id="tab_' . $k . '">';
					$currentTabId = $k;
					$currentTabName = $v[1];
					require $this->baseInc . $v[0] . '.php';
					echo '</div>';
					echo '<script language="javascript" type="text/javascript">
						<!--
						show_tab(' . $this->active . ');
						//-->
						</script>';
				}
			}
			echo '</td></tr></table>';
		}
	}
}

function styleRenderBoxTop() { }

function styleRenderBoxBottom() { }