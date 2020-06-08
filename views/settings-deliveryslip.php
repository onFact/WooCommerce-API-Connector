<?php
try {
    ?>
    <h1 class="wp-heading-inline"><?=__('Delivery Slip', 'onfact')?></h1>
    <p><?=__('You can create Descriptions and Email Descriptions in your onFact account', 'onfact')?></p>
    <!-- Form to handle the upload - The enctype value here is very important -->
    <form  method="post" action="options.php">
        <h3 class="wp-heading-inline"><?=__('Document Description Text', 'onfact')?></h3>
        <table class="form-table" role="presentation">

            <tbody>

            <?PHP
            settings_fields('onfact-settings-deliveryslip');

            $descriptions = Onfact_Settings::get_onfact_descriptions();
            $languages = Onfact_Settings::get_onfact_languages();
            foreach ($languages->getItems() as $language):
                if (!$language->getActive()) continue;
                ?>
                <?php do_settings_sections( 'onfact-settings-deliveryslip' ); ?>
                <tr>
                    <th scope="row"><label for="api_key"><?=__('Description', 'onfact')?> <?=__($language->getName(), 'onfact')?></label></th>
                    <td>
                        <select name="deliveryslip_description_<?=$language->getId()?>" id="description.<?=$language->getId()?>" class="regular-text">
                            <option value="none"><?=__('No Description', 'onfact')?></option>
                            <?PHP foreach ($descriptions->getItems() as $description): ?>
                                <?PHP if ($description->getLanguageId() !== $language->getId() || $description->getModel() !== 'Deliveryslip') continue ?>
                                <option
                                        value="<?=$description->getId()?>"
                                    <?=$description->getId() == get_option('deliveryslip_description_' . $language->getId()) ? 'selected=selected' : ''?>>
                                    <?=$description->getName()?>
                                </option>
                            <?PHP endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?PHP endforeach; ?>
            </tbody>
        </table>
        <h3 class="wp-heading-inline"><?=__('Document Email Text', 'onfact')?></h3>
        <table class="form-table" role="presentation">

            <tbody>

            <?PHP
            $emaildescriptions = Onfact_Settings::get_onfact_emaildescriptions();
            $languages = Onfact_Settings::get_onfact_languages();
            foreach ($languages->getItems() as $language):
                if (!$language->getActive()) continue;
                ?>
                <?php do_settings_sections( 'onfact-settings-deliveryslip' ); ?>
                <tr>
                    <th scope="row"><label for="api_key"><?=__('Email', 'onfact')?> <?=$language->getName()?></label></th>
                    <td>
                        <select name="deliveryslip_emaildescription_<?=$language->getId()?>" id="description.<?=$language->getId()?>" class="regular-text">
                            <option value="none"><?=__('Do not send email', 'onfact')?></option>
                            <?PHP foreach ($emaildescriptions->getItems() as $emaildescription): ?>
                                <?PHP if ($emaildescription->getLanguageId() !== $language->getId() || $emaildescription->getModel() !== 'Deliveryslip') continue ?>
                                <option
                                        value="<?=$emaildescription->getId()?>"
                                    <?=$emaildescription->getId() == get_option('deliveryslip_emaildescription_' . $language->getId()) ? 'selected=selected' : ''?>>
                                    <?=$emaildescription->getName()?>
                                </option>
                            <?PHP endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?PHP endforeach; ?>
            <tr>
                <th></th>
                <td><?php submit_button(__('Save', 'onfact')) ?></td>
            </tr>
            </tbody>
        </table>


    </form>
    <?php

} catch (\Exception $e) {
    ?>
    <?=__('You need to configure the general API settings first.', 'onfact')?>
    <?php
}
