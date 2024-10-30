<?php
/*
Plugin Name: HAQ Login Signup
Plugin URI: http://lightofweb.com/
Description: Provides simple front end registration and login forms
Version: 2.0
Author: Husain Ahmed
Author URI: https://husain25.wordpress.com/
*/



function haqRegistrationForm() {
 	if(!is_user_logged_in()) {
 		global $haqclassload;
		$haqclassload = true;
 
		// check and make sure user registration is enabled from wordpress admin
		$enabledRegistration = get_option('users_can_register');
 
		// only show the registration form if allowed
		if($enabledRegistration) {
			$output = haqRegistrationFormFields();
		} else {
			$output = __('User registration is not enabled from admin area');
		}
		return $output;
	}
}
add_shortcode('haq_signup', 'haqRegistrationForm');



// user login form
function haqLoginForm() {
 
	if(!is_user_logged_in()) {
 		global $haqclassload;
		$haqclassload = true;
 		$output = haqLoginFormFields();
	} else {
		$output = 'user already logedin';
	}
	return $output;
}
add_shortcode('haq_login', 'haqLoginForm');



// register our form css
function haqRegisterCss() {
	wp_register_style('haq-form-css', plugin_dir_url( __FILE__ ) . '/css/forms.css');
}
add_action('init', 'haqRegisterCss');

// load our form css
function haqPrintCss() {
	global $haqclassload;
 
	if ( ! $haqclassload )
		return; 
 
	wp_print_styles('haq-form-css');
}
add_action('wp_footer', 'haqPrintCss');


// signup form fields
function haqRegistrationFormFields() {
 	ob_start(); ?>	
		<h3 class="haq-header"><?php _e('Signup New Account'); ?></h3>
 		<?php 
		// show any error messages after form submit registration form
		haqShowErrorMessage(); ?>
 
		<form id="haqRegistrationForm" class="haq-registration-form" action="" method="POST">
			<fieldset>
				<p>
					<label for="haq_user_Login"><?php _e('Username'); ?></label>
					<input name="haq_user_Login" id="haq_user_Login" class="required" type="text"/>
				</p>
				<p>
					<label for="haq_user_email"><?php _e('Email'); ?></label>
					<input name="haq_user_email" id="haq_user_email" class="required" type="email"/>
				</p>
				<p>
					<label for="haq_user_first"><?php _e('First Name'); ?></label>
					<input name="haq_user_first" id="haq_user_first" type="text"/>
				</p>
				<p>
					<label for="haq_user_last"><?php _e('Last Name'); ?></label>
					<input name="haq_user_last" id="haq_user_last" type="text"/>
				</p>
				<p>
					<label for="password"><?php _e('Password'); ?></label>
					<input name="haq_user_pass" id="password" class="required" type="password"/>
				</p>
				<p>
					<label for="confirm-password"><?php _e('Password Again'); ?></label>
					<input name="confirm_password" id="confirm-password" class="required" type="password"/>
				</p>
				<p>
					<input type="checkbox" name="age_validation" id="age_validation">
					<label class="age_validation" for="age_validation"> I am 13 years of age or older.  </label>
					
				</p>

				<p>
					<input type="checkbox" name="term_condition" id="term_condition">
					<label class="term_condition" for="term_condition"> I agree to the Terms of Service and the Privacy Policy. </label>
					
				</p>

				<p><br>
					<input type="hidden" name="haq_register_nonce" value="<?php echo wp_create_nonce('pippin-register-nonce'); ?>"/>
					<input type="submit" value="<?php _e('Sign Up'); ?>"/>
				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}



// login form fields
function haqLoginFormFields() {

	ob_start(); ?>
		<h3 class="haq-header"><?php _e('Login'); ?></h3>
 
		<?php
		// show any error messages after form submission
		haqShowErrorMessage(); ?>
 
		<form id="haq-login-form"  class="haq-login-form"action="" method="post">
			<fieldset>
				<p>
					<label for="haq_user_Login">Username</label>
					<input name="haq_user_Login" id="haq_user_Login" class="required" type="text"/>
				</p>
				<p>
					<label for="haq_user_pass">Password</label>
					<input name="haq_user_pass" id="haq_user_pass" class="required" type="password"/>
				</p>
				
				<p>
					<input type="hidden" name="haq_login_nonce" value="<?php echo wp_create_nonce('haq-login-nonce'); ?>"/>
					<input id="haq_login_submit" type="submit" value="Login"/>
				</p>
			</fieldset>
		</form>
	<?php
	return ob_get_clean();
}


// logs a member in after submitting a form
function haqLoginMember() {
 
	if(isset($_POST['haq_user_Login']) && wp_verify_nonce($_POST['haq_login_nonce'], 'haq-login-nonce')) {

		$userlogin = sanitize_text_field($_POST['haq_user_Login']);
		$userpas   = sanitize_text_field($_POST['haq_user_pass']);
 
		// this returns the user ID and other info from the user name
		$user = get_userdatabylogin($_POST['haq_user_Login']);
 
		if(!$user) {
			// if the user name doesn't exist
			haq_errors()->add('empty_username', __('Invalid username'));
		}
 
		if(!isset($_POST['haq_user_pass']) || $_POST['haq_user_pass'] == '') {
			// if no password was entered
			haq_errors()->add('empty_password', __('Please enter a password'));
		}
 
		// check the user's login with their password
		if(!wp_check_password($_POST['haq_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			haq_errors()->add('empty_password', __('Incorrect password'));
		}
 
		// retrieve all error messages
		$errors = haq_errors()->get_error_messages();
 
		// only log the user in if there are no errors
		if(empty($errors)) {
			
			wp_setcookie($userlogin, $userpas, true);
			wp_set_current_user($user->ID, $userlogin);	
			do_action('wp_login', $userlogin);
 
			wp_redirect(home_url()); exit;
		}
	}
}
add_action('init', 'haqLoginMember');



// register a new user
function haqAddNewUser() {
  	if (isset( $_POST["haq_user_Login"] ) && wp_verify_nonce($_POST['haq_register_nonce'], 'haq-register-nonce')) {
		$user_login		= sanitize_text_field( $_POST["haq_user_Login"] );	
		$user_email		= sanitize_text_field( $_POST["haq_user_email"] );
		$user_first 		= sanitize_text_field( $_POST["haq_user_first"] );
		$user_last	 	= sanitize_text_field( $_POST["haq_user_last"] );
		$user_pass		= sanitize_text_field( $_POST["haq_user_pass"] );
		$pass_confirm 		= sanitize_text_field( $_POST["confirm_password"] );
		$term_condition		= sanitize_text_field( $_POST["term_condition"] );
		$age_validation		= sanitize_text_field( $_POST["age_validation"] );
 
		// this is required for username checks
		require_once(ABSPATH . WPINC . '/registration.php');
 
		if(username_exists($user_login)) {
			// Username already registered
			haq_errors()->add('username_unavailable', __('Username already taken'));
		}
		if(!validate_username($user_login)) {
			// invalid username
			haq_errors()->add('username_invalid', __('Invalid username'));
		}
		if($user_login == '') {
			// empty username
			haq_errors()->add('username_empty', __('Please enter a username'));
		}
		if(!is_email($user_email)) {
			//invalid email
			haq_errors()->add('email_invalid', __('Invalid email'));
		}
		if(email_exists($user_email)) {
			//Email address already registered
			haq_errors()->add('email_used', __('Email already registered'));
		}
		if($user_pass == '') {
			// passwords do not match
			haq_errors()->add('password_empty', __('Please enter a password'));
		}
		if($user_pass != $pass_confirm) {
			// passwords do not match
			haq_errors()->add('password_mismatch', __('Passwords do not match'));
		}
 		
 		if($age_validation == ''){
			//age validation
			haq_errors()->add('age_validation', __('Validate your Age'));
		}

		if($term_condition == ''){
			//accept term condition
			haq_errors()->add('term_condition', __('Accept term condition'));
		}

		$errors = haq_errors()->get_error_messages();
 
		// only create the user in if there are no errors
		if(empty($errors)) {
 
			$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
				)
			);
			if($new_user_id) {
				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);
 
				// log the new user in
				wp_setcookie($user_login, $user_pass, true);
				wp_set_current_user($new_user_id, $user_login);	
				do_action('wp_login', $user_login);
 
				// send the newly created user to the home page after logging them in
				wp_redirect(home_url()); exit;
			}
 
		}
 
	}
}
add_action('init', 'haqAddNewUser');


// used for tracking error messages
function haq_errors(){
    static $wp_error; 
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}


// displays error messages from form submit
function haqShowErrorMessage() {
	if($codes = haq_errors()->get_error_codes()) {
		echo '<div class="haq_errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = haq_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error: ') . '</strong>: ' . $message . '</span><br/>';
		    }
		echo '</div>';
	}	
}

