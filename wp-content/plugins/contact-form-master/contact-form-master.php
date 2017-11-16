<?php
/**
 * Plugin Name: Contact Form
 * Description: The best Contact form Plugin.
 * Version: 1.0.6
 * Author: Edmon
 * Author URI: 
 * License: GPLv2
 */
 
require_once(dirname(__FILE__).'/config.php');
require_once(YCF_CLASSES."ContactForm.php");

$contactObj = new ContactForm();