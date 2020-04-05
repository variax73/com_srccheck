<?php

/* 
 * Copyright (C) 2020 Your Name <your.name at your.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package     Joomla.Administrator
 * @subpackage  com_srccheck
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->filter_order);
$listDirn      = $this->escape($this->filter_order_Dir);
?>

<form action="index.php?option=com_srccheck&view=manage" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span6">
                <?php echo JText::_('COM_SRCCHECK_MANAGE_FILTER'); ?>
		<?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
            </div>
        </div>
	<table class="table table-striped table-hover">
                <thead>
		<tr>
<!-- 			<th width="1%"><?php //echo JText::_('COM_SRECCHECK_NUM'); ?></th>
-->			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="50%">
				<?php echo JHtml::_('grid.sort','COM_SRCCHECK_PATH', 'path', $listDirn, $listOrder) ;?>
			</th>
			<th width="30%">
				<?php echo JHtml::_('grid.sort','COM_SRCCHECK_FILENAME', 'filename', $listDirn, $listOrder); ?>
			</th>
			<th width="20%">
				<?php echo JHtml::_('grid.sort','COM_SRCCHECK_STATUS', 'status', $listDirn, $listOrder); ?>
			</th>
			<th width="20%">
				<?php echo JHtml::_('grid.sort','COM_SRCCHECK_VERYFIED', 'veryfied', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) : ?>

					<tr>
<!--						<td>
							<?php //echo $this->pagination->getRowOffset($i); ?>
						</td>
-->						<td>
							<?php echo JHtml::_('grid.id', $i, $row->check_id); ?>
						</td>
						<td>
							<?php echo $row->path; ?>
						</td>
						<td>
							<?php echo $row->filename; ?>
						</td>
						<td align="center">
							<?php
                                                            switch ($row->status) {
                                                                case 0:
                                                                    echo JText::_('COM_SRCCHECK_NEW_STATUS_FILE');
                                                                    break;
                                                                case 1:
                                                                    echo JText::_('COM_SRCCHECK_VERYFIED_STATUS_FILE');
                                                                    break;
                                                                case 2:
                                                                    echo JText::_('COM_SRCCHECK_DELETED_STATUS_FILE');
                                                                    break;
                                                                default:
                                                                    echo JText::_('COM_SRCCHECK_UNEXPECTED_STATUS_FILE');
                                                            }
                                                        ?>
						</td>
						<td align="center">
							<?php
//                                                            echo JText::_($row->last_check_id);
                                                            switch ($row->veryfied) {
                                                                case 0:
                                                                    echo JText::_('COM_SRCCHECK_INVALID_STATUS_FILE');
                                                                    break;
                                                                case 1:
                                                                    echo JText::_('COM_SRCCHECK_VALID_STATUS_FILE');
                                                                    break;
                                                                default:
                                                                    echo JText::_('COM_SRCCHECK_UNEXPECTED_STATUS_FILE');
                                                            }
                                                        ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
       	<input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
       	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>


