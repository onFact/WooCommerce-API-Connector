<?php
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>
<!-- Our admin page content should all be inside .wrap -->
<div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
        <a href="?page=onfact" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?=__('Settings', 'onfact')?></a>
        <?php if (get_option('document_to_create') == 'Orderslip'): ?><a href="?page=onfact&tab=orderslip" class="nav-tab <?php if($tab==='orderslip'):?>nav-tab-active<?php endif; ?>"><?=__('Order Slip', 'onfact')?></a><?PHP endif; ?>
        <?php if (get_option('document_to_create') == 'Deliveryslip'): ?><a href="?page=onfact&tab=deliveryslip" class="nav-tab <?php if($tab==='deliveryslip'):?>nav-tab-active<?php endif; ?>"><?=__('Delivery Slip', 'onfact')?></a><?PHP endif; ?>
        <?php if (get_option('document_to_create') == 'Invoice'): ?><a href="?page=onfact&tab=invoice" class="nav-tab <?php if($tab==='invoice'):?>nav-tab-active<?php endif; ?>"><?=__('Invoice', 'onfact')?></a><?PHP endif; ?>
    </nav>

    <div class="tab-content" style="background-color: white; padding: 10px;">
        <?php switch($tab) :
            case 'orderslip':
                include('settings-orderslip.php');
                break;
            case 'deliveryslip':
                include('settings-deliveryslip.php');
                break;
            case 'invoice':
                include('settings-invoice.php');
                break;
            default:
                include('settings-general.php');
                break;
        endswitch; ?>
    </div>
</div>

