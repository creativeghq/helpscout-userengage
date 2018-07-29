<?php
/*
Plugin Name: Userengage Application for Helpscout
Plugin URI: https://creativeg.gr
Description: Connect UserEngage inside your HelpScout Application
Version: 1.0.0
Author: Basilis Kanonidis
Author URI: https:///creativeg.gr
Requires at least: 3.9.1
Tested up to: 4.1
Text Domain:
 */
include_once 'config.php';
include_once plugin_dir_path(__FILE__) . '/api/class-helpscout-plugin.php';
include_once plugin_dir_path(__FILE__) . '/api/userengage.php';
class Custom_Helpscout_UserEngage
{
    public function __construct()
    {
        add_action('init', array($this, 'add_rewrite_endpoints'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }
    public function add_rewrite_endpoints()
    {
        add_rewrite_endpoint('helpscout_userengage', EP_PERMALINK | EP_PAGES);
        add_rewrite_endpoint('helpscout_userengage_action', EP_PERMALINK | EP_PAGES);
        flush_rewrite_rules();
    }
    public function template_redirect()
    {
        global $wp_query;
        if (isset($wp_query->query_vars['helpscout_userengage'])) {
            $plugin = new CUSTOM_HELPSCOUT_PLUGIN_HANDLER();
            echo json_encode($plugin->getResponse());
            exit();
        }
        if (isset($wp_query->query_vars['helpscout_userengage_action'])) {
            $plugin = new CUSTOM_HELPSCOUT_PLUGIN_HANDLER();
            if ($plugin->validateString($_GET['v'])) {
                $action     = $_GET['action'];
                $user_id    = $_GET['userid'];
                $first_name = $_GET['first_name'];
                $last_name  = $_GET['last_name'];
                $listid     = $_GET['listid'];
                $tag        = $_GET['tag'];
                $email      = $_GET['email'];
                if ($action == 'addtolist') {
                    if ($user_id && $listid) {
                        $plugin->addToList($listid, $user_id);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else if ($listid && $first_name && $last_name && $email) {
                        $user_id = $plugin->addToList($listid, '', true, $email, $first_name, $last_name);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'error', 'msg' => 'Invalid parmeters'));die;
                    }
                }
                if ($action == 'addtotag') {
                    if ($user_id && $tag) {
                        $plugin->addToTag($tag, $user_id);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else if ($tag && $email && $first_name && $last_name) {
                        $user_id = $plugin->addToTag($tag, '', true, $email, $first_name, $last_name);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'error', 'msg' => 'Invalid parameters'));die;
                    }
                }
                if ($action == 'removefromlist') {
                    if ($user_id && $listid) {
                        $plugin->removeFromList($user_id, $listid);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'error', 'msg' => 'Invalid parameters'));die;
                    }
                }
                if ($action == 'removetag') {
                    if ($user_id && $tag) {
                        $plugin->removeTag($user_id, $tag);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'error', 'msg' => 'Invalid parameters'));die;
                    }
                }
                if ($action == 'adduser') {
                    if ($email && $first_name && $last_name) {
                        $user_id = $plugin->createUser($email, $first_name, $last_name);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $user_id . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'success', 'msg' => 'Invalid parameters'));die;
                    }
                }
                if ($action == 'removeuser') {
                    if ($user_id) {
                        $plugin->deleteUser($user_id);
                        wp_redirect('https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/');
                        exit;
                    } else {
                        echo json_encode(array('type' => 'error', 'msg' => 'Invalid parameters'));die;
                    }
                }
            } else {
                echo json_encode(array('type' => 'error', 'msg' => 'Invalid url'));die;
            }
        }
    }
}
new Custom_Helpscout_UserEngage;
