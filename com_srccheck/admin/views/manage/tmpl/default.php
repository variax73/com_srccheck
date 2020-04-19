<?php
/**
 ************************************************************************
 Source Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   1.0.1
 ************************************************************************
 */

defined('_JEXEC') or die('Restricted Access');

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
			<th width="2%">
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
						<td>
							<?php
                                                            echo JHtml::_('grid.id', $i, $row->file_id);
                                                        ?>
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
