<?php
/*
 * Copyright 2005-2013 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once('libs/init.php');
require_once('libs/chat.php');
require_once('libs/expand.php');
require_once('libs/groups.php');
require_once('libs/notify.php');
require_once('libs/classes/thread.php');

$errors = array();
$page = array();

$token = verifyparam( "token", "/^\d{1,8}$/");
$threadid = verifyparam( "thread", "/^\d{1,8}$/");

$thread = Thread::load($threadid, $token);
if (! $thread) {
	die("wrong thread");
}

$email = getparam('email');
$page['email'] = $email;
$group = is_null($thread->groupId)?NULL:group_by_id($thread->groupId);
if( !$email ) {
	$errors[] = no_field("form.field.email");
} else if( !is_valid_email($email)) {
	$errors[] = wrong_field("form.field.email");
}

if( count($errors) > 0 ) {
	$page['formemail'] = $email;
	$page['chat.thread.id'] = $thread->id;
	$page['chat.thread.token'] = $thread->lastToken;
	$page['level'] = "";
	$page = array_merge_recursive(
		$page,
		setup_logo($group)
	);
	expand("styles/dialogs", getchatstyle(), "mail.tpl");
	exit;
}

$history = "";
$last_id = -1;
$messages = $thread->getMessages(true, $last_id);
foreach ($messages as $msg) {
	$history .= message_to_text($msg);
}

$subject = getstring("mail.user.history.subject");
$body = getstring2(
	"mail.user.history.body",
	array($thread->userName, $history, Settings::get('title'), Settings::get('hosturl'))
);

webim_mail($email, $webim_mailbox, $subject, $body);

$page = array_merge_recursive(
	$page,
	setup_logo($group)
);
expand("styles/dialogs", getchatstyle(), "mailsent.tpl");
exit;
?>