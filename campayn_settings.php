<?php
//
//  SETTINGS CONFIGURATION CLASS
//
//  By Olly Benson / v 1.3 / 20 November 2011 / http://code.olib.co.uk
//
//  HOW TO USE
//  * add a include() to this file in your plugin. 
//  * amend the config class below to add your own settings requirements. 
//  * to avoid potential conflicts change the namespace to something unique.
//  * Full details of how to use Settings see here: http://codex.wordpress.org/Settings_API  
 
 
class ob_campayn_settings_config {
var $group = 'ob_campayn'; // defines setting groups (should be bespoke to your settings) 

var $menu = array( 
	'page_name' => 'ob_campayn', // defines which pages settings will appear on. Either bespoke or media/discussion/reading etc
	'title' => "Campayn",  // page title that is displayed 
	'intro_text' => "", // This allows you to configure the Bar search plugin exactly the way you want it", // text below title
	'nav_title' => "Campayn" // how page is listed on left-hand Settings panel
	);

// settings screen WITHOUT API key
var $sections_withoutkey = array(
    'campayn' => array(
        'title' => "Campayn Settings",
        'description' => "",
        'fields' => array (
          'apikey' => array (
              'label' => "Campayn API Key",
              'description' => "Enter your Campayn API Key before using the plugin. You can get one <a href=\"http://campayn.com/login?redirect=/users/api\">here</a>.",
              'length' => "65",
              'suffix' => "",
              'default_value' => ""
              ),
          'signup' => array(
              'label' => "Sign up free",
              'description' => "You can edit this text by editin the function... ",
              'function'  => 'campayn_signup_callback',
          )
      )));

//settings screen WITH API key
var $sections_withkey = array(
    'campayn' => array(
        'title' => "Campayn Settings",
        'description' => "",
        'fields' => array (
          'apikey' => array (
              'label' => "Campayn API Key",
              'description' => "Enter your Campayn API Key before using the plugin. You can get one <a href=\"http://campayn.com/login?redirect=/users/api\">here</a>.",
              'length' => "65",
              'suffix' => "",
              'default_value' => ""
              ),
          'apikeyresult' => array (
              'label' => "Key check",
              'description' => "",
              'function' => 'campaynApiKeyCheck'
              ),
          'forms' => array (
              'label' => "Forms",    
              'function' => "ob_api_key_callback",
              'length' => "40",
              'suffix' => "",
              'default_value' => "",
              'description' => "Copy and paste the form shortcode on your site"
              ),
        ),
    ),
	  'commentators' => array (        
       'title' => "Comment subscriptions",
       'description' => "",
       'fields' => array(
          'enabled' => array (
              'label' => "Enable comment subscriptions", // you can edit this text in campayn.php in the function below
              'function' => "campayn_setting_checkbox",
              ),
          'list' => array(
              'label' => 'Select contact list',
              'function'  => 'campayn_setting_dropdown',
          ),
          'text' => array(
              'label'       => 'Text',
              'description' => '',
              'length'      => 255,
              'default_value' => 'Signup to out newsletter!',
          )
    )
    )
);
};



class ob_campayn_settings {
var $settingsConfig = NULL;
 
function __CONSTRUCT() {

	$this->settingsConfig = get_class_vars(sprintf('%s_settings_config','ob_campayn'));
    
    if (function_exists('add_action')) :
      add_action('admin_init', array( &$this, 'admin_init'));
      add_action('admin_menu', array( &$this, 'admin_add_page'));
      endif;
}
 
function admin_add_page() {
	extract($this->settingsConfig['menu']);
	add_options_page($title,$nav_title, 'manage_options', $page_name, array( &$this,'options_page'));
	}
 
function options_page() {
	printf('</pre><div><h2>%s</h2>%s<form action="options.php" method="post">',$this->settingsConfig['menu']['title'],$this->settingsConfig['menu']['intro_text']);
	settings_fields($this->settingsConfig['group']);
	do_settings_sections($this->settingsConfig['menu']['page_name']);
	printf('<input type="submit" name="Submit" value="%s" /></form></div><pre>',__('Save Changes'));
	}
 
function admin_init(){
  $apikey = get_option('ob_campayn_apikey');
  $apikey = $apikey['text_string'];
  if (!empty($apikey)) {
    $this->settingsConfig['sections'] = $this->settingsConfig['sections_withkey'];
  } else {
    $this->settingsConfig['sections'] = $this->settingsConfig['sections_withoutkey'];
  }

  foreach ($this->settingsConfig["sections"] AS $section_key=>$section_value) :
    add_settings_section($section_key, $section_value['title'], array( &$this, 'section_text'), $this->settingsConfig['menu']['page_name'], $section_value);
    foreach ($section_value['fields'] AS $field_key=>$field_value) :
      $function = (!empty($field_value['dropdown'])) ? array( &$this, 'setting_dropdown' ) : array( &$this, 'setting_string' );
      $function = (!empty($field_value['function'])) ? $field_value['function'] : $function;
      $callback = (!empty($field_value['callback'])) ? $field_value['callback'] : NULL;
      add_settings_field($this->settingsConfig['group'].'_'.$field_key, $field_value['label'], $function, $this->settingsConfig['menu']['page_name'], 
		$section_key,array_merge($field_value,array('name' => $this->settingsConfig['group'].'_'.$field_key)));
      register_setting($this->settingsConfig['group'], $this->settingsConfig['group'].'_'.$field_key,$callback);
      endforeach;
    endforeach;
  }
 
function section_text($value = NULL) {
	printf("%s",$this->settingsConfig['sections'][$value['id']]['description']);
	}
 
function setting_string($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  printf('<input id="%s" type="text" name="%1$s[text_string]" value="%2$s" size="40" /> %3$s%4$s',
    $value['name'],
    (!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
    (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
    (!empty ($value['description'])) ? sprintf("<br /><em>%s</em>",$value['description']) : NULL);
  }
 
function setting_dropdown($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
    $chooseFrom = array();
    $choices = $this->settingsConfig['dropdown_options'][$value['dropdown']];
  foreach($choices AS $key=>$option) $chooseFrom[]= sprintf('<option value="%s" %s>%s</option>',$key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
  printf('<select id="%s" name="%1$s[text_string]">%2$s</select>%3$s',$value['name'],implode("",$chooseFrom),(!empty ($value['description'])) ? sprintf("<br /><em>%s</em>",$value['description']) : NULL);
  }
 
//end class
}
$a = (sprintf('%s_settings','ob_campayn'));
$b = (sprintf("%s_init",'ob_campayn'));
$$b = new $a;
?>
