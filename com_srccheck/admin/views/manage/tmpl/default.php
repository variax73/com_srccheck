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

?>
<form action="index.php?option=com_srccheck&view=manage" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">
	<table class="table table-striped table-hover">
                <thead>
		<tr>
			<th width="1%"><?php echo JText::_('COM_HELLOWORLD_NUM'); ?></th>
			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="90%">
				<?php echo JText::_('COM_HELLOWORLD_HELLOWORLDS_NAME') ;?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_HELLOWORLD_PUBLISHED'); ?>
			</th>
			<th width="2%">
				<?php echo JText::_('COM_HELLOWORLD_ID'); ?>
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
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td>
							<?php echo $row->path; ?>
						</td>
						<td>
							<?php echo $row->filename; ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
       	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>


