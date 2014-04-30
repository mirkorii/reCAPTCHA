<?php
// Copyright 2014 Tristan van Bokkem

if (!defined("IN_ESOTALK")) exit;

$form = $data["reCAPTCHASettingsForm"];
?>
<?php echo $form->open(); ?>

<div class='section'>

<ul class='form'>

<li>
    <label><strong><?php echo T("Private Key"); ?></strong></label>
    <?php echo $form->input("private"); ?>
</li>

<li>
    <label><?php echo T("Public Key"); ?></label>
    <?php echo $form->input("public"); ?>
    <small><?php echo T("Enter your reCAPTCHA Keys (<a href='https://www.google.com/recaptcha/admin#whyrecaptcha' target='_blank'>Got no Keys yet? Get them here!</a>)"); ?></small>
</li>


</ul>

</div>

<div class='buttons'>
<?php echo $form->saveButton(); ?>
</div>

<?php echo $form->close(); ?>
