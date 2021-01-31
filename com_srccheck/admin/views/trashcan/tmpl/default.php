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
srcCheckLog::start();


$listOrder     = $this->escape($this->filter_order);
srcCheckLog::debug( "Test 1");
$listDirn      = $this->escape($this->filter_order_Dir);
srcCheckLog::debug( "Test 2");
?>
<script type="text/javascript">
    jQuery(document).ready(function($){
//        $("#srcCheckTabs > li").on("click", function() {
            /**
            * Set activeTab in code.
            */
        activeTab = window.localStorage.getItem( "srcCheckTabActive" );

        $( "[href*='scat='], [action*='scat=']" ).each(function(){
            $.each(this.attributes, function() {
                if( this.value.includes( "scat=" ) ) {
//console.log( $( "["+this.name+"*='scat='" ).attr(this.name).replace( /scat[^&]+/i, "scat=" + activeTab.substr( 1 ) ) );
//console.log(this.name, this.value );
//console.log( this.value, this.value.replace( /scat[^&]+/i, "scat=" + activeTab.substr( 1 ) ) );
//console.log( $( "["+this.name+"='"+this.value+"']" ).attr(this.name) );
                    $( "["+this.name+"='"+this.value+"']" ).attr(this.name, this.value.replace( /scat[^&]+/i, "scat=" + activeTab.substr( 1 ) ) );
//console.log(this.name, this.value );
                }
            });
        });
//        })
})
</script>

<?php srcCheckLog::debug( "Test 3"); ?>

<form action="index.php?option=com_srccheck&view=trashcan&scat=" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span6">
                <?php echo JText::_('COM_SRCCHECK_MANAGE_FILTER'); ?>
<?php srcCheckLog::debug( "Test 4"); ?>
		<?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
<?php srcCheckLog::debug( "Test 5"); ?>
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
						<td align="center">
							<?php
//                                                            echo JText::_($row->last_check_id);
                                                            switch ($row->veryfied) {
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
       	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>
<?php srcCheckLog::stop(); ?>