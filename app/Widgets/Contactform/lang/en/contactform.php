<?php
return [
	'widget name' => 'Contact Form',
	'widget desc' => 'This displays a contact form',

	// Misc.
	'send' => 'Send Message',
	'name' => 'Name:',
	'email' => 'Email:',
	'subject' => 'Subject:',
	'message' => 'Message:',
	'message from' => ':name (:email) sent this message on :date',
	'honeypot' => 'Please leave the following blank',
	'thank you' => 'Thank you for your contact.',
	'confirmation sent' => 'A confirmation email has been sent to the provided email address.',
	'confirmation to' => 'This is a confirmation of your message to :app.',

	// Error messages
	'error' => [
		'sending' => 'Your message could not be sent. Please try again.',
		'invalid email' => 'Please provide a valid email.',
		'invalid institution' => 'Please provide an institution.',
		'invalid domain' => 'Please provide a domain.',
		'invalid name' => 'Please provide a name.',
		'invalid honeypot' => 'Invalid response.',
	],

	// Params
	'params' => [
		'confirmation' => 'Send Confirmation',
		'confirmation desc' => 'Send a confirmation email to the submitter?',
		'name label' => 'Name Label',
		'name label desc' => 'The label next to the name input.',
		'email label' => 'Email Label',
		'email label desc' => 'The label next to the email input.',
		'subject label' => 'Subject Label',
		'subject label desc' => 'The label next to the subject input.',
		'body label' => 'Message Label',
		'body label desc' => 'The label next to the message text area.',
		'recipient email' => 'Email Recipient',
		'recipient email desc' => 'The recipient of the contact mail. If left blank, the global site email will be used.',
		'recipient name' => 'Email Recipient',
		'recipient name desc' => 'The recipient of the contact mail. If left blank, the global site name will be used.',
		'button text' => 'Button Text',
		'button text desc' => 'The text on the send button',
		'thank you text' => 'Thank you text',
		'thank you text desc' => 'The text displayed to the user when they send a message',
		'error text' => 'Error page text',
		'error text desc' => 'The text displayed to the user when the message fails to be sent',
		'no email' => 'No Email Error Message',
		'no email desc' => 'The error message when the user does not write an email',
		'invalid email' => 'Invalid Email Error Message',
		'invalid email desc' => 'The error message when the user writes an invalid email',
		'pretext' => 'Small Intro Text',
		'pretext desc' => 'A small text shown before (above) the message form',
		'honeypot' => 'Enable Bot Honeypot',
		'honeypot desc' => 'Enable this to help prevent bot submissions.',
	],
];
