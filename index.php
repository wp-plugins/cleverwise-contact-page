<?php
/**
* Plugin Name: Cleverwise Contact Page
* Description: Creates a professional contact page including web form (unlimited subjects & departments), emails, phones, faxes, hours, addresses.
* Version: 1.2
* Author: Jeremy O'Connell
* Author URI: http://www.cyberws.com/cleverwise-plugins/
* License: GPL2 .:. http://opensource.org/licenses/GPL-2.0
*/

////////////////////////////////////////////////////////////////////////////
//	Load Cleverwise Framework Library
////////////////////////////////////////////////////////////////////////////
include_once('cwfa.php');
$cwfa_cp=new cwfa_cp;

////////////////////////////////////////////////////////////////////////////
//	Wordpress database option
////////////////////////////////////////////////////////////////////////////
Global $wpdb,$cp_wp_option_version_txt,$cp_wp_option,$cp_wp_option_version_num;

$cp_wp_option_version_num='1.2';
$cp_wp_option='contact_page';
$cp_wp_option_version_txt=$cp_wp_option.'_version';

////////////////////////////////////////////////////////////////////////////
//	If admin panel is showing and user can manage options load menu option
////////////////////////////////////////////////////////////////////////////
if (is_admin()) {
	//	Hook admin code
	include_once("cpa.php");

	//	Activation code
	register_activation_hook( __FILE__, 'cw_contact_page_activate');

	//	Check installed version and if mismatch upgrade
	Global $wpdb;
	$cp_wp_option_db_version=get_option($cp_wp_option_version_txt);
	if ($cp_wp_option_db_version < $cp_wp_option_version_num) {
		update_option($cp_wp_option_version_txt,$cp_wp_option_version_num);
	}
}

////////////////////////////////////////////////////////////////////////////
//	Register shortcut to display visitor side
////////////////////////////////////////////////////////////////////////////
add_shortcode('cw_contact_page', 'cw_contact_page_vside');

////////////////////////////////////////////////////////////////////////////
//	Visitor Display
////////////////////////////////////////////////////////////////////////////
function cw_contact_page_vside() {
Global $wpdb,$cp_wp_option,$cwfa_cp;

	////////////////////////////////////////////////////////////////////////////
	//	Load data from wp db
	////////////////////////////////////////////////////////////////////////////
	$cp_wp_option_array=get_option($cp_wp_option);
	$cp_wp_option_array=unserialize($cp_wp_option_array);

	////////////////////////////////////////////////////////////////////////////
	//	Load variables
	////////////////////////////////////////////////////////////////////////////
	$cp_frm_info_layout=$cp_wp_option_array['cp_frm_info_layout'];
	$cp_organization=$cp_wp_option_array['cp_organization'];
	$cp_topics=$cp_wp_option_array['cp_topics'];
	$cp_address=$cp_wp_option_array['cp_address'];
	$cp_email=$cp_wp_option_array['cp_email'];
	$cp_hours=$cp_wp_option_array['cp_hours'];
	$cp_phone=$cp_wp_option_array['cp_phone'];
	$cp_fax=$cp_wp_option_array['cp_fax'];
	$cp_inc_ip=$cp_wp_option_array['cp_inc_ip'];
	$cp_inc_agent=$cp_wp_option_array['cp_inc_agent'];

	$cp_frm_title=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_title']);
	$cp_frm_email=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_email']);
	$cp_frm_name=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_name']);
	$cp_frm_topic=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_topic']);
	$cp_frm_comments=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_comments']);
	$cp_frm_submit=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_submit']);
	$cp_frm_css_text=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_css_text']);
	$cp_frm_css_select=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_css_select']);
	$cp_frm_css_submit=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_css_submit']);
	$cp_error_fields_msg=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_error_fields_msg']);
	$cp_error_technical_msg=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_error_technical_msg']);
	$cp_error_layout=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_error_layout']);
	$cp_success_layout=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_success_layout']);
	$cp_frm_info_layout=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_info_layout']);

	$cp_mail_server=$cp_wp_option_array['cp_mail_server'];
	$cp_mail_port=$cp_wp_option_array['cp_mail_port'];
	$cp_mail_conn_type=$cp_wp_option_array['cp_mail_conn_type'];
	$cp_mail_email=$cp_wp_option_array['cp_mail_email'];
	$cp_mail_passwd=$cp_wp_option_array['cp_mail_passwd'];
	$cp_frm_topic_box=$cp_wp_option_array['cp_frm_topic_box'];
	$cp_frm_honeypot=$cp_wp_option_array['cp_frm_honeypot'];

	$cp_topic_box='';

	////////////////////////////////////////////////////////////////////////////
	//	Process Email
	////////////////////////////////////////////////////////////////////////////

	if (isset($_REQUEST['cw_action']) and $_REQUEST['cw_action'] == 'send') {
		$cw_email=$cwfa_cp->cwf_fmt_striptrim($_REQUEST['cw_email']);
		$cw_name=$cwfa_cp->cwf_fmt_striptrim($_REQUEST['cw_name']);
		$cp_topic_box=$cwfa_cp->cwf_fmt_striptrim($_REQUEST[$cp_frm_topic_box]);
		$cw_comments=$cwfa_cp->cwf_fmt_striptrim($_REQUEST['cw_comments']);
		$cp_frm_error_msg=$cwfa_cp->cwf_fmt_striptrim($cp_wp_option_array['cp_frm_error_msg']);

		$error='';

		if (substr_count($cw_email,'@') != '1' and substr_count($cw_email,'.') < '1') {
			$error .="<li>$cp_frm_email</li>";
		}

		if (!$cw_name or $cw_name == $cp_frm_name) {
			$error .="<li>$cp_frm_name</li>";
		}

		if (is_numeric($cp_topic_box)) {
			$cp_topic_box=$cp_topic_box-1;
			isset($cp_topic_box_details);
			if ($cp_topic_box > '-1') {
				$cp_topics_array=explode("\n",$cp_topics);
				$cp_topic_box_details=$cp_topics_array[$cp_topic_box];
				list($cw_cp_to_email,$cw_cp_subject)=explode('|',$cp_topic_box_details);
			}
			unset($cp_topic_box_details);
		}
		if (!isset($cw_cp_to_email)) {
			$error .="<li>$cp_frm_topic</li>";
		}

		if (!$cw_comments or $cw_comments == $cp_frm_comments) {
			$error .="<li>$cp_frm_comments</li>";
		}

		if (!$error) {
		
			$cw_cp_body=$cp_frm_title.' :: '.$cw_cp_subject."\n\n".$cw_comments."\n\n".$cw_name;
			$cw_cp_subject=trim($cw_cp_subject);
			$cw_cp_to_email=trim($cw_cp_to_email);
		
			$cp_inc_extras='';
			if ($cp_inc_ip == '1') {
				$cw_cp_body .="\n\n========================================\n";
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$cp_inc_ip=$_SERVER['HTTP_CLIENT_IP'];
				//	to check ip is pass from proxy
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				      $cp_inc_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$cp_inc_ip=$_SERVER['REMOTE_ADDR'];
				}
				$cw_cp_body .=$cp_inc_ip;
				$cp_inc_extras='1';
			}
			if ($cp_inc_agent == '1') {
				if ($cp_inc_extras == '1') {
					$cw_cp_body .=' :: ';
				} else {
					$cw_cp_body .="\n\n========================================\n";
				}
				$cw_cp_body .=$_SERVER['HTTP_USER_AGENT'];
			}
			unset($cp_inc_extras);

			$cw_cp_body .="\n\n";
			$cw_cp_mail_status=cw_contact_page_mailout($cw_cp_to_email,$cw_email,$cw_cp_subject,$cw_cp_body,$cp_mail_server,$cp_mail_port,$cp_mail_conn_type,$cp_mail_email,$cp_mail_passwd);

			if ($cw_cp_mail_status == 'success') {
				print $cp_success_layout;
			} else {
				$error=$cp_error_technical_msg;
				Global $current_user;
				if (is_user_logged_in()) {
					if (wp_get_current_user('manage_options')) {
						$error .='<div style="margin-top: 10px;">'.$cw_cp_mail_status.'</div>';
					}
				}
				$cp_error_layout=preg_replace('/{{error}}/',$error,$cp_error_layout);
				print $cp_error_layout;
			}
		} else {
			$error=$cp_error_fields_msg.'<ul style="margin-top: 10px; text-align: left;">'.$error.'</ul>';
			$cp_error_layout=preg_replace('/{{error}}/',$error,$cp_error_layout);
			print $cp_error_layout;

			$cp_frm_email=$cw_email;
			$cp_frm_name=$cw_name;
			$cp_topic_box++;
			$cp_frm_comments=$cw_comments;
		}
	}

	////////////////////////////////////////////////////////////////////////////
	//	Load Contact Form
	////////////////////////////////////////////////////////////////////////////

	$cp_topics_cnt='1';
	$cp_topics=explode("\n",$cp_topics);
	$contact_topics='<option value="0">'.$cp_frm_topic.'</option>';
	foreach ($cp_topics as $cp_topic) {
		$cp_topic=trim($cp_topic);
		list($cp_topic_email,$cp_topic_name)=explode('|',$cp_topic);
		$contact_topics .='<option value="'.$cp_topics_cnt.'"';
		if ($cp_topic_box == $cp_topics_cnt) {
			$contact_topics .=' selected';
		}
		$contact_topics .='>'.$cp_topic_name.'</option>';
		$cp_topics_cnt++;
	}

	if ($cp_email and substr_count('<ascii>',$cp_email) > '0') {
		$cp_email_protect=explode('<ascii>',$cp_email);
		array_shift($cp_email_protect);
		foreach ($cp_email_protect as $cp_email_convert) {
			list ($cp_email_convert,$cp_email_convert_discard)=explode('</ascii>',$cp_email_convert);
			$cp_email_convert=trim($cp_email_convert);
			$cp_email_ascii=cw_contact_page_strtoascii($cp_email_convert);
			$cp_email=str_replace("<ascii>$cp_email_convert</ascii>",$cp_email_ascii,$cp_email);
		}
	}

$contact_page='';
$contact_page .=<<<EOM
<style>
@media only screen and (min-width: 610px) {
  #cw_cp_form {
float: left;
  }
@media only screen and (min-width: 400px) {
  #cw_cp_form {
display: block;
  }
}
</style>
<div style="width: 100%; overflow: hidden;">
	<div id="cw_cp_info" name="cw_cp_info" style="width: 275px; float: left;">$cp_frm_info_layout</div>
	<div id="cw_cp_form" name="cw_cp_form" style="width: 300px; text-align: left; float: left;">{{contact_form}}</div>
</div>
EOM;

$contact_form='';
$contact_form .=<<<EOM
<p><b>$cp_frm_title</b></p>
<form method="post" style="padding: 0px; margin: 0px;">
<input type="hidden" name="cw_action" value="send">
<p><input type="text" class="$cp_frm_css_text" name="cw_email" value="$cp_frm_email" onfocus="if (&#039;$cp_frm_email&#039; === this.value) {this.value = &#039;&#039;;}" onblur="if (&#039;&#039; === this.value) {this.value = &#039;$cp_frm_email&#039;;}" style="width: 275px;"></p>
<p><input type="text" class="$cp_frm_css_text" name="cw_name" value="$cp_frm_name" onfocus="if (&#039;$cp_frm_name&#039; === this.value) {this.value = &#039;&#039;;}" onblur="if (&#039;&#039; === this.value) {this.value = &#039;$cp_frm_name&#039;;}" style="width: 275px;"></p>
<p><select name="$cp_frm_topic_box" class="$cp_frm_css_select">$contact_topics</select></p>
<p><textarea name="cw_comments" onfocus="if (&#039;$cp_frm_comments&#039; === this.value) {this.value = &#039;&#039;;}" onblur="if (&#039;&#039; === this.value) {this.value = &#039;$cp_frm_comments&#039;;}" style="width: 275px; height: 175px;">$cp_frm_comments</textarea></p>
<p><input type="submit" value="$cp_frm_submit" class="$cp_frm_css_submit"></p>
</form>
EOM;

	$cp_address=preg_replace('/{{organization}}/',$cp_organization,$cp_address);

	$cp_organization=cw_contact_page_section_process($cp_organization);
	$contact_page=preg_replace('/{{organization}}/',$cp_organization,$contact_page);
	$cp_address=cw_contact_page_section_process($cp_address);
	$contact_page=preg_replace('/{{address}}/',$cp_address,$contact_page);
	$cp_email=cw_contact_page_section_process($cp_email);
	$contact_page=preg_replace('/{{email}}/',$cp_email,$contact_page);
	$cp_hours=cw_contact_page_section_process($cp_hours);
	$contact_page=preg_replace('/{{hours}}/',$cp_hours,$contact_page);
	$cp_phone=cw_contact_page_section_process($cp_phone);
	$contact_page=preg_replace('/{{phone}}/',$cp_phone,$contact_page);
	$cp_fax=cw_contact_page_section_process($cp_fax);
	$contact_page=preg_replace('/{{fax}}/',$cp_fax,$contact_page);

	$contact_page=preg_replace('/{{contact_form}}/',$contact_form,$contact_page);

	//	Display to browser/site
	return $contact_page;
}

///////	Format Contact Section
function cw_contact_page_section_process($cw_section) {
	$cw_section=stripslashes($cw_section);
	$cw_section='<p>'.$cw_section.'</p>';
	$cw_section=preg_replace('/\n/','<br>',$cw_section);
	return($cw_section);
}

//////	Convert to ASCII
function cw_contact_page_strtoascii($input) { 
    foreach (str_split($input) as $obj) { 
        $output .= '&#' . ord($obj) . ';'; 
    }
    return $output;
}

//////	Mailout
function cw_contact_page_mailout($cw_cp_to_email,$cw_email,$cw_cp_subject,$cw_cp_body,$cp_mail_server,$cp_mail_port,$cp_mail_conn_type,$cp_mail_email,$cp_mail_passwd) {
	if (!function_exists('PHPMailer')) {
		require_once('class.smtp.php');
		require_once('class.phpmailer.php');
	}

	$cw_cp_mail_config = new PHPMailer();

	$cw_cp_mail_config->isSMTP();
	$cw_cp_mail_config->SMTPAuth = true;
	$cw_cp_mail_config->Host = $cp_mail_server;
	$cw_cp_mail_config->Port = $cp_mail_port;
	//	Should we use a secure connection?
	if ($cp_mail_conn_type == 'tls' or $cp_mail_conn_type == 'ssl') {
		$cw_cp_mail_config->SMTPSecure = $cp_mail_conn_type;
	}
	$cw_cp_mail_config->Username = $cp_mail_email;
	$cw_cp_mail_config->Password = $cp_mail_passwd;

	//	Debug
	$cw_cp_mail_config->SMTPDebug = 0;
	$cw_cp_mail_config->Debugoutput = 'html';

	$cw_cp_mail_config->SetFrom($cp_mail_email,$cw_email);
	$cw_cp_mail_config->AddAddress($cw_cp_to_email);
	$cw_cp_mail_config->AddReplyTo($cw_email);

	$cw_cp_mail_config->isHTML(false);
	$cw_cp_mail_config->Subject = $cw_cp_subject;
	$cw_cp_mail_config->Body=$cw_cp_body;

	//	Send
	isset($cw_cp_mail_status);

	if (!$cw_cp_mail_config->send()) {
		$cw_cp_mail_status=$cw_cp_mail_config->ErrorInfo;
	} else {
		$cw_cp_mail_status='success';
	}

	return($cw_cp_mail_status);
}
