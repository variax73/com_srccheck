<?php
/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   2.0.0
 **************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
<form action="index.php?option=com_srccheck&view=manage&layout=filehistory&tmpl=component&scat=1&file_id=" method="post" id="adminForm" name="adminForm">
    <div id="j-main-container" class="span10">
	<table class="table table-striped table-hover">
            <tbody>
                <tr>
                    <td>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_LOCALISATION" ); ?>
                    </td>
                    <td>
                        <?php echo $this->items[0]->f_path . DIRECTORY_SEPARATOR . $this->items[0]->f_filename; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_STORED_IN_TA" ); ?>
                    </td>
                    <td>
                        <?php echo $this->items[0]->ta_path . DIRECTORY_SEPARATOR . $this->items[0]->ta_filename . " [" . $this->items[0]->ta_name . "]"; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_STATUS" ); ?>
                    </td>
                    <td>
                        <?php switch ($this->items[0]->f_status)
                                {
                                    case FILE_STATUS_NEW:
                                        echo JText::_('COM_SRCCHECK_NEW_STATUS_FILE');
                                        break;
                                    case FILE_STATUS_VERIFIED:
                                        echo JText::_('COM_SRCCHECK_VERYFIED_STATUS_FILE');
                                        break;
                                    case FILE_STATUS_DELETED:
                                        echo JText::_('COM_SRCCHECK_DELETED_STATUS_FILE');
                                        break;
                                    case FILE_STATUS_IN_TRASHCAN:
                                        echo JText::_('COM_SRCCHECK_IN_TRASHCAN_STATUS_FILE');
                                        break;
                                    default:
                                        echo JText::_('COM_SRCCHECK_UNEXPECTED_STATUS_FILE');
                                }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_FIRST_VERYFICATION_TIME" ); ?>
                    </td>
                    <td>
                        <?php echo $this->items[0]->f_timestamp; ?>
                    </td>
                </tr>
            </tbody>
        </table>
	<table class="table table-striped table-hover">
            <thead>
		<tr>
<!--                    <th>
                    	  <!-- ?php // echo JHtml::_('grid.checkall'); ? 
                    </th>-->
                    <th>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_VERSION" ); ?>
                    </th>
                    <th>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_VERYFICATION_DATE" ); ?>
                    </th>
                    <th>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_IN_TA_LOCALISATION" ); ?>
                    </th>
                    <th>
                        <?php echo JText::_( "COM_SRCCHECK_FILE_VERYFIED_STATUS" ); ?>
                    </th>
		</tr>
            </thead>
            <tbody>
                <?php if (!empty($this->items)) : ?>
		<?php foreach ($this->items as $i => $row) : ?>
                <tr>
<!--                    <td>
                        <!-- ?php
                            echo JHtml::_('grid.id', $i, $row->c_id);
                        ?>
                    </td>-->
                    <td>
                        <?php echo $row->c_crc_check_history_id; ?>
                    </td>
                    <td>
                        <?php echo $row->c_timestamp; ?>
                    </td>
                    <td>
                        <?php echo $row->ta_path . DIRECTORY_SEPARATOR . $row->ta_filename . "[" . $row->c_ta_localisation . "]"; ?>
                    </td>
                    <td align="center">
                        <?php
                            switch ($row->c_veryfied)
                            {
                                case FILE_CHECKED_STATUS_INVALID:
                                    echo JText::_('COM_SRCCHECK_INVALID_STATUS_FILE');
                                    break;
                                case FILE_CHECKED_STATUS_VALID:
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
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>
