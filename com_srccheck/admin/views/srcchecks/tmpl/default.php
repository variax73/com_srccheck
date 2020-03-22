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

<form action="index.php?option=com_srccheck&view=srcchecks" method="post" id="adminForm" name="adminForm">
	<table class="table table-striped table-hover">
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row) : ?>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_TOTALCOUNT_FILES'); ?>
                            </td>
                            <td>
                                <?php echo $row->total_count_files; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_COUNT_VERYFIED_POSITIVE'); ?>
                            </td>
                            <td>
                                <?php echo $row->count_veryfied_positive; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_COUNT_VERYFIED_NEGATIVE'); ?>
                            </td>
                            <td>
                                <?php echo $row->count_veryfied_negative; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_LAST_CHECK_TIME'); ?>
                            </td>
                            <td>
                                <?php echo $row->last_check_time; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_USER_ID'); ?>
                            </td>
                            <td>
                                <?php echo $row->user_id; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_USER_LOGIN'); ?>
                            </td>
                            <td>
                                <?php echo $row->user_login; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_USER_NAME'); ?>
                            </td>
                            <td>
                                <?php echo $row->user_name; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
		<?php endif; ?>
            </tbody>
	</table>
</form>