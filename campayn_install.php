<?php

register_activation_hook(__FILE__,'campayn_install');
add_action('plugins_loaded', 'campayn_update_db_check');

$campayn_db_version = 0.1;

function campayn_install() {
   global $wpdb;
   global $campayn_db_version;

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   $forms_table = $wpdb->prefix.'campayn_forms';

   // example json response to store (GET http://api.campayn.com/api/v1/forms.json?filter[form_type]=1):
   // {   "id"              : "1603",
   //     "contact_list_id" : "1589",
   //     "form_title"      : "Only Email",
   //     "form_type"       : "1",
   //     "wp_form"         : "<form id=\"cmp_campayn\" action=\"http:\/\/campayn.com\/contacts\/signup_form_add_contact\/1589\" method=\"post\"><div>\n<label for=\"email\">Email:<span style=\"color:red\">*<\/span><\/label>\n<br \/>\n<input name=\"email\" id=\"email\" type=\"text\" placeholder=\"Email\" class=\"required\"\/><\/div>\n<input type=\"submit\" name=\"submit\" value=\"Subscribe\" \/><input type=\"hidden\" name=\"formId\" value=\"1603\" \/><\/form>",
   //     "list_name":"Sample Contact List"
   // }
   update_option('campayn_forms_table',$forms_table);
   $sql = 'CREATE TABLE '.$forms_table.' (
    id int not null,
    contact_list_id int,
    form_title varchar(255),
    form_type varchar(255), 
    wp_form text,
    list_name varchar(255),
    PRIMARY KEY (`id`)
   );';
   dbDelta($sql);
}

function campayn_update_db_check() {
    global $campayn_db_version;
    if (get_option('campayn_dbversion') < $campayn_db_version) {
        campayn_install();
    }
}


?>
