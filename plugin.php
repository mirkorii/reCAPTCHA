<?php
// Copyright 2014 Tristan van Bokkem

if (!defined("IN_ESOTALK")) exit;

ET::$pluginInfo["reCAPTCHA"] = array(
	"name" => "reCAPTCHA",
	"description" => "Protect your forum from spam and abuse while letting real people pass through with ease.",
	"version" => "1.0.0",
	"author" => "Tristan van Bokkem",
	"authorEmail" => "tristanvanbokkem@gmail.com",
	"authorURL" => "http://esotalk.org",
	"license" => "GPLv2"
);


class ETPlugin_reCAPTCHA extends ETPlugin {

	public function setup()
	{
		// We need to check if the Honeypot plugin is active, because it also overrides the join page.
		// Waiting for https://github.com/esotalk/esoTalk/issues/215 to get this fixed for both plugins.
		if (in_array("Honeypot", C("esoTalk.enabledPlugins"))) {
			return "This plugin cannot be used together with the <strong>Honeypot</strong> plugin. Please disable the Honeypot plugin if you want to use this plugin.";
		} else {
			return true;
		}
	}

	public function init()
	{
		// Include the Google reCAPTCHA library.
		require_once (PATH_PLUGINS."/reCAPTCHA/lib/recaptchalib.php");
	}

	// Override the join function to include the reCAPTCHA magic.
	public function action_userController_join($sender)
	{
		$sender->addCSSFile($this->resource("recaptcha.css"));

		// If we're already logged in, get out of here.
		if (ET::$session->user) $sender->redirect(URL(""));

		// If registration is closed, show a message.
		if (!C("esoTalk.registration.open")) {
			$sender->renderMessage(T("Registration Closed"), T("message.registrationClosed"));
			return;
		}

		// Set the title and make sure this page isn't indexed.
		$sender->title = T("Sign Up");
		$sender->addToHead("<meta name='robots' content='noindex, noarchive'/>");

		// Construct a form.
		$form = ETFactory::make("form");
		$form->action = URL("user/join");

		// Add the reCAPTCHA section.
		$form->addSection("recaptcha");

		// Add the reCAPTCHA field.
		$form->addField("recaptcha", "recaptcha", array($this, "renderRecaptchaField"));

		if ($form->isPostBack("cancel")) $sender->redirect(URL(R("return")));

		// If the form has been submitted, validate it and add the member into the database.
		if ($form->validPostBack("submit")) {

			// Check for reCaptcha.
			$reCaptcha = true;
			if(C('plugin.reCAPTCHA.private') && C('plugin.reCAPTCHA.public')) {
				$resp = recaptcha_check_answer (C('plugin.reCAPTCHA.private'), $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				$reCaptcha = $resp->is_valid;
			}

			// If no valid words are entered, show them an error.
			if (!$reCaptcha) {
				$form->error("recaptcha_response_field", T("message.invalidCaptcha"));
			} else {

				// Make sure the passwords match. The model will do the rest of the validation.
				if ($form->getValue("password") != $form->getValue("confirm")) {
					$form->error("confirm", T("message.passwordsDontMatch"));
				}
				if (!$form->errorCount()) {
					$data = array(
						"username" => $form->getValue("username"),
						"email" => $form->getValue("email"),
						"password" => $form->getValue("password"),
						"account" => ACCOUNT_MEMBER
					);

					if (!C("esoTalk.registration.requireEmailConfirmation")) {
						$data["confirmed"] = true;
					} else {
						$data["resetPassword"] = md5(uniqid(rand()));
					}

					// Create the member.
					$model = ET::memberModel();
					$memberId = $model->create($data);

					// If there were validation errors, pass them to the form.
					if ($model->errorCount()) {
						$form->errors($model->errors());
					} else {

						// If we require the user to confirm their email, send them an email and show a message.
						if (C("esoTalk.registration.requireEmailConfirmation")) {
							$sender->sendConfirmationEmail($data["email"], $data["username"], $memberId.$data["resetPassword"]);
							$sender->renderMessage(T("Success!"), T("message.confirmEmail"));
						} else {
							ET::$session->login($form->getValue("username"), $form->getValue("password"));
							$sender->redirect(URL(""));
						}
						return;
					}
				}
			}
		}
		$sender->data("form", $form);
		$sender->render($this->view("join"));
	}

	function renderRecaptchaField($form)
	{
		// Format the reCAPTCHA form with some JavaScript and HTML
		// retrieved from the Google reCAPTCHA library.
	    return "<script type='text/javascript'>
					var RecaptchaOptions={theme:'clean', custom_translations:" . json_encode(T("mlarray.reCaptcha")) . "};
					$('#recaptcha_image').live('click', function() { Recaptcha.reload(); });
				</script>".
				recaptcha_get_html(C('plugin.reCAPTCHA.public'), '', C('esoTalk.https'));
	}

	public function settings($sender)
	{
		// Set up the settings form.
		$form = ETFactory::make("form");
		$form->action = URL("admin/plugins/settings/reCAPTCHA");

		$form->setValue("private", C("plugin.reCAPTCHA.private"));
		$form->setValue("public", C("plugin.reCAPTCHA.public"));

		// If the form was submitted...
		if ($form->validPostBack()) {

			// Construct an array of config options to write.
			$config = array();
			$config["plugin.reCAPTCHA.private"] = $form->getValue("private");
			$config["plugin.reCAPTCHA.public"] = $form->getValue("public");

			// Write the config file.
			ET::writeConfig($config);

			$sender->message(T("message.changesSaved"), "success autoDismiss");
			$sender->redirect(URL("admin/plugins"));
		}

		$sender->data("reCAPTCHASettingsForm", $form);
		return $this->view("settings");
	}
}
