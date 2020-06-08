<h1 class="wp-heading-inline"><?=__('onFact Connector Setup', 'onfact')?></h1>
<!-- Form to handle the upload - The enctype value here is very important -->
<form  method="post" action="options.php">
    <table class="form-table" role="presentation">

        <tbody>

        <?php settings_fields( 'onfact-settings' ); ?>
        <?php do_settings_sections( 'onfact-settings' ); ?>
        <tr>
            <th scope="row"><label for="api_key"><?=__('API Key', 'onfact')?></label></th>
            <td><input name="api_key" type="text" id="api_key" value="<?php echo get_option('api_key'); ?>" class="regular-text"></td>
        </tr>

        <?php settings_fields( 'onfact-settings' ); ?>
        <?php do_settings_sections( 'onfact-settings' ); ?>
        <tr>
            <th scope="row"><label for="api_key"><?=__('Company UUID', 'onfact')?></label></th>
            <td><input name="company_uuid" type="text" id="company_uuid" value="<?php echo get_option('company_uuid'); ?>" class="regular-text"></td>
        </tr>


        <tr>
            <th scope="row"><?=__('Stock', 'onfact')?>:</th>
            <td>
                <?php settings_fields( 'onfact-settings' ); ?>
                <?php do_settings_sections( 'onfact-settings' ); ?>
                <fieldset>
                    <label for="use_onfact_stock">
                        <input name="use_onfact_stock" type="checkbox" id="use_onfact_stock" value="true" <?PHP if(get_option('use_onfact_stock') === 'true') { echo "checked"; } ?>>
                        <?=__('Use onFact Stock', 'onfact')?>
                    </label>
                </fieldset>
            </td>
        </tr>

        <tr>
            <th scope="row"><?=__('Document to Create after Order', 'onfact')?>:</th>
            <td>
                <select name="document_to_create" id="document_to_create" class="regular-text">
                    <option value="Orderslip" <?= 'Orderslip' == get_option('document_to_create') ? 'selected=selected' : ''?>><?=__('Order Slip', 'onfact')?></option>
                    <option value="Deliveryslip" <?= 'Deliveryslip' == get_option('document_to_create') ? 'selected=selected' : ''?>><?=__('Delivery Slip', 'onfact')?></option>
                    <option value="Invoice" <?= 'Invoice' == get_option('document_to_create') || empty(get_option('document_to_create')) ? 'selected=selected' : ''?>><?=__('Invoice', 'onfact')?></option>
                </select>
            </td>
        </tr>

        </tbody>
    </table>
    <?php submit_button('Save') ?>
</form>
