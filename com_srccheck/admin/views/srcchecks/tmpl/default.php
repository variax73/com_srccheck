<?php
/**
 ************************************************************************
 Source Files Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.2
 ************************************************************************
 */

defined('_JEXEC') or die('Restricted Access');
?>

<form action="index.php?option=com_srccheck&view=srcchecks" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
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
                                <?php echo JText::_('COM_SRCCHECK_NEW_FILES'); ?>
                            </td>
                            <td>
                                <?php echo $row->new_files; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('COM_SRCCHECK_DELETED_FILES'); ?>
                            </td>
                            <td>
                                <?php echo $row->deleted_files; ?>
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
       	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>