<?php 

/** @param resource $imap */
#[\Until('8.1')]
function imap_mail_copy($imap, string $message_nums, string $mailbox, int $flags = 0) : bool
{
}
#[\Since('8.1')]
function imap_mail_copy(\IMAP\Connection $imap, string $message_nums, string $mailbox, int $flags = 0) : bool
{
}