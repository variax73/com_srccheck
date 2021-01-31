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
defined('_JEXEC') or die('Restricted Access');
$document = JFactory::getDocument();
?>

<script type="text/javascript">
    jQuery(document).ready(function($){
        $("#srcCheckTabs > li").on("click", function() {
            activeTab = $("a", this).attr("href");
            window.localStorage.setItem( "srcCheckTabActive", activeTab);
            /**
            * Set activeTab in code.
            */
            $( "[href*='scat='], [action*='scat=']" ).each(function(){
                $.each(this.attributes, function() {
                    if( this.value.includes( "scat=" ) ) {
                        $( "["+this.name+"='"+this.value+"']" ).attr(this.name, this.value.replace( /scat[^&]+/i, "scat=" + activeTab.substr( 1 ) ) );
                    }
                });
            });
        })
})
</script>

<script type="text/javascript">
    jQuery(document).ready(function($){
                activeTab = localStorage.getItem('srcCheckTabActive');
                if ( !activeTab )
                {
                    activeTab = $("#srcCheckTabs li:first a").attr("href");
                    window.localStorage.setItem('srcCheckTabActive', activeTab);
                }
                $( "#srcCheckTabs > li > a[href=" + activeTab + "]" ).click();
            }
        )
</script>

<form action="index.php?option=com_srccheck&view=srcchecks&scat=" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php if (!empty($this->items)) : ?>
            <?php 
                foreach ( $this->items as $row ) $tab[]=$row;
                echo JHtml::_('bootstrap.startTabSet', 'srcCheck' );
            ?>
                <?php foreach ($tab as $i => $row) : ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'srcCheck', $row->id, JText::_($row->ta_name)) ?>
                    <table class="table table-striped table-hover">
                        <tbody>
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
                        </tbody>
                    </table>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endforeach; ?>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?> 
	<?php endif; ?>
       	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
    </div>
</form>