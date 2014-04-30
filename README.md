## esoTalk – reCAPTCHA plugin

- Protect your forum from spam and abuse while letting real people pass through with ease.

### Installation

Browse to your esoTalk plugin directory:
```
cd WEB_ROOT_DIR/addons/plugins/
```

Clone the reCAPTCHA plugin repo into the plugin directory:
```
git clone git@github.com:tristanvanbokkem/reCAPTCHA.git reCAPTCHA
```

Chown the reCAPTCHA plugin folder to the right web user:
```
chown -R apache:apache reCAPTCHA/
```

### Translation

Add the following definitions to your translation file (or create a seperate definitions.Backup.php file):

```
$definitions["message.reCaptchaRefreshInfo"] = "Click or tap on an image to refresh it";
$definitions["message.invalidCaptcha"] = "The CAPTCHA you entered is invalid. Please try again.";
$definitions["Are you human?"] = "Are you human?";
$definitions["mlarray.reCaptcha"] = array(
	"instructions_visual" => "Type the two words"
);
```

### Screenshots
Sign Up page
![Sign Up page](http://i.imgur.com/xq3WbLf.png)

Wrong CAPTCHA
![Wrong CAPTCHA](http://i.imgur.com/THqvAsK.png)

reCAPTCHA Settings
![reCAPTCHA Settings](http://i.imgur.com/M7ZX1R1.png)
