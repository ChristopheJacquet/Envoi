<?php
require("ldap.php");

head("TEST LDAP");
?>

<h1>Test LDAP</h1>

<?php

if(!isset($_POST["login"]) || !isset($_POST["password"])) {

?>

<form action="testldap.php" method="post">
    Login: <input name="login" /><br />
    Password: <input name="password" type="password" /><br />
    <input type="submit" value="Login" />
</form>


<?php

} else {
    // login and password set => check with LDAP server
    $login = $_POST["login"];
    $passwd = $_POST["password"];
    echo "<p>Login: " . $login . " --&gt; Go check this!</p>";

    //echo "<p>Valid ? " . (is_valid_user($login, $passwd) ? "yes" : "no") . "</p>";

    echo "<pre>";
    $ds=ldap_connect("ldaps://ldap.supelec.fr");
    $r=ldap_bind($ds, "uid=$login,ou=people,dc=gif,dc=supelec,dc=fr", $passwd);
    $sr = ldap_search($ds, "uid=$login,ou=people,dc=gif,dc=supelec,dc=fr", "objectClass=*");
    #print_r(ldap_get_entries($ds, $sr));
    $attrs = ldap_get_attributes($ds, ldap_first_entry($ds, $sr));
    print_r($attrs);
    echo "</pre>";
}

foot();
?>