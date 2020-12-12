<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_subhead('New category');
form_start(Config::get('MELLIVORA_CONFIG_SITE_ADMIN_RELPATH') . 'actions/new_category');
form_input_text('Title');
form_textarea('Description');
// form_input_checkbox('Exposed', true);
// form_input_text('Available from', wactf_start());
// form_input_text('Available until', wactf_end());
form_hidden('action', 'new');
form_button_submit('Create category');
form_end();

foot();