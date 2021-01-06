<?php
/**
 **************************************************************************
 Source Files Check - component that verifies the integrity of Joomla files
 **************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 **************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');


$listOrder     = $this->escape($this->filter_order);
$listDirn      = $this->escape($this->filter_order_Dir);
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

<form action="index.php?option=com_srccheck&view=manage&scat=" method="post" id="adminForm" name="adminForm">
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
                    <th>
                    	<?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th>
                        <?php echo JHtml::_('grid.sort','COM_SRCCHECK_PATH', 'path', $listDirn, $listOrder) ;?>
                    </th>
                    <th>
                        <?php echo JHtml::_('grid.sort','COM_SRCCHECK_FILENAME', 'filename', $listDirn, $listOrder); ?>
                    </th>
                    <th>
                        <?php echo JHtml::_('grid.sort','COM_SRCCHECK_STATUS', 'status', $listDirn, $listOrder); ?>
                    </th>
                    <th>
                        <?php echo JHtml::_('grid.sort','COM_SRCCHECK_VERYFIED', 'veryfied', $listDirn, $listOrder); ?>
                    </th>
                    <th>
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
                            <?php echo JHtml::_('grid.id', $i, $row->file_id); ?>
                        </td>
                        <td>
                            <?php echo $row->path; ?>
                        </td>
                        <td>
                            <?php echo $row->filename; ?>
                        </td>
                        <td>
                            <?php
                                switch ($row->status)
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
                                        JText::_('COM_SRCCHECK_UNEXPECTED_STATUS_FILE');
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                switch ($row->veryfied)
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
                        <td>
                            <?php
                                $selector = "fileHistory".$row->file_id;
                                echo $this->addHistory( array(  "filename"          => $row->filename,
                                                                "selector"          => $selector,
                                                                "file_id"           => $row->file_id,
                                                                "trustedarchive_id" => $row->trustedarchive_id) );
                            ?>
                            <button data-toggle="modal" data-target="#<?php echo $selector; ?>" id="<?php echo $row->file_id; ?>">
                                <?php echo JText::_('COM_SRCCHECK_BTN_DETAILS'); ?>
                            </button>
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


