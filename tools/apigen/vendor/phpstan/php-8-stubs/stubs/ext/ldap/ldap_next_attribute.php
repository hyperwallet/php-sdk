<?php 

/**
 * @param resource $ldap
 * @param resource $entry
 */
#[\Until('8.1')]
function ldap_next_attribute($ldap, $entry) : string|false
{
}
#[\Since('8.1')]
function ldap_next_attribute(\LDAP\Connection $ldap, \LDAP\ResultEntry $entry) : string|false
{
}