<?php
/* 
 *******************************************************************************
 * Copyright 2011-2017 DANTE Ltd. and GÉANT on behalf of the GN3, GN3+, GN4-1 
 * and GN4-2 consortia
 *
 * License: see the web/copyright.php file in the file structure
 *******************************************************************************
 */
?>
<?php
require_once(dirname(dirname(dirname(__FILE__))) . "/config/_config.php");

require_once("Helper.php");
require_once("User.php");

require_once("inc/common.inc.php");
require_once("inc/input_validation.inc.php");
require_once("../resources/inc/header.php");
require_once("../resources/inc/footer.php");
require_once("inc/option_html.inc.php");

defaultPagePrelude(_("Editing User Attributes"));
$user = new User($_SESSION['user']);
?>
<script src="js/XHR.js" type="text/javascript"></script>
<script src="js/option_expand.js" type="text/javascript"></script>
</head>
<body>
    <?php productheader("USERMGMT"); ?>
    <h1>
        <?php _("Editing User Attributes"); ?>
    </h1>
    <div class='infobox'>
        <h2>
            <?php echo _("Current User Attributes"); ?>
        </h2>
        <table>
            <?php echo infoblock($user->getAttributes(), "user", "User"); ?>
        </table>
    </div>
    <form enctype='multipart/form-data' action='edit_user_result.php' method='post' accept-charset='UTF-8'>
        <fieldset class="option_container">
            <legend>
                <strong><?php echo _("Your attributes"); ?></strong>
            </legend>
            <?php echo prefilledOptionTable($user->getAttributes(), "user", "User"); ?>
            <button type='button' class='newoption' onclick='getXML("user")'>
                <?php echo _("Add new option"); ?>
            </button>
        </fieldset>
        <div>
            <button type='submit' name='submitbutton' value='<?php echo BUTTON_SAVE; ?>'>
                <?php echo _("Save data"); ?>
            </button>
            <button type='button' class='delete' name='abortbutton' value='abort' onclick='javascript:window.location = "overview_user.php"'>
                <?php echo _("Discard changes"); ?>
            </button>
        </div>
    </form>
    <?php
    footer();
    