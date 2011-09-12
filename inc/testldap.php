<?php


/*
    This file is part of Envoi.

    Envoi is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Envoi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Envoi.  If not, see <http://www.gnu.org/licenses/>.

    (c) Christophe Jacquet, 2009-2011.
 */


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